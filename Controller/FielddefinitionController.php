<?php
namespace App\GptCavebackendBundle\Controller;
use App\GptCavebackendBundle\Form\Type\Fielddefinition\FielddefinitionType;
use App\GptCavebackendBundle\Form\Type\Fielddefinition\FielddefinitionlangType;
use App\GptCavebackendBundle\Form\Type\Fielddefinition\FielddefinitionsearchType;
use App\GptCavebackendBundle\Model\CaveExceptionInteface;
use App\GptCavebackendBundle\Repository\FielddefinitionBackendRepository;
use App\GptCavebackendBundle\Util\ControllerParameters\FielddefinitionParams;
use App\GptCaveBundle\Entity\Fielddefinition;
use App\GptCaveBundle\Entity\Fielddefinitionlang;
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

/**
 * Fielddefinition controller.
 */
class FielddefinitionController extends AbstractController
{
    /**
     * @var FielddefinitionParams
     */
    private $controllerParams;

    /**
     * @param TranslatorInterface $translator
     * @param ParameterBagInterface $params
     */
    public function __construct(TranslatorInterface $translator, ParameterBagInterface $params)
    {
        $this->controllerParams = new FielddefinitionParams($params->get('cave_backend'), $translator);
    }

    /**
     * Index search form
     *
     * @Route("/fielddefinition",
     *     name="cave_backend_fielddefinition_index")
     * @return Response
     * @throws \Exception
     */
    public function indexAction()
    {
        $form = $this->createForm(FielddefinitionsearchType::class, new Fielddefinition() , [
            'attr'=> ['id'=>'fielddefinition_search_form'],
            'choices'=> $this->controllerParams->getChoices()
        ]);

        return $this->render('@GptCavebackend/content/fielddefinition/index.html.twig',
            array(
                'arrayParams'=>$this->controllerParams->indexParams(),
                'form'   => $form->createView()
            ));
    }

    /**
     * Search result
     * @Route("/fielddefinition/ajaxpager",
     *     name="cave_backend_fielddefinition_ajaxpager",
     *     methods={"GET","POST"})
     * @param FielddefinitionBackendRepository $repository
     * @param Request $request
     * @return Response
     * @throws NonUniqueResultException
     */
    public function ajaxpagerAction(FielddefinitionBackendRepository $repository, Request $request)
    {
        $arrayParams= $this->controllerParams->getParametersbag();

        $page       = $request->get('page', 1);
        $ipp        = $request->get('ipp', $arrayParams['page']['itemsPerPage'] ?? 20);

        $form = $this->createForm(FielddefinitionsearchType::class, new Fielddefinition(), [
            'attr'=> ['id'=>'fielddefinition_search_form'],
            'choices'=> $this->controllerParams->getChoices()
        ])->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $entity = $form->getData();
        }else{
            return $this->render('@GptCavebackend/partial/form/error/all_errors_message.html.twig',
                ['form'=>$form->createView()]
            );
        }

        list($paginator, $result) = $repository->pageByFielDefinition($entity, $page, $ipp);

        return $this->render(
            '@GptCavebackend/content/fielddefinition/index_ajax.html.twig',
            array(
                'arrayParams'=>$arrayParams,
                'entities' => $result,
                "entity_token"=>$this->container->get('security.csrf.token_manager')->getToken('fielddefinition_token'),
                'paginator'=>$paginator
            ));
    }

    /**
     * New registry
     *
     * @Route("/fielddefinition/new",
     *     name="cave_backend_fielddefinition_new",
     *     methods={"GET","POST"})
     * @param Request $request
     * @return Response
     */
    public function newAction(Request $request)
    {
        $form = $this->createForm(FielddefinitionType::class, new Fielddefinition(),
            [
                'attr'=> ['id'=>'new-fielddefinition', 'name'=> 'fielddefinitionnew'],
                'choices'=> $this->controllerParams->getChoices()
            ]
        )->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            try {
                $entity = $form->getData();
                $em = $this->getDoctrine()->getManager();
                $em->persist($entity);
                $em->flush();
                $em->clear();
                return $this->redirectToRoute('cave_backend_fielddefinition_edit', array('id' => $entity->getCode()));
            }catch (\Exception $ex){
                ($ex instanceof CaveExceptionInteface)?
                    $form->addError(new FormError($this->controllerParams->getTranslator()->trans($ex->getMessageKey(), $ex->getMessageData()))) :
                    $form->addError(new FormError($ex->getMessage()));
            }
        }

        return $this->render(
            '@GptCavebackend/content/fielddefinition/new.html.twig', [
            'arrayParams'=>$this->controllerParams->newParams(),
            'form'=> $form->createView()
        ]);
    }


    /**
     * Edit registry
     *
     * @Route("/fielddefinition/edit/{id}",
     *     name="cave_backend_fielddefinition_edit",
     *     methods={"GET","POST"})
     * @param Request $request
     * @param Fielddefinition $fielddefinition
     * @return Response
     */
    public function editAction(Request $request, Fielddefinition $fielddefinition)
    {
        $form = $this->createForm(FielddefinitionType::class, $fielddefinition,
            [
            'attr'=> ['id'=>'edit-fielddefinition'],
            'choices'=> $this->controllerParams->getChoices()
            ]
        )->handleRequest($request);

        return $this->render('@GptCavebackend/content/fielddefinition/edit.html.twig', array(
            'arrayParams'=>$this->controllerParams->editParams($fielddefinition->getCode(), $fielddefinition->getName()),
            'form' => $form->createView(),
            'delete_form' => $this->createDeleteForm($fielddefinition)->createView(),
            'fielddefinition'=>$fielddefinition
        ));
    }

    /**
     * Save form
     *
     * @Route("/fielddefinition/save/{id}",
     *     name="cave_backend_fielddefinition_save",
     *     methods={"GET","POST"})
     * @param Request $request
     * @param Fielddefinition $fielddefinition
     * @return Response|JsonResponse
     */
    public function saveAction(Request $request, Fielddefinition $fielddefinition)
    {
        if(!$request->isXmlHttpRequest()){
            throw new HttpException(403, sprintf("Forbidden request method %s", $request->getMethod()));
        }

        $form = $this->createForm(FielddefinitionType::class, $fielddefinition,
            [
                'attr'=> ['id'=>'edit-fielddefinition'],
                'choices'=> $this->controllerParams->getChoices()
            ]
        )->handleRequest($request);

        if($form->isSubmitted())
        {
            if($form->isValid())
            {
                try {
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($fielddefinition);
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
     * Delete
     *
     * @Route("/fielddefinition/{id}/delete/",
     *     name="cave_backend_fielddefinition_delete",
     *     methods={"DELETE"})
     * @param Request $request
     * @param Fielddefinition $fielddefinition
     * @return RedirectResponse
     */
    public function deleteAction(Request $request, Fielddefinition $fielddefinition)
    {
        $form = $this->createDeleteForm($fielddefinition);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $msg= $this->controllerParams->getTranslator()->trans('id.successfully.deleted', array('%id%'=>$fielddefinition->getCode()), 'cavemessages');
            $em = $this->getDoctrine()->getManager();
            try{
                $em->remove($fielddefinition);
                $em->flush();
                $this->addFlash('success', $msg);
            }catch(\Exception $ex){
                $this->addFlash('danger', $ex->getMessage() );
                return $this->redirectToRoute('cave_backend_fielddefinition_edit', array('id' => $fielddefinition->getCode()));
            }
        }

        return $this->redirectToRoute('cave_backend_fielddefinition_index');
    }

    /**
     * Delete form
     *
     * @param Fielddefinition $fielddefinition
     * @return FormInterface
     */
    private function createDeleteForm(Fielddefinition $fielddefinition)
    {
        return $this->createFormBuilder(null, ['attr'=> ['id'=>'fielddefinition_delete_form']])
            ->setAction($this->generateUrl('cave_backend_fielddefinition_delete', array(
                'id' => $fielddefinition->getCode()
            )))
            ->setMethod('DELETE')
            ->getForm()
            ;
    }
    
    /**
     * Edit/new fielddefinitionlang
     *
     * @Route("/fielddefinition/editonetomany/{id}/{language}",
     *     name="cave_backend_fielddefinition_editonetomany",
     *     methods={"POST"})
     * @param Request $request
     * @param Fielddefinition $fielddefinition
     * @param string $language
     * @return Response|JsonResponse
     * @throws NonUniqueResultException
     */
    public function editonetomanyAction(Request $request, Fielddefinition $fielddefinition, $language)
    {
        if(!$request->isXmlHttpRequest()){
            throw new HttpException(403, sprintf("Forbidden request method %s", $request->getMethod()));
        }

        $em = $this->getDoctrine()->getManager();
        $repository = new FielddefinitionBackendRepository($em);
        $translationExist = $fielddefinitionlang = $repository->getTranslation($fielddefinition->getCode(), $language);
        if ($fielddefinitionlang === null)
        {
            $fielddefinitionlang= new Fielddefinitionlang($fielddefinition);
            $fielddefinitionlang->setLanguage($language);
            foreach (['comment', 'name', 'definition', 'example', 'uso'] as $i)
            {
                $fielddefinitionlang->{'set'.ucfirst($i)}($fielddefinition->{'get'.ucfirst($i)}());
            }
        }

        $form = $this->createForm(FielddefinitionlangType::class, $fielddefinitionlang , array(
            'attr' => ['id'=>'fielddefinition_'.$language.'_form']
        ))->handleRequest($request);

        if (!$form->isSubmitted())
        {
            return $this->render("@GptCavebackend/content/fielddefinition/form/modal_form.html.twig", array(
                'arrayParams' => $this->controllerParams->getParametersbag(),
                "form" => $form->createView(),
                "language" => $language,
                "entity"=>$fielddefinitionlang,
                "fielddefinition"=>$fielddefinition,
                'translationExist'=>$translationExist
            ));
        }else{
            if ($form->isValid()) {
                try {
                    $em->persist($form->getData());
                    $em->flush();
                    $em->clear();
                    return new Response();//no error. Return Object.length: 0
                } catch (\Exception $ex) {
                    ($ex instanceof CaveExceptionInteface)?
                        $form->addError(new FormError($this->controllerParams->getTranslator()->trans($ex->getMessageKey(), $ex->getMessageData()))) :
                        $form->addError(new FormError($ex->getMessage()));
                }
            }

        }
        return $this->render("@GptCavebackend/content/fielddefinition/form/modal_form.html.twig", array(
            'arrayParams' => $this->controllerParams->getParametersbag(),
            "form" => $form->createView(),
            "language" => $language,
            "entity"=>$fielddefinitionlang,
            "fielddefinition"=>$fielddefinition,
            'translationExist'=>$translationExist
        ));
    }


    /**
     * Delete onetomany. Borrado mediante enlaces.
     *
     * @Route("/fielddefinition/deleteonetomany/{id}/{language}",
     *     name="cave_backend_fielddefinition_deleteonetomany",
     *     methods={"POST"})
     * @param Request $request
     * @param Fielddefinition $fielddefinition $fielddefinition ID
     * @param string $language
     * @return JsonResponse
     */
    public function deleteonetomanyAction(Request $request, Fielddefinition $fielddefinition, $language)
    {
        if(!$request->isXmlHttpRequest()){
            throw new HttpException(403, sprintf("Forbidden request method %s", $request->getMethod()));
        }
        $data = [];
        $em = $this->getDoctrine()->getManager();
        $respository = $em->getRepository(Fielddefinitionlang::class);
        $entity= $respository->findOneBy(['code'=>$fielddefinition->getCode(), 'language'=>$language]);

        if ($this->isCsrfTokenValid('entity_token', $request->get('_token'))) {
            try{
                $em->remove($entity);
                $em->flush();
            }catch (Exception $ex){
                $data= ($ex instanceof CaveExceptionInteface)?
                    $this->controllerParams->getTranslator()->trans($ex->getMessageKey(), $ex->getMessageData(), 'caveerrors') : $ex->getMessage();
            }
        }else{
           $data = $this->get("translator")->trans('warning.invalid.token',[], "caveerrors");
        }
        return new JsonResponse($data);
    }

    /**
     * Language pagination
     *
     * @Route("/fielddefinition/onetomanypager/{id}",
     *     name="cave_backend_fielddefinition_onetomanypager",
     *     methods={"GET","POST"})
     * @param Request $request
     * @param Fielddefinition $fielddefinition
     * @return Response
     * @throws NonUniqueResultException
     */
    public function onetomanypagerAction(Request $request, Fielddefinition $fielddefinition)
    {

        $arrayParams= $this->controllerParams->getParametersbag();
        $repository = new FielddefinitionBackendRepository($this->getDoctrine()->getManager());

        $page = (int) $request->get('page', 1);
        $ipp  = (int) $request->get('ipp', $arrayParams['page']['itemsPerPage'] ?? 20);

        list($paginator, $result) = $repository->paginateFdTranslationsByCode($fielddefinition->getCode(), $page, $ipp);

        $translation = ['done'=>[], 'pending'=>[], 'available'=>$arrayParams['page']['languages'] ?? []];
        foreach($result as $item){
            $translation['done'][]=$item->getLanguage();
        }
        $translation['pending']= array_diff($translation['available'], $translation['done']);

        return $this->render(
            "@GptCavebackend/content/fielddefinition/language/search_result/index.html.twig", array(
            'arrayParams'=>$this->controllerParams->indexParams(),
            "entity_token"=>$this->container->get('security.csrf.token_manager')->getToken('entity_token'),
            "entity"=>$fielddefinition,
            "entities" => $result,
            "paginator"=>$paginator,
            "translation"=>$translation
        ));
    }    

}