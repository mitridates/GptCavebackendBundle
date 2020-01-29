<?php
namespace App\GptCavebackendBundle\Controller;
use App\GptCavebackendBundle\Form\Type\Cave\CavesearchType;
use App\GptCavebackendBundle\Form\Type\Cave\EditCaveType;
use App\GptCavebackendBundle\Model\CaveExceptionInteface;
use App\GptCavebackendBundle\Repository\CaveBackendRepository;
use App\GptCavebackendBundle\Util\ControllerParameters\CaveParams;
use App\GptCaveBundle\Doctrine\Paginator;
use App\GptCaveBundle\Entity\Cave;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class CaveController extends AbstractController
{

    /**
     * @var CaveParams
     */
    private $controllerParams;

    /**
     * @param TranslatorInterface $translator
     * @param ParameterBagInterface $params
     */
    public function __construct(TranslatorInterface $translator, ParameterBagInterface $params)
    {
        $this->controllerParams = new CaveParams($params->get('cave_backend'), $translator);
    }

    /**
     * Index search form
     *
     * @Route("/cave",
     *      name="cave_backend_cave_index")
     * @return Response
     * @throws \Exception
     */
    public function indexAction()
    {
        $form = $this->createForm( CavesearchType::class, new Cave() , [
            'attr'=> ['id'=>'cave_search_form'],
        ]);

        return $this->render('@GptCavebackend/content/cave/index.html.twig',
            array(
                'arrayParams'=>$this->controllerParams->indexParams(),
                'form'   => $form->createView()
        ));
    }

    /**
     * Search result
     * @Route("/cave/ajaxpager",
     *     name="cave_backend_cave_ajaxpager")
     * @param CaveBackendRepository $repository
     * @param Request $request
     * @return Response
     * @throws NonUniqueResultException
     */
    public function ajaxpagerAction(CaveBackendRepository $repository, Request $request)
    {
        $arrayParams= $this->controllerParams->getParametersbag();

        $page       = $request->get('page', 1);
        $ipp        = $request->get('ipp', $arrayParams['page']['itemsPerPage'] ?? 20);

        $form = $this->createForm( CavesearchType::class, new Cave())->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $entity = $form->getData();
        }else{
            return $this->render('@GptCavebackend/partial/form/error/all_errors_message.html.twig',
                ['form'=>$form->createView()]
            );
        }

        list($paginator, $result) = $repository->pageByCave($entity, $page, $ipp);

        return $this->render(
        '@GptCavebackend/content/cave/index_ajax.html.twig',
            array(
                'arrayParams'=>$this->controllerParams->indexParams(),
                'entities' => $result,
                "entity_token"=>$this->container->get('security.csrf.token_manager')->getToken('cave_token'),
                'paginator'=>$paginator
            ));
    }

    /**
     * New registry
     *
     * @Route("/cave/new",
     *     name="cave_backend_cave_new",
     *     methods={"GET","POST"})
     * @param Request $request
     * @return Response
     */
    public function newAction(Request $request)
    {
        $form = $this->createForm(EditCaveType::class, new Cave())->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            try {
                $cave = $form->getData();
                $em = $this->getDoctrine()->getManager();
                $em->persist($cave);
                $em->flush();
                $em->clear();
                return $this->redirectToRoute('cave_backend_cave_edit', array('id' => $cave->getCaveid()));
            }catch (\Exception $ex){
                ($ex instanceof CaveExceptionInteface)?
                    $form->addError(new FormError($this->controllerParams->getTranslator()->trans($ex->getMessageKey(), $ex->getMessageData(), 'caveerrors'))) :
                    $form->addError(new FormError($ex->getMessage()));
            }
        }

        return $this->render(
            '@GptCavebackend/content/cave/new.html.twig', [
            'arrayParams'=>$this->controllerParams->newParams(),
            'form'=> $form->createView()
        ]);
    }

    /**
     * Render Edit page
     * @Route("/cave/edit/{id}",
     *     name="cave_backend_cave_edit",
     *     methods={"GET","POST"})
     * @param Cave $cave
     * @return Response
     */
    public function editAction(Cave $cave)
    {
        return $this->render('@GptCavebackend/content/cave/edit.html.twig', array(
            'arrayParams'=>$this->controllerParams->editParams($cave->getCaveid(), $cave->getName()),
            'delete_form' => $this->createDeleteForm($cave)->createView(),
            'cave'=>$cave
        ));
    }


    /**
     * Render Cave partial form
     * @Route("/cave/createpartial/{id}/{name}",
     *     name="cave_backend_cave_create_partial",
     *     methods={"GET","POST"})
     * @param Cave $cave
     * @param string $name
     * @return Response
     */

    public function createPartialFormAction(Cave $cave, string $name)
    {
        $params = array(
            'arrayParams'=>$this->controllerParams->editParams($cave->getCaveid(), $cave->getName()),
            'formname'=> $name,
            'form' => call_user_func_array ([$this, 'createForm'] , $this->controllerParams->createPartialform($cave, $name))->createView(),
            'cave'=>$cave
        );

        return $this->render('@GptCavebackend/content/cave/edit/cave_partial_forms.html.twig', $params);
    }

    /**
     * Save Cave partial form
     *
     * @Route("/cave/savepartial/{id}/{name}",
     *     name="cave_backend_cave_save_partial",
     *     methods={"GET","POST"})
     * @param Request $request
     * @param Cave $cave
     * @param string $name
     * @return Response|JsonResponse
     */
    public function savePartialFormAction(Request $request, Cave $cave, string $name)
    {
        if(!$request->isXmlHttpRequest()){
            throw new HttpException(403, sprintf("Forbidden request method %s", $request->getMethod()));
        }

        $form = call_user_func_array ([$this, 'createForm'] , $this->controllerParams->createPartialform($cave, $name))->handleRequest($request);

        if($form->isSubmitted())
        {
            if($form->isValid())
            {
                try {
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($cave);
                    $em->flush();
                    $em->clear();
                    return new JsonResponse([]);//no news is good news
                }catch (\Exception $ex){
                    ($ex instanceof CaveExceptionInteface)?
                        $form->addError(new FormError($this->controllerParams->getTranslator()->trans($ex->getMessageKey(), $ex->getMessageData(), 'caveerrors'))) :
                        $form->addError(new FormError($ex->getMessage()));
                }
            }
        }else{
            $form->addError(new FormError($this->controllerParams->getTranslator()->trans('unknown.error', [], 'caveerrors'))) ;
        }
        return $this->render('@GptCavebackend/partial/form/error/all_errors_message.html.twig',['form' => $form->createView()]);
    }

    /**
     * Render Cave onetoone form
     * @Route("/cave/createonetoone/{id}/{name}",
     *     name="cave_backend_cave_create_onetoone",
     *     methods={"GET","POST"})
     * @param Cave $cave
     * @param string $name
     * @return Response
     */
    public function createOnetooneFormAction(Cave $cave, string $name)
    {
        return $this->render('@GptCavebackend/content/cave/edit/cave_edit_onetoone_forms.html.twig', array(
            'arrayParams'=>$this->controllerParams->editParams($cave->getCaveid(), $cave->getName()),
            'formname'=> $name,
            'form' => call_user_func_array ([$this, 'createForm'] , $this->controllerParams->createOnetooneform($cave, $name))->createView(),
            'cave'=>$cave,
            "delete_token"=>$this->container->get('security.csrf.token_manager')->getToken('delete_token_'.$name)
        ));
    }

    /**
     * Save Cave onetoone form
     *
     * @Route("/cave/saveonetoone/{id}/{name}",
     *     name="cave_backend_cave_save_onetoone",
     *     methods={"GET","POST"})
     * @param Request $request
     * @param Cave $cave
     * @param string $name
     * @return Response|JsonResponse
     */
    public function saveOnetooneFormAction(Request $request, Cave $cave, string $name)
    {
        if(!$request->isXmlHttpRequest()){
            throw new HttpException(403, sprintf("Forbidden request method %s", $request->getMethod()));
        }

        list($formType, $entity, $parameters)=  $this->controllerParams->createOnetooneform($cave, $name);
        $form= $this->createForm($formType, $entity, $parameters)->handleRequest($request);

        if($form->isSubmitted())
        {
            if($form->isValid())
            {
                try {
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($form->getData());
                    $em->flush();
                    $em->clear();
                    return new JsonResponse([]);//no news is good news
                }catch (\Exception $ex){
                    ($ex instanceof CaveExceptionInteface)?
                        $form->addError(new FormError($this->controllerParams->getTranslator()->trans($ex->getMessageKey(), $ex->getMessageData(), 'caveerrors'))) :
                        $form->addError(new FormError($ex->getMessage()));
                }
            }
        }else{
            $form->addError(new FormError($this->controllerParams->getTranslator()->trans('unknown.error', [], 'caveerrors'))) ;
        }
        return $this->render('@GptCavebackend/partial/form/error/all_errors_message.html.twig',['form' => $form->createView()]);
    }

    /**
     * Delete onetoone
     *
     * @Route("/cave/deleteonetoone/{id}/{name}",
     *     name="cave_backend_cave_delete_onetoone",
     *     methods={"POST"})
     * @param Request $request
     * @param Cave $cave
     * @param string $name
     * @return Response
     */
    public function deleteonetooneAction(Request $request, Cave $cave, $name)
    {
        if(!$request->isXmlHttpRequest()){
            throw new HttpException(403, sprintf("Forbidden request method %s", $request->getMethod()));
        }
        if ($this->isCsrfTokenValid('delete_token_'.$name, $request->get('delete_token')))
        {
            $em = $this->getDoctrine()->getManager();
            $entity = $cave->{'getCave'.$name}();
            if($entity!==null){
                $em->remove($entity);
                $em->flush();
            }
            return new Response();
        }else{
            return new Response($this->controllerParams->getTranslator()->trans('warning.invalid.token',[], "caveerrors"));
        }
    }

    /**
     * OneToMany pagination
     *
     * @Route("/cave/onetomanypager/{id}/{name}",
     *     name="cave_backend_cave_onetomany",
     *     methods={"GET","POST"})
     * @param Request $request
     * @param Cave $cave
     * @param string $name OneToMany Cave+name
     * @return Response
     */
    public function onetomanypagerAction(Request $request, Cave $cave, $name)
    {
        $arrayParams= $this->controllerParams->getParametersbag();

        $page       = $request->get('page', 1);
        $ipp        = $request->get('ipp', $arrayParams['page']['itemsPerPage'] ?? 20);

        /**
         * @var ArrayCollection $result
         */
        $result = $cave->{'getCave'.$name}();
        $paginator = new Paginator($page, $ipp, $result->count());

        return $this->render(
            '@GptCavebackend/content/cave/edit/onetomany_pagination.html.twig', array(
            "name"=>$name,
            "delete_token"=>$this->container->get('security.csrf.token_manager')->getToken('delete_onetomany_token_'.$name),
            "cave"=>$cave,
            'arrayParams'=>$arrayParams,
            'entities' => $result,
            'paginator'=>$paginator
        ));
    }

    /**
     * Render Cave onetomany form
     * @Route("/cave/createonetomany/{cave}/{name}/{sequence?}",
     *     name="cave_backend_cave_create_onetomany",
     *     methods={"GET","POST"})
     * @param Cave $cave
     * @param string $name
     * @param int|null $sequence
     * @return Response
     */
    public function createOnetomanyformAction(Cave $cave, string $name, $sequence=null)
    {

        list($formType, $entity, $parameters)=  $this->controllerParams->createOnetomanyform($cave, $name, $sequence, ($sequence)? $this->getDoctrine()->getManager() : null);
        $form= $this->createForm($formType, $entity, $parameters)->createView();

        return $this->render('@GptCavebackend/content/cave/edit/editonetomany.html.twig', array(
            'arrayParams'=>$this->controllerParams->editParams($cave->getCaveid(), $cave->getName()),
            'name'=> $name,
            'form' => $form,
            'cave'=>$cave,
            "delete_token"=>$this->container->get('security.csrf.token_manager')->getToken('delete_onetomany_token_'.$name)
        ));
    }


    /**
     * Save Cave onetomany form
     *
     * @Route("/cave/saveonetomany/{cave}/{name}/{sequence?}",
     *     name="cave_backend_cave_save_onetomany",
     *     methods={"GET","POST"})
     * @param Request $request
     * @param Cave $cave
     * @param string $name
     * @param int|null $sequence
     * @return Response
     */
    public function saveOnetomanyFormAction(Request $request, Cave $cave, string $name, int $sequence=null)
    {
        if(!$request->isXmlHttpRequest()){
            throw new HttpException(403, sprintf("Forbidden request method %s", $request->getMethod()));
        }

        $em =  $this->getDoctrine()->getManager();
        list($formType, $entity, $parameters)=  $this->controllerParams->createOnetomanyform($cave, $name, $sequence, ($sequence)? $em : null);
        $form= $this->createForm($formType, $entity, $parameters)->handleRequest($request);


        if($form->isSubmitted())
        {
            if($form->isValid())
            {
                try {
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($form->getData()->setCave($cave));
                    $em->flush();
                    $em->clear();
                    return new Response();//no news is good news
                }catch (\Exception $ex){
                    ($ex instanceof CaveExceptionInteface)?
                        $form->addError(new FormError($this->controllerParams->getTranslator()->trans($ex->getMessageKey(), $ex->getMessageData(), 'caveerrors'))) :
                        $form->addError(new FormError($ex->getMessage()));
                }
            }
        }else{
            $form->addError(new FormError($this->controllerParams->getTranslator()->trans('unknown.error', [], 'caveerrors'))) ;
        }
        return $this->render('@GptCavebackend/partial/form/error/all_errors_message.html.twig',['form' => $form->createView()]);
    }


    /**
     * Delete onetomany
     *
     * @Route("/cave/deleteonetomany/{cave}/{name}/{sequence}/{deletetoken}",
     *     name="cave_backend_cave_delete_onetomany",
     *     methods={"GET"})
     * @param Request $request
     * @param Cave $cave
     * @param string $name
     * @param int $sequence
     * @param string $deletetoken
     * @return Response
     */
    public function deleteonetomanyAction(Request $request, Cave $cave, $name, $sequence, $deletetoken)
    {
        if(!$request->isXmlHttpRequest()){
            throw new HttpException(403, sprintf("Forbidden request method %s", $request->getMethod()));
        }
        if ($this->isCsrfTokenValid('delete_onetomany_token_'.$name, $deletetoken))
        {
            $em = $this->getDoctrine()->getManager();
            $entity= $em->getRepository("App\GptCaveBundle\Entity\Cave".$name)->findOneBy(['cave'=>$cave->getCaveid(), 'sequence'=>$sequence]);
            if($entity){
                $em->remove($entity);
                $em->flush();
            }else{
                return new Response($this->controllerParams->getTranslator()->trans('unknown.error',[], "caveerrors"));
            }
        }else{
            return new Response($this->controllerParams->getTranslator()->trans('warning.invalid.token',[], "caveerrors"));
        }
        return new Response();
    }

