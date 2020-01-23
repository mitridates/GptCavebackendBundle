<?php
namespace App\GptCavebackendBundle\Controller;
use App\GptCavebackendBundle\Form\Type\Area\AreaType;
use App\GptCavebackendBundle\Form\Type\Area\AreasearchType;
use App\GptCavebackendBundle\Model\CaveExceptionInteface;
use App\GptCavebackendBundle\Repository\AreaBackendRepository;
use App\GptCavebackendBundle\Util\ControllerParameters\CommonParams;
use App\GptCaveBundle\Entity\Area;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class AreaController extends AbstractController
{
    /**
     * @var CommonParams
     */
    private $controllerParams;

    /**
     * @param TranslatorInterface $translator
     * @param ParameterBagInterface $params
     */
    public function __construct(TranslatorInterface $translator, ParameterBagInterface $params)
    {
        $this->controllerParams = new CommonParams('area', $params->get('cave_backend'), $translator);
    }

    /**
     * Index search form
     *
     * @Route("/area",
     *     name="cave_backend_area_index")
     * @return Response
     * @throws \Exception
     */
    public function indexAction()
    {
        $form = $this->createForm(AreasearchType::class, new Area() , ['attr'=> ['id'=>'area_search_form']]);

        return $this->render('@GptCavebackend/content/area/index.html.twig',
            array(
                'arrayParams'=>$this->controllerParams->indexParams(),
                'form'   => $form->createView()
        ));
    }

    /**
     * Search result
     * @Route("/area/ajaxpager",
     *     name="cave_backend_area_ajaxpager",
     *     methods={"GET","POST"})
     * @param AreaBackendRepository $repository
     * @param Request $request
     * @return Response
     * @throws NonUniqueResultException
     */
    public function ajaxpagerAction(AreaBackendRepository $repository, Request $request)
    {
        $arrayParams= $this->controllerParams->getParametersbag();

        $page       = $request->get('page', 1);
        $ipp        = $request->get('ipp', $arrayParams['page']['itemsPerPage'] ?? 20);

        $form = $this->createForm(AreasearchType::class, new Area())->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $entity = $form->getData();
        }else{
            return $this->render('@GptCavebackend/partial/form/error/all_errors_message.html.twig',
                ['form'=>$form->createView()]
            );
        }

        list($paginator, $result) = $repository->pageByArea($entity, $page, $ipp);

        return $this->render(
            '@GptCavebackend/content/area/index_ajax.html.twig',
            array(
                'arrayParams'=>$arrayParams,
                'entities' => $result,
                "entity_token"=>$this->container->get('security.csrf.token_manager')->getToken('area_token'),
                'paginator'=>$paginator
            ));
    }

    /**
     * New registry
     *
     * @Route("/area/new",
     *     name="cave_backend_area_new",
     *     methods={"GET","POST"})
     * @param Request $request
     * @return Response
     */
    public function newAction(Request $request)
    {
        $form = $this->createForm(AreaType::class, new Area())->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            try {
                $area = $form->getData();
                $em = $this->getDoctrine()->getManager();
                $em->persist($area);
                $em->flush();
                $em->clear();
                return $this->redirectToRoute('cave_backend_area_edit', array('id' => $area->getAreaid()));
            }catch (\Exception $ex){
                $ex instanceof CaveExceptionInteface?
                    $form->addError(new FormError($this->controllerParams->getTranslator()->trans($ex->getMessageKey(), $ex->getMessageData()))) :
                    $form->addError(new FormError($ex->getMessage()));
            }
        }

        return $this->render(
            '@GptCavebackend/content/area/new.html.twig', [
            'arrayParams'=>$this->controllerParams->newParams(),
            'form'=> $form->createView()
        ]);
    }

    /**
     * Edit registry
     *
     * @Route("/area/edit/{id}",
     *     name="cave_backend_area_edit",
     *     methods={"GET","POST"})
     * @param Request $request
     * @param Area $area
     * @return Response
     */
    public function editAction(Request $request, Area $area)
    {
        $form = $this->createForm(AreaType::class, $area,
            ['attr'=> ['id'=>'edit-area']]
        )->handleRequest($request);

        return $this->render('@GptCavebackend/content/area/edit.html.twig', array(
            'arrayParams'=>$this->controllerParams->editParams($area->getAreaid(), $area->getName()),
            'form' => $form->createView(),
            'delete_form' => $this->createDeleteForm($area)->createView(),
            'area'=>$area
        ));
    }

    /**
     * Save form
     *
     * @Route("/area/save/{id}",
     *     name="cave_backend_area_save",
     *     methods={"GET","POST"})
     * @param Request $request
     * @param Area $area
     * @return Response|JsonResponse
     */
    public function saveAction(Request $request, Area $area)
    {
        if(!$request->isXmlHttpRequest()){
            throw new HttpException(403, sprintf("Forbidden request method %s", $request->getMethod()));
        }

        $form = $this->createForm(AreaType::class, $area)->handleRequest($request);

        if($form->isSubmitted())
        {
            if($form->isValid())
            {
                try {
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($area);
                    $em->flush();
                    $em->clear();
                    return new JsonResponse([]);//no news is good news
                }catch (\Exception $ex){
                    $ex instanceof CaveExceptionInteface?
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
     * @Route("/area/{id}/delete/",
     *     name="cave_backend_area_delete",
     *     methods={"DELETE"})
     * @param Request $request
     * @param Area $area
     * @return RedirectResponse
     */
    public function deleteAction(Request $request, Area $area)
    {
        $form = $this->createDeleteForm($area);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $msg= $this->controllerParams->getTranslator()->trans('id.successfully.deleted', array('%id%'=>$area->getAreaid()), 'cavemessages');
            $em = $this->getDoctrine()->getManager();
            try{
                //TODO no debería eliminarse de la base de datos ya que
                // el registro no puede reutilizarse
                $em->remove($area);
                $em->flush();
                $this->addFlash('success', $msg);
            }catch(\Exception $ex){
                $this->addFlash('danger', $ex->getMessage() );
                return $this->redirectToRoute('cave_backend_area_edit', array('id' => $area->getAreaid()));
            }
        }

        return $this->redirectToRoute('cave_backend_area_index');
    }

    /**
     * Delete form
     *
     * @param Area $area
     * @return FormInterface
     */
    private function createDeleteForm(Area $area)
    {
        return $this->createFormBuilder(null, ['attr'=> ['id'=>'area_delete_form']])
            ->setAction($this->generateUrl('cave_backend_area_delete', array(
                'id' => $area->getAreaid()
            )))
            ->setMethod('DELETE')
            ->getForm()
            ;
    }
}