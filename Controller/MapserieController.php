<?php
namespace App\GptCavebackendBundle\Controller;
use App\GptCavebackendBundle\Form\Type\Mapserie\MapserieType;
use App\GptCavebackendBundle\Form\Type\Mapserie\MapseriesearchType;
use App\GptCavebackendBundle\Model\CaveExceptionInteface;
use App\GptCavebackendBundle\Repository\MapserieBackendRepository;
use App\GptCavebackendBundle\Util\ControllerParameters\CommonParams;
use App\GptCaveBundle\Entity\Mapserie;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class MapserieController extends AbstractController
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
        $this->controllerParams = new CommonParams('mapserie', $params->get('cave_backend'), $translator);
    }

    /**
     * Index search form
     *
     * @Route("/mapserie",
     *     name="cave_backend_mapserie_index")
     * @return Response
     * @throws \Exception
     */
    public function indexAction()
    {
        $form = $this->createForm(MapseriesearchType::class, new Mapserie() , [
            'attr'=> ['id'=>'mapserie_search_form']
        ]);

        return $this->render('@GptCavebackend/content/mapserie/index.html.twig',
            array(
                'arrayParams'=>$this->controllerParams->indexParams(),
                'form'   => $form->createView()
            ));
    }


    /**
     * Search result
     * @Route("/mapserie/ajaxpager",
     *     name="cave_backend_mapserie_ajaxpager",
     *     methods={"GET","POST"})
     * @param MapserieBackendRepository $repository
     * @param Request $request
     * @return Response
     * @throws NonUniqueResultException
     */
    public function ajaxpagerAction(MapserieBackendRepository $repository, Request $request)
    {
        $arrayParams= $this->controllerParams->getParametersbag();

        $page       = $request->get('page', 1);
        $ipp        = $request->get('ipp', $arrayParams['page']['itemsPerPage'] ?? 20);

        $form = $this->createForm(MapseriesearchType::class, new Mapserie())->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $entity = $form->getData();
        }else{
            return $this->render('@GptCavebackend/partial/form/error/all_errors_message.html.twig',
                ['form'=>$form->createView()]
            );
        }

        list($paginator, $result) = $repository->pageByMapserie($entity, $page, $ipp);

        return $this->render(
            '@GptCavebackend/content/mapserie/index_ajax.html.twig',
            array(
                'arrayParams'=>$arrayParams,
                'entities' => $result,
                "entity_token"=>$this->container->get('security.csrf.token_manager')->getToken('mapserie_token'),
                'paginator'=>$paginator
            ));
    }

    /**
     * New registry
     *
     * @Route("/mapserie/new",
     *     name="cave_backend_mapserie_new",
     *     methods={"GET","POST"})
     * @param Request $request
     * @return Response
     */
    public function newAction(Request $request)
    {
        $form = $this->createForm(MapserieType::class, new Mapserie())->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            try {
                $mapserie = $form->getData();
                $em = $this->getDoctrine()->getManager();
                $em->persist($mapserie);
                $em->flush();
                $em->clear();
                return $this->redirectToRoute('cave_backend_mapserie_edit', array('id' => $mapserie->getMapserieid()));
            }catch (\Exception $ex){
                ($ex instanceof CaveExceptionInteface)?
                    $form->addError(new FormError($this->controllerParams->getTranslator()->trans($ex->getMessageKey(), $ex->getMessageData()))) :
                    $form->addError(new FormError($ex->getMessage()));
            }
        }

        return $this->render(
            '@GptCavebackend/content/mapserie/new.html.twig', [
            'arrayParams'=>$this->controllerParams->newParams(),
            'form'=> $form->createView()
        ]);
    }

    /**
     * Edit registry
     *
     * @Route("/mapserie/edit/{id}",
     *     name="cave_backend_mapserie_edit",
     *     methods={"GET","POST"})
     * @param Request $request
     * @param Mapserie $mapserie
     * @return Response
     */
    public function editAction(Request $request, Mapserie $mapserie)
    {
        $form = $this->createForm(MapserieType::class, $mapserie,
            ['attr'=> ['id'=>'edit-mapserie']]
        )->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            try {
                $em = $this->getDoctrine()->getManager();
                $em->persist($mapserie);
                $em->flush();
                $em->clear();
            }catch (\Exception $ex){
                ($ex instanceof CaveExceptionInteface)?
                    $form->addError(new FormError($this->controllerParams->getTranslator()->trans($ex->getMessageKey(), $ex->getMessageData()))) :
                    $form->addError(new FormError($ex->getMessage()));
            }

        }

        return $this->render('@GptCavebackend/content/mapserie/edit.html.twig', array(
            'arrayParams'=>$this->controllerParams->editParams($mapserie->getMapserieid(), $mapserie->getName()),
            'form' => $form->createView(),
            'delete_form' => $this->createDeleteForm($mapserie)->createView(),
            'mapserie'=>$mapserie
        ));
    }


    /**
     * Delete
     *
     * @Route("/mapserie/{id}/delete/",
     *     name="cave_backend_mapserie_delete",
     *     methods={"DELETE"})
     * @param Request $request
     * @param Mapserie $mapserie
     * @return RedirectResponse
     */
    public function deleteAction(Request $request, Mapserie $mapserie)
    {
        $form = $this->createDeleteForm($mapserie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $msg= $this->controllerParams->getTranslator()->trans('id.successfully.deleted', array('%id%'=>$mapserie->getMapserieid()), 'cavemessages');
            $em = $this->getDoctrine()->getManager();
            try{
                //TODO no debería eliminarse de la base de datos ya que
                // el registro no puede reutilizarse
                $em->remove($mapserie);
                $em->flush();
                $this->addFlash('success', $msg);
            }catch(\Exception $ex){
                $this->addFlash('danger', $ex->getMessage() );
                return $this->redirectToRoute('cave_backend_mapserie_edit', array('id' => $mapserie->getMapserieid()));
            }
        }

        return $this->redirectToRoute('cave_backend_mapserie_index');
    }

    /**
     * Delete form
     *
     * @param Mapserie $mapserie
     * @return FormInterface
     */
    private function createDeleteForm(Mapserie $mapserie)
    {
        return $this->createFormBuilder(null, ['attr'=> ['id'=>'mapserie_delete_form']])
            ->setAction($this->generateUrl('cave_backend_mapserie_delete', array(
                'id' => $mapserie->getMapserieid()
            )))
            ->setMethod('DELETE')
            ->getForm()
            ;
    }
}