//    /**
//     * Edición en ventana modal para entidades 1-n
//     * La request es XmlHttpRequest
//     *
//     * @Route("/cave/editonetomany/{id}/{sequence}/{name}",
//     *     name="cave_backend_cave_editonetomany",
//     *     methods={"POST"})
//     * @param Request $request
//     * @param Cave $cave
//     * @param int $sequence
//     * @param string $name nombre del formulario|entidad a cargar
//     * @return JsonResponse|Response
//     * @throws NonUniqueResultException
//     */
//    public function editonetomanyAction(Request $request, Cave $cave, $sequence, $name)
//    {
//        if(!$request->isXmlHttpRequest()){
//            throw new HttpException(403, sprintf("Forbidden request method %s", $request->getMethod()));
//        }
//
//        $em = $this->getDoctrine()->getManager();
//        $entity = $em->getRepository("App\GptCaveBundle\Entity\Cave".$name)->findOneBy(['cave'=>$cave->getCaveid(), 'sequence'=>$sequence]);
//        $options= array(
//                    "attr"=> ['class'=>'cave-onetomany cave-'.$name.'-modal-form','id'=>'cave-'.$name.'-modal-form'],
//                    "parameters"=>new Arraypath($this->getParameter('cave_backend')),
//                    "translator"=>$this->get('translator')
//        );
//
//        if (null=== $entity) {
//            return new JsonResponse(['error'=> [
//                'global'=>$this->get("translator")->trans('error.registry.not.found', ['%more%'=>sprintf('cave ID: %s, sequence: %s', $cave->getCaveid(), $sequence)], "caveerrors")
//            ]]);
//        }
//
//        /**
//         * Unica forma de pasar los attr al formulario
//         * Necesario para modificar el id del formulario y evitar colisiones
//         * con otro formulario en la misma ventana
//         * los id quedan cave-'.$name.'-modal-form_{fieldname}
//         */
//        $form = $this->get('form.factory')->createNamedBuilder(
//            'edit-modal-'.$name,
//            sprintf('%s\\%s', CaveService::FORM_TYPE_NAMESPACE, 'Edit'.ucfirst($name)."Type"),
//            $entity,$options)->getForm();
//
//        $form->handleRequest($request);
//
//        if (!$form->isSubmitted())
//        {
//            return $this->render(
//                "@GptCavebackend/content/cave/tabs/modal.html.twig", array(
//                'xparams' => new Arraypath(['cave_backend'=> $this->getParameter('cave_backend')]),
//                "name"=>$name,
//                "form" => $form->createView(),
//                "entity"=>$entity,
//                "cave"=>$cave
//
//            ));
//        }
//
//        $data = [];
//
//        if ($form->isValid()) {
//            /*$em->getConnection()->beginTransaction(); // Tansacciones. suspend auto-commit*/
//            try{
//                $em->persist($entity);
//                $em->flush();
//                $em->clear();
//                /*$em->getConnection()->commit();*/
//            }catch (\Exception $ex){
//                /*$em->getConnection()->rollBack();*/
//                if($ex instanceof TranslatableException){
//                    $ex->trans($this->get("translator"));
//                }
//                return new JsonResponse(['error'=> ['global'=>['Unknow Exception: '.$ex->getMessage()]]]);
//            }
//        }else{
//            return new JsonResponse(array('error'=> FormErrorsFielddefinitionSerializer::serializeFielddefinitions($form,
//                $this->getDoctrine()->getRepository('GptCaveBundle:Fielddefinition'),
//                $request->getLocale())));
//        }
//         return new JsonResponse($data);
//    }

