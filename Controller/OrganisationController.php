<?php
namespace App\GptCavebackendBundle\Controller;
use App\GptCavebackendBundle\Form\Type\Organisation\OrganisationsearchType;
use App\GptCavebackendBundle\Form\Type\Organisation\OrganisationType;
use App\GptCavebackendBundle\Model\CaveExceptionInteface;
use App\GptCavebackendBundle\Repository\OrganisationBackendRepository;
use App\GptCavebackendBundle\Util\ControllerParameters\CommonParams;
use App\GptCaveBundle\Entity\Organisation;
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

class OrganisationController extends AbstractController
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
        $this->controllerParams = new CommonParams('organisation', $params->get('cave_backend'), $translator);
    }

    /**
     * Index search form
     *
     * @Route("/organisation",
     *     name="cave_backend_organisation_index")
     * @return Response
     * @throws \Exception
     */
    public function indexAction()
    {
        $form = $this->createForm(OrganisationsearchType::class, new Organisation() , [
            'attr'=> ['id'=>'organisation_search_form']
        ]);

        return $this->render('@GptCavebackend/content/organisation/index.html.twig',
            array(
                'arrayParams'=>$this->controllerParams->indexParams(),
                'form'   => $form->createView()
        ));
    }

    /**
     * Search result
     * @Route("/organisation/ajaxpager",
     *     name="cave_backend_organisation_ajaxpager",
     *     methods={"GET","POST"})
     * @param OrganisationBackendRepository $repository
     * @param Request $request
     * @return Response
     * @throws NonUniqueResultException
     */
    public function ajaxpagerAction(OrganisationBackendRepository $repository, Request $request){

        $arrayParams= $this->controllerParams->getParametersbag();

        $page       = $request->get('page', 1);
        $ipp        = $request->get('ipp', $arrayParams['page']['itemsPerPage'] ?? 20);

        $form = $this->createForm(OrganisationsearchType::class, new Organisation())->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $entity = $form->getData();
        }else{
            return $this->render('@GptCavebackend/partial/form/error/all_errors_message.html.twig',
                ['form'=>$form->createView()]
            );
        }

        list($paginator, $result) = $repository->pageByOrganisation($entity, $page, $ipp);

        return $this->render(
            '@GptCavebackend/content/organisation/index_ajax.html.twig',
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
     * @Route("/organisation/new",
     *     name="cave_backend_organisation_new",
     *     methods={"GET","POST"})
     * @param Request $request
     * @return Response
     */
    public function newAction(Request $request)
    {
        $form = $this->createForm(OrganisationType::class, new Organisation())->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            try {
                $organisation = $form->getData();
                $em = $this->getDoctrine()->getManager();
                $em->persist($organisation);
                $em->flush();
                $em->clear();
                return $this->redirectToRoute('cave_backend_organisation_edit', array('id' => $organisation->getOrganisationid()));
            }catch (\Exception $ex){
                ($ex instanceof CaveExceptionInteface)?
                    $form->addError(new FormError($this->controllerParams->getTranslator()->trans($ex->getMessageKey(), $ex->getMessageData()))) :
                    $form->addError(new FormError($ex->getMessage()));
            }
        }

        return $this->render(
            '@GptCavebackend/content/organisation/new.html.twig', [
            'arrayParams'=>$this->controllerParams->newParams(),
            'form'=> $form->createView()
        ]);
    }

    /**
     * Edit registry
     *
     * @Route("/organisation/edit/{id}",
     *     name="cave_backend_organisation_edit",
     *     methods={"GET","POST"})
     * @param Request $request
     * @param Organisation $organisation
     * @return Response
     */
    public function editAction(Request $request, Organisation $organisation)
    {
        $form = $this->createForm(OrganisationType::class, $organisation,
            ['attr'=> ['id'=>'edit-organisation'], 'translator'=>$this->controllerParams->getTranslator()]
        )->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            try {
                $em = $this->getDoctrine()->getManager();
                $em->persist($organisation);
                $em->flush();
                $em->clear();
            }catch (\Exception $ex){
                ($ex instanceof CaveExceptionInteface)?
                    $form->addError(new FormError($this->controllerParams->getTranslator()->trans($ex->getMessageKey(), $ex->getMessageData()))) :
                    $form->addError(new FormError($ex->getMessage()));
            }

        }

        return $this->render('@GptCavebackend/content/organisation/edit.html.twig', array(
            'arrayParams'=>$this->controllerParams->editParams($organisation->getOrganisationid(), $organisation->getName()),
            'form' => $form->createView(),
            'delete_form' => $this->createDeleteForm($organisation)->createView(),
            'organisation'=>$organisation
        ));
    }

    /**
     * Delete
     *
     * @Route("/organisation/{id}/delete",
     *     name="cave_backend_organisation_delete",
     *     methods={"DELETE"})
     * @param Request $request
     * @param Organisation $organisation
     * @return RedirectResponse
     */
    public function deleteAction(Request $request, Organisation $organisation)
    {
        $form = $this->createDeleteForm($organisation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $msg = $this->controllerParams->getTranslator()->trans('id.successfully.deleted', array('%id%'=>$organisation->getOrganisationid()), 'cavemessages');
            $em = $this->getDoctrine()->getManager();
            try{
                //TODO no debería eliminarse de la base de datos ya que
                // el registro no puede reutilizarse
                $em->remove($organisation);
                $em->flush();
                $this->addFlash('success', $msg);
            }catch(\Exception $ex){
                $this->addFlash('danger', $ex->getMessage() );
                return $this->redirectToRoute('cave_backend_organisation_edit', array('id' => $organisation->getOrganisationid()));
            }
        }

        return $this->redirectToRoute('cave_backend_organisation_index');
    }

    /**
     * Delete form
     *
     * @param Organisation $organisation
     * @return FormInterface
     */
    private function createDeleteForm(Organisation $organisation)
    {
        return $this->createFormBuilder(null, ['attr'=> ['id'=>'organisation_delete_form']])
            ->setAction($this->generateUrl('cave_backend_organisation_delete', array(
                'id' => $organisation->getOrganisationid()
                )))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
