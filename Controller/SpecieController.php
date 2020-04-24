<?php
namespace App\GptCavebackendBundle\Controller;
use App\GptCavebackendBundle\Form\Error\FormErrorsFielddefinitionSerializer;
use App\GptCavebackendBundle\Form\Type\Article\ArticlesearchType;
use App\GptCavebackendBundle\Form\Type\Article\ArticleType;
use App\GptCavebackendBundle\Form\Type\Specie\SpecieType;
use App\GptCavebackendBundle\Form\Type\Specie\SpeciesearchType;
use App\GptCavebackendBundle\Model\CaveExceptionInteface;
use App\GptCavebackendBundle\Repository\ArticleBackendRepository;
use App\GptCavebackendBundle\Repository\SpecieBackendRepository;
use App\GptCavebackendBundle\Util\ControllerParameters\CommonParams;
use App\GptCaveBundle\Entity\Article;
use App\GptCaveBundle\Entity\Specie;
use Doctrine\ORM\Id\AssignedGenerator;
use Doctrine\ORM\Mapping\ClassMetadata;
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
 * Specie controller.
 */
class SpecieController extends AbstractController
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
        $this->controllerParams = new CommonParams('specie', $params->get('cave_backend'), $translator);
    }

    /**
     * Index search form
     *
     * @Route("/specie",
     *     name="cave_backend_specie_index")
     * @return Response
     * @throws \Exception
     */
    public function indexAction()
    {
        $form = $this->createForm(SpeciesearchType::class, new Specie() , ['attr'=> ['id'=>'specie_search_form']]);

        return $this->render('@GptCavebackend/content/specie/index.html.twig',
            array(
                'arrayParams'=>$this->controllerParams->indexParams(),
                'form'   => $form->createView()
            ));
    }

    /**
     * Search result
     * @Route("/specie/ajaxpager",
     *     name="cave_backend_specie_ajaxpager",
     *     methods={"GET","POST"})
     * @param SpecieBackendRepository $repository
     * @param Request $request
     * @return Response
     * @throws NonUniqueResultException
     */
    public function ajaxpagerAction(SpecieBackendRepository $repository, Request $request){

        $arrayParams= $this->controllerParams->getParametersbag();

        $page       = $request->get('page', 1);
        $ipp        = $request->get('ipp', $arrayParams['page']['itemsPerPage'] ?? 20);

        $form = $this->createForm(SpeciesearchType::class, new Specie())->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $entity = $form->getData();
        }else{
            return $this->render('@GptCavebackend/partial/form/error/all_errors_message.html.twig',
                ['form'=>$form->createView()]
            );
        }

        list($paginator, $result) = $repository->pageBySpecie($entity, $page, $ipp);

        return $this->render(
            '@GptCavebackend/content/specie/index_ajax.html.twig',
            array(
                'arrayParams'=>$arrayParams,
                'entities' => $result,
                "entity_token"=>$this->container->get('security.csrf.token_manager')->getToken('specie_token'),
                'paginator'=>$paginator
            ));
    }

    /**
     * New registry
     *
     * @Route("/specie/new",
     *     name="cave_backend_specie_new",
     *     methods={"GET","POST"})
     * @param Request $request
     * @return Response
     */
    public function newAction(Request $request)
    {
        $form = $this->createForm(SpecieType::class, new Specie())->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            try {
                $specie = $form->getData();
                $em = $this->getDoctrine()->getManager();
                $em->persist($specie);
                $em->flush();
                $em->clear();
                return $this->redirectToRoute('cave_backend_specie_edit', array('id' => $specie->getSpecieid()));
            }catch (\Exception $ex){
                $ex instanceof CaveExceptionInteface ?
                    $form->addError(new FormError($this->controllerParams->getTranslator()->trans($ex->getMessageKey(), $ex->getMessageData()))) :
                    $form->addError(new FormError($ex->getMessage()));
            }
        }

        return $this->render(
            '@GptCavebackend/content/specie/new.html.twig', [
            'arrayParams'=>$this->controllerParams->newParams(),
            'form'=> $form->createView()
        ]);
    }

    /**
     * Edit registry
     *
     * @Route("/specie/edit/{id}",
     *     name="cave_backend_specie_edit",
     *     methods={"GET","POST"})
     * @param Request $request
     * @param Specie $specie
     * @return Response
     */
    public function editAction(Request $request, Specie $specie)
    {
        $form = $this->createForm(SpecieType::class, $specie,
            ['attr'=> ['id'=>'edit-specie']]
        )->handleRequest($request);

        return $this->render('@GptCavebackend/content/specie/edit.html.twig', array(
            'arrayParams'=>$this->controllerParams->editParams($specie->getSpecieid(), $specie->getName()),
            'form' => $form->createView(),
            'delete_form' => $this->createDeleteForm($specie)->createView(),
            'specie'=>$specie
        ));
    }

    /**
     * Save form
     *
     * @Route("/specie/save/{id}",
     *     name="cave_backend_specie_save",
     *     methods={"GET","POST"})
     * @param Request $request
     * @param Specie $specie
     * @return Response|JsonResponse
     */
    public function saveAction(Request $request, Specie $specie)
    {
        if(!$request->isXmlHttpRequest()){
            throw new HttpException(403, sprintf("Forbidden request method %s", $request->getMethod()));
        }

        $form = $this->createForm(SpecieType::class, $specie)->handleRequest($request);

        if($form->isSubmitted())
        {
            if($form->isValid())
            {
                try {
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($specie);
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
     * @Route("/specie/delete/{id}",
     *     name="cave_backend_specie_delete",
     *     methods={"DELETE"})
     * @param Request $request
     * @param Specie $specie
     * @return RedirectResponse
     */
    public function deleteAction(Request $request, Specie $specie)
    {
        $form = $this->createDeleteForm($specie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $msg= $this->get('translator')->trans('id.successfully.deleted', array('%id%'=>$specie->getSpecieid()), 'cavemessages');
            $em = $this->getDoctrine()->getManager();
            try{
                $em->remove($specie);
                $em->flush();
                $this->addFlash('success', $msg);
            }catch(\Exception $ex){
                $this->addFlash('danger', $ex->getMessage() );
                return $this->redirectToRoute('cave_backend_specie_edit', array('id' => $specie->getSpecieid()));
            }
        }        

        return $this->redirectToRoute('cave_backend_specie_index');
    }

    /**
     * Genera el Delete form
     *
     * @param Specie $specie
     * @return FormInterface
     */
    private function createDeleteForm(Specie $specie)
    {
        return $this->createFormBuilder(null, ['attr'=> ['id'=>'specie_delete_form']])
            ->setAction($this->generateUrl('cave_backend_specie_delete', array(
                'id' => $specie->getSpecieid()
            )))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}