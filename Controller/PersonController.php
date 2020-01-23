<?php
namespace App\GptCavebackendBundle\Controller;
use App\GptCavebackendBundle\Form\Type\Person\PersonType;
use App\GptCavebackendBundle\Form\Type\Person\PersonsearchType;
use App\GptCavebackendBundle\Repository\PersonBackendRepository;
use App\GptCavebackendBundle\Util\ControllerParameters\CommonParams;
use App\GptCaveBundle\Entity\Person;
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

class PersonController extends AbstractController
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
        $this->controllerParams = new CommonParams('person', $params->get('cave_backend'), $translator);
    }

    /**
     * Index search form
     *
     * @Route("/person",
     *     name="cave_backend_person_index")
     * @return Response
     * @throws \Exception
     */
    public function indexAction()
    {
        $form = $this->createForm(PersonsearchType::class, new Person() , ['attr'=> ['id'=>'person_search_form']]);

        return $this->render('@GptCavebackend/content/person/index.html.twig',
            array(
                'arrayParams'=>$this->controllerParams->indexParams(),
                'form'   => $form->createView()
            ));
    }

    /**
     * Search result
     * @Route("/person/ajaxpager",
     *     name="cave_backend_person_ajaxpager",
     *     methods={"GET","POST"})
     * @param PersonBackendRepository $repository
     * @param Request $request
     * @return Response
     * @throws NonUniqueResultException
     */
    public function ajaxpagerAction(PersonBackendRepository $repository, Request $request)
    {
        $arrayParams= $this->controllerParams->getParametersbag();

        $page       = $request->get('page', 1);
        $ipp        = $request->get('ipp', $arrayParams['page']['itemsPerPage'] ?? 20);

        $form = $this->createForm(PersonsearchType::class, new Person())->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $entity = $form->getData();
        }else{
            return $this->render('@GptCavebackend/partial/form/error/all_errors_message.html.twig',
                ['form'=>$form->createView()]
            );
        }

        list($paginator, $result) = $repository->pageByPerson($entity ,$page,$ipp );

        return $this->render(
            '@GptCavebackend/content/person/index_ajax.html.twig',
            array(
                'arrayParams'=>$arrayParams,
                'entities' => $result,
                "entity_token"=>$this->container->get('security.csrf.token_manager')->getToken('person_token'),
                'paginator'=>$paginator
            ));
    }

    /**
     * New registry
     *
     * @Route("/person/new",
     *     name="cave_backend_person_new",
     *     methods={"GET","POST"})
     * @param Request $request
     * @return Response
     */
    public function newAction(Request $request)
    {
        $form = $this->createForm(PersonType::class, new Person(), ['attr'=> ['id'=>'person_new']])->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            try {
                $person = $form->getData();
                $em = $this->getDoctrine()->getManager();
                $em->persist($person);
                $em->flush();
                $em->clear();
                return $this->redirectToRoute('cave_backend_person_edit', array('id' => $person->getPersonid()));
            }catch (\Exception $ex){
                $ex instanceof CaveExceptionInteface ?
                    $form->addError(new FormError($this->controllerParams->getTranslator()->trans($ex->getMessageKey(), $ex->getMessageData()))) :
                    $form->addError(new FormError($ex->getMessage()));
            }
        }

        return $this->render(
            '@GptCavebackend/content/person/new.html.twig', [
            'arrayParams'=>$this->controllerParams->newParams(),
            'form'=> $form->createView()
        ]);
    }

    /**
     * Edit registry
     *
     * @Route("/person/edit/{id}",
     *     name="cave_backend_person_edit",
     *     methods={"GET","POST"})
     * @param Request $request
     * @param Person $person
     * @return Response
     */
    public function editAction(Request $request, Person $person)
    {
        $form = $this->createForm(PersonType::class, $person,
            ['attr'=> ['id'=>'edit-person']]
        )->handleRequest($request);

        return $this->render('@GptCavebackend/content/person/edit.html.twig', array(
            'arrayParams'=>$this->controllerParams->editParams($person->getPersonid(), $person->getName()),
            'form' => $form->createView(),
            'delete_form' => $this->createDeleteForm($person)->createView(),
            'person'=>$person
        ));
    }


    /**
     * Save form
     *
     * @Route("/person/save/{id}",
     *     name="cave_backend_person_save",
     *     methods={"GET","POST"})
     * @param Request $request
     * @param Person $person
     * @return Response|JsonResponse
     */
    public function saveAction(Request $request, Person $person)
    {
        if(!$request->isXmlHttpRequest()){
            throw new HttpException(403, sprintf("Forbidden request method %s", $request->getMethod()));
        }

        $form = $this->createForm(PersonType::class, $person)->handleRequest($request);

        if($form->isSubmitted())
        {
            if($form->isValid())
            {
                try {
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($person);
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
     * @Route("/person/delete/{id}",
     *     name="cave_backend_person_delete",
     *     methods={"DELETE"})
     * @param Request $request
     * @param Person $person
     * @return RedirectResponse
     */
    public function deleteAction(Request $request, Person $person)
    {
        $form = $this->createDeleteForm($person);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $msg = $this->get('translator')->trans('id.successfully.deleted', array('%id%'=>$person->getPersonid()), 'cavemessages');
            $em = $this->getDoctrine()->getManager();
            try{
                //TODO no debería eliminarse de la base de datos ya que
                // el registro no puede reutilizarse
                $em->remove($person);
                $em->flush();
                $this->addFlash('success', $msg);
            }catch(Exception $ex){
                $this->addFlash('danger', $ex->getMessage() );
                return $this->redirectToRoute('cave_backend_person_edit', array('id' => $person->getPersonid()));
            }
        }

        return $this->redirectToRoute('cave_backend_person_index');
    }

    /**
     * Delete form
     *
     * @param Person $person
     * @return FormInterface
     */
    private function createDeleteForm(Person $person)
    {
        return $this->createFormBuilder(null, ['attr'=> ['id'=>'person_delete_form']])
            ->setAction($this->generateUrl('cave_backend_person_delete', array(
                'id' => $person->getPersonid()
            )))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
