<?php
namespace App\GptCavebackendBundle\Controller;
use App\GptCavebackendBundle\Form\Type\Map\EditMapType;
use App\GptCavebackendBundle\Form\Type\Map\MapsearchType;
use App\GptCavebackendBundle\Model\CaveExceptionInteface;
use App\GptCavebackendBundle\Repository\MapBackendRepository;
use App\GptCavebackendBundle\Util\ControllerParameters\MapParams;
use App\GptCaveBundle\Doctrine\Paginator;
use App\GptCaveBundle\Entity\Map;
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

class MapController extends AbstractController
{

    /**
     * @var MapParams
     */
    private $controllerParams;

    /**
     * @param TranslatorInterface $translator
     * @param ParameterBagInterface $params
     */
    public function __construct(TranslatorInterface $translator, ParameterBagInterface $params)
    {
        $this->controllerParams = new MapParams($params->get('cave_backend'), $translator);
    }

    /**
     * Index search form
     *
     * @Route("/map",
     *      name="cave_backend_map_index")
     * @return Response
     * @throws \Exception
     */
    public function indexAction()
    {
        $form = $this->createForm( MapsearchType::class, new Map() , [
            'attr'=> ['id'=>'map_search_form'],
        ]);

        return $this->render('@GptCavebackend/content/map/index.html.twig',
            array(
                'arrayParams'=>$this->controllerParams->indexParams(),
                'form'   => $form->createView()
        ));
    }

    /**
     * Search result
     * @Route("/map/ajaxpager",
     *     name="cave_backend_map_ajaxpager")
     * @param MapBackendRepository $repository
     * @param Request $request
     * @return Response
     * @throws NonUniqueResultException
     */
    public function ajaxpagerAction(MapBackendRepository $repository, Request $request)
    {
        $arrayParams= $this->controllerParams->getParametersbag();

        $page       = $request->get('page', 1);
        $ipp        = $request->get('ipp', $arrayParams['page']['itemsPerPage'] ?? 20);

        $form = $this->createForm( MapsearchType::class, new Map())->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $entity = $form->getData();
        }else{
            return $this->render('@GptCavebackend/partial/form/error/all_errors_message.html.twig',
                ['form'=>$form->createView()]
            );
        }

        list($paginator, $result) = $repository->pageByMap($entity, $page, $ipp);