//    /**
//     * Formulario para entidades onetomany
//     * La request es XmlHttpRequest
//     * @Route("/cave/newonetomany/{id}/{name}",
//     *     name="cave_backend_cave_newonetomany",
//     *     methods={"POST"})
//     * @param Request $request
//     * @param Cave $cave
//     * @param string $name nombre del formulario|entidad a cargar
//     * @return JsonResponse
//     * @throws NonUniqueResultException
//     */
//    public function newonetomanyAction(Request $request, Cave $cave, $name)
//    {
//        if(!$request->isXmlHttpRequest()){
//            throw new HttpException(403, sprintf("Forbidden request method %s", $request->getMethod()));
//        }
//        $data = [];
//        $options = [
//            'parameters'=>new Arraypath($this->getParameter('cave_backend')),
//            'translator'=>$this->get('translator')
//        ];
//        $form = $this->createForm(
//                sprintf('%s\\%s', CaveService::FORM_TYPE_NAMESPACE, 'Edit'.ucfirst($name)."Type"),
//                CaveService::getEntityClass($cave, $name), $options);
//
//        $form->handleRequest($request);
//
//        if (!$form->isSubmitted()) {
//            $data = ['error'=> [
//                'global'=>[$this->get("translator")->trans('error.form.not.found', [], "caveerrors")]]
//            ];
//            return new JsonResponse($data);
//        }
//
//        $em = $this->getDoctrine()->getManager();
//
//        if ($form->isValid()) {
//            $entity = $form->getData();
//            $entity->setCave($cave);
//
//            try{
//                $em->persist($entity);
//                $em->flush();
//                $em->clear();
//                /*$em->getConnection()->commit();*/
//            }catch (\Exception $ex){
//                /*$em->getConnection()->rollBack();*/
//                if($ex instanceof TranslatableException){
//                    $ex->trans($this->get("translator"));
//                }
//
//                $data = ['error'=> [
//                    'global'=>[$ex->getMessage()]]
//                ];
//            }
//        }else{//catch errors
//            $data['error']=  FormErrorsFielddefinitionSerializer::serializeFielddefinitions($form,
//                $this->getDoctrine()->getRepository('GptCaveBundle:Fielddefinition'),
//                $request->getLocale());
//        }
//
//        return new JsonResponse($data);
//    }

//    /**
//     * Borrado mediante botones para entidades OnetoMany.
//     * Validado mediante el token 'entity_token'.
//     *
//     * @Route("/cave/deleteonetomany/{id}/{sequence}/{name}",
//     *     name="cave_backend_cave_deleteonetomany",
//     *     methods={"POST"})
//     * @param Request $request
//     * @param Cave $cave
//     * @param int $sequence PK
//     * @param string $name nombre del formulario|entidad a borrar
//     * @return JsonResponse
//     */
//    public function deleteonetomanyAction(Request $request, Cave $cave, $sequence, $name)
//    {
//        $data = [];
//        if(!$request->isXmlHttpRequest()){
//            throw new HttpException(403, sprintf("Forbidden request method %s", $request->getMethod()));
//        }
//        if ($this->isCsrfTokenValid('entity_token', $request->get('_token'))) {
//                $em = $this->getDoctrine()->getManager();
//                $entity= $em->getRepository("App\GptCaveBundle\Entity\Cave".$name)->findOneBy(['cave'=>$cave->getCaveid(), 'sequence'=>$sequence]);
//            try{
//                $em->remove($entity);
//                $em->flush();
//            }catch (\Exception $ex){
//                if($ex instanceof TranslatableException){
//                    $ex->trans($this->get("translator"));
//                }
//                $data = ['error'=> [
//                    'global'=>[$ex->getMessage()]]
//                ];
//            }
//        }else{
//            $data = ['error'=> [
//                'global'=>[$this->get("translator")->trans('warning.invalid.token',[], "caveerrors")]]
//            ];
//        }
//            return new JsonResponse($data);
//    }

//    /**
//     * Borrado mediante botones para entidades OnetoOne.
//     * Validado mediante el token 'entity_token'
//     *
//     * @Route("/cave/deleteonetoone/{id}/{name}",
//     *     name="cave_backend_cave_deleteonetoone",
//     *     methods={"POST"})
//     * @param Request $request
//     * @param Cave $cave
//     * @param string $name nombre del formulario|entidad a borrar
//     * @return JsonResponse
//     */
//    public function deleteonetooneAction(Request $request, Cave $cave, $name)
//    {
//        if(!$request->isXmlHttpRequest()){
//            throw new HttpException(403, sprintf("Forbidden request method %s", $request->getMethod()));
//        }
//        if ($this->isCsrfTokenValid('delete_token', $request->get('delete_token')))
//        {
//            $em = $this->getDoctrine()->getManager();
//            $entity= $em->getRepository("App\GptCaveBundle\Entity\Cave".$name)->findOneBy(['cave'=>$cave->getCaveid()]);
//
//            if(null!==$entity){
//                $em->remove($entity);
//                $em->flush();
//            }
//            $data = ['success'=> [
//                'title'=>$this->get("translator")->trans('form.successfully.deleted',['%id%'=>$cave->getCaveid()], "cavemessages")
//            ]];
//        }else{
//            $data = ['error'=> [
//                'global'=>[$this->get("translator")->trans('warning.invalid.token',[], "caveerrors")]]
//            ];
//        }
//        return new JsonResponse($data);
//    }

    /**
     * Delete
     *
     * @Route("/cave/{id}/delete/",
     *     name="cave_backend_cave_delete",
     *     methods={"DELETE"})
     * @param Request $request
     * @param Cave $cave
     * @return RedirectResponse
     */
    public function deleteAction(Request $request, Cave $cave)
    {
        $form = $this->createDeleteForm($cave);
        $form->handleRequest($request);

       if ($form->isSubmitted() && $form->isValid()) {
           $msg= $this->controllerParams->getTranslator()->trans('id.successfully.deleted', array('%id%'=>$cave->getCaveid()), 'cavemessages');
            $em = $this->getDoctrine()->getManager();
            try{
                //TODO no debería eliminarse de la base de datos ya que
                // el registro no puede reutilizarse
                $em->remove($cave);
                $em->flush();
                $this->addFlash('success', $msg);
            }catch(\Exception $ex){
                $this->addFlash('danger',$ex->getMessage() );
                return $this->redirectToRoute('cave_backend_cave_edit', array('id' => $cave->getCaveid()));
            }
        }

        return $this->redirectToRoute("cave_backend_cave_index");
    }

    /**
     * Genera el Delete form para Cave
     *
     * @param Cave $cave
     * @return FormInterface
     */
    private function createDeleteForm(Cave $cave)
    {
        return $this->createFormBuilder(null, ['attr'=> ['id'=>'cave_delete_form']])
            ->setAction($this->generateUrl('cave_backend_cave_delete', array(
                "id" => $cave->getCaveid()
                )))
            ->setMethod("DELETE")
            ->getForm()
        ;
    }
}