        return $this->render(
            '@GptCavebackend/content/map/index_ajax.html.twig',
            array(
                'arrayParams'=>$this->controllerParams->indexParams(),
                'entities' => $result,
                "entity_token"=>$this->container->get('security.csrf.token_manager')->getToken('map_token'),
                'paginator'=>$paginator
            ));
    }

    /**
     * New registry
     *
     * @Route("/map/new",
     *     name="cave_backend_map_new",
     *     methods={"GET","POST"})
     * @param Request $request
     * @return Response
     */
    public function newAction(Request $request)
    {
        $form = $this->createForm(EditMapType::class, new Map())->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            try {
                $map = $form->getData();
                $em = $this->getDoctrine()->getManager();
                $em->persist($map);
                $em->flush();
                $em->clear();
                return $this->redirectToRoute('cave_backend_map_edit', array('id' => $map->getMapid()));
            }catch (\Exception $ex){
                ($ex instanceof CaveExceptionInteface)?
                    $form->addError(new FormError($this->controllerParams->getTranslator()->trans($ex->getMessageKey(), $ex->getMessageData(), 'caveerrors'))) :
                    $form->addError(new FormError($ex->getMessage()));
            }
        }

        return $this->render(
            '@GptCavebackend/content/map/new.html.twig', [
            'arrayParams'=>$this->controllerParams->newParams(),
            'form'=> $form->createView()
        ]);
    }

    /**
     * Render Edit page
     * @Route("/map/edit/{id}",
     *     name="cave_backend_map_edit",
     *     methods={"GET","POST"})
     * @param Map $map
     * @return Response
     */
    public function editAction(Map $map)
    {
        return $this->render('@GptCavebackend/content/map/edit.html.twig', array(
            'arrayParams'=>$this->controllerParams->editParams($map->getMapid(), $map->getName()),
            'delete_form' => $this->createDeleteForm($map)->createView(),
            'map'=>$map
        ));
    }

    /**
     * Render Map partial form
     * @Route("/map/createpartial/{id}/{name}",
     *     name="cave_backend_map_create_partial",
     *     methods={"GET","POST"})
     * @param Map $map
     * @param string $name
     * @return Response
     */

    public function createPartialFormAction(Map $map, string $name)
    {
        $params = array(
            'arrayParams'=>$this->controllerParams->editParams($map->getMapid(), $map->getName()),
            'formname'=> $name,
            'form' => call_user_func_array ([$this, 'createForm'] , $this->controllerParams->createPartialform($map, $name))->createView(),
            'map'=>$map
        );

        return $this->render('@GptCavebackend/content/map/edit/forms_partial.html.twig', $params);
    }

    /**
     * Save Map partial form
     *
     * @Route("/map/savepartial/{id}/{name}",
     *     name="cave_backend_map_save_partial",
     *     methods={"GET","POST"})
     * @param Request $request
     * @param Map $map
     * @param string $name
     * @return Response|JsonResponse
     */
    public function savePartialFormAction(Request $request, Map $map, string $name)
    {
        if(!$request->isXmlHttpRequest()){
            throw new HttpException(403, sprintf("Forbidden request method %s", $request->getMethod()));
        }

        $form = call_user_func_array ([$this, 'createForm'] , $this->controllerParams->createPartialform($map, $name))->handleRequest($request);

        if($form->isSubmitted())
        {
            if($form->isValid())
            {
                try {
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($map);
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
     * Render Map onetoone form
     * @Route("/map/createonetoone/{id}/{name}",
     *     name="cave_backend_map_create_onetoone",
     *     methods={"GET","POST"})
     * @param Map $map
     * @param string $name
     * @return Response
     */
    public function createOnetooneFormAction(Map $map, string $name)
    {
        return $this->render('@GptCavebackend/content/map/edit/forms_onetoone.html.twig', array(
            'arrayParams'=>$this->controllerParams->editParams($map->getMapid(), $map->getName()),
            'formname'=> $name,
            'form' => call_user_func_array ([$this, 'createForm'] , $this->controllerParams->createOnetooneform($map, $name))->createView(),
            'map'=>$map,
            "delete_token"=>$this->container->get('security.csrf.token_manager')->getToken('delete_token_'.$name)
        ));
    }

    /**
     * Save Map onetoone form
     *
     * @Route("/map/saveonetoone/{id}/{name}",
     *     name="cave_backend_map_save_onetoone",
     *     methods={"GET","POST"})
     * @param Request $request
     * @param Map $map
     * @param string $name
     * @return Response|JsonResponse
     */
    public function saveOnetooneFormAction(Request $request, Map $map, string $name)
    {
        if(!$request->isXmlHttpRequest()){
            throw new HttpException(403, sprintf("Forbidden request method %s", $request->getMethod()));
        }

        list($formType, $entity, $parameters)=  $this->controllerParams->createOnetooneform($map, $name);
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
     * @Route("/map/deleteonetoone/{id}/{name}",
     *     name="cave_backend_map_delete_onetoone",
     *     methods={"POST"})
     * @param Request $request
     * @param Map $map
     * @param string $name
     * @return Response
     */
    public function deleteonetooneAction(Request $request, Map $map, $name)
    {
        if(!$request->isXmlHttpRequest()){
            throw new HttpException(403, sprintf("Forbidden request method %s", $request->getMethod()));
        }
        if ($this->isCsrfTokenValid('delete_token_'.$name, $request->get('delete_token')))
        {
            $em = $this->getDoctrine()->getManager();
            $entity = $map->{'getMap'.$name}();
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
     * @Route("/map/manytoonepager/{id}/{name}",
     *     name="cave_backend_map_manytoone",
     *     methods={"GET","POST"})
     * @param Request $request
     * @param Map $map
     * @param string $name OneToMany Map+name
     * @return Response
     */
    public function manytoonepagerAction(Request $request, Map $map, $name)
    {
        $arrayParams= $this->controllerParams->getParametersbag();

        $page       = $request->get('page', 1);
        $ipp        = $request->get('ipp', $arrayParams['page']['itemsPerPage'] ?? 20);

        /**
         * @var ArrayCollection $result
         */
        $result = $map->{'getMap'.$name}();
        $paginator = new Paginator($page, $ipp, $result->count());

        return $this->render(
            '@GptCavebackend/content/map/edit/paginator_result.html.twig', array(
            "name"=>$name,
            "delete_token"=>$this->container->get('security.csrf.token_manager')->getToken('delete_manytoone_token_'.$name),
            "map"=>$map,
            'arrayParams'=>$arrayParams,
            'entities' => $result,
            'paginator'=>$paginator
        ));
    }

    /**
     * Render Map manytoone form
     * @Route("/map/createmanytoone/{map}/{name}/{sequence?}",
     *     name="cave_backend_map_create_manytoone",
     *     methods={"GET","POST"})
     * @param Map $map
     * @param string $name
     * @param int|null $sequence
     * @return Response
     */
    public function createmanytooneformAction(Map $map, string $name, $sequence=null)
    {

        list($formType, $entity, $parameters)=  $this->controllerParams->createManytooneform($map, $name, $sequence, ($sequence)? $this->getDoctrine()->getManager() : null);
        $form= $this->createForm($formType, $entity, $parameters)->createView();

        return $this->render('@GptCavebackend/content/map/edit/forms_manytoone.html.twig', array(
            'arrayParams'=>$this->controllerParams->editParams($map->getMapid(), $map->getName()),
            'name'=> $name,
            'form' => $form,
            'map'=>$map,
            "delete_token"=>$this->container->get('security.csrf.token_manager')->getToken('delete_manytoone_token_'.$name)
        ));
    }


    /**
     * Save Map manytoone form
     *
     * @Route("/map/savemanytoone/{map}/{name}/{sequence?}",
     *     name="cave_backend_map_save_manytoone",
     *     methods={"GET","POST"})
     * @param Request $request
     * @param Map $map
     * @param string $name
     * @param int|null $sequence
     * @return Response
     */
    public function savemanytooneFormAction(Request $request, Map $map, string $name, int $sequence=null)
    {
        if(!$request->isXmlHttpRequest()){
            throw new HttpException(403, sprintf("Forbidden request method %s", $request->getMethod()));
        }

        $em =  $this->getDoctrine()->getManager();
        list($formType, $entity, $parameters)=  $this->controllerParams->createManytooneform($map, $name, $sequence, ($sequence)? $em : null);
        $form= $this->createForm($formType, $entity, $parameters)->handleRequest($request);


        if($form->isSubmitted())
        {
            if($form->isValid())
            {
                try {
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($form->getData()->setMap($map));
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
     * Delete manytoone
     *
     * @Route("/map/deletemanytoone/{map}/{name}/{sequence}/{deletetoken}",
     *     name="cave_backend_map_delete_manytoone",
     *     methods={"GET"})
     * @param Request $request
     * @param Map $map
     * @param string $name
     * @param int $sequence
     * @param string $deletetoken
     * @return Response
     */
    public function deletemanytooneAction(Request $request, Map $map, $name, $sequence, $deletetoken)
    {
        if(!$request->isXmlHttpRequest()){
            throw new HttpException(403, sprintf("Forbidden request method %s", $request->getMethod()));
        }
        if ($this->isCsrfTokenValid('delete_manytoone_token_'.$name, $deletetoken))
        {
            $em = $this->getDoctrine()->getManager();
            $entity= $em->getRepository("App\GptCaveBundle\Entity\Map".$name)->findOneBy(['map'=>$map->getMapid(), 'sequence'=>$sequence]);
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

    /**
     * Delete
     *
     * @Route("/map/{id}/delete/",
     *     name="cave_backend_map_delete",
     *     methods={"DELETE"})
     * @param Request $request
     * @param Map $map
     * @return RedirectResponse
     */
    public function deleteAction(Request $request, Map $map)
    {
        $form = $this->createDeleteForm($map);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $msg= $this->controllerParams->getTranslator()->trans('id.successfully.deleted', array('%id%'=>$map->getMapid()), 'cavemessages');
            $em = $this->getDoctrine()->getManager();
            try{
                //TODO no debería eliminarse de la base de datos ya que
                // el registro no puede reutilizarse
                $em->remove($map);
                $em->flush();
                $this->addFlash('success', $msg);
            }catch(\Exception $ex){
                $this->addFlash('danger',$ex->getMessage() );
                return $this->redirectToRoute('cave_backend_map_edit', array('id' => $map->getMapid()));
            }
        }

        return $this->redirectToRoute("cave_backend_map_index");
    }

    /**
     * Genera el Delete form para Map
     *
     * @param Map $map
     * @return FormInterface
     */
    private function createDeleteForm(Map $map)
    {
        return $this->createFormBuilder(null, ['attr'=> ['id'=>'map_delete_form']])
            ->setAction($this->generateUrl('cave_backend_map_delete', array(
                "id" => $map->getMapid()
            )))
            ->setMethod("DELETE")
            ->getForm()
            ;
    }
}