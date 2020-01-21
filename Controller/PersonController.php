<?php
namespace App\GptCavebackendBundle\Controller;
use App\GptCavebackendBundle\Form\Error\FormErrorsFielddefinitionSerializer;
use App\GptCavebackendBundle\Form\Type\Person\PersonType;
use App\GptCavebackendBundle\Form\Type\Person\PersonsearchType;
use App\GptCavebackendBundle\Service\BackendParams\PersonParams;
use App\GptCavebackendBundle\Util\Select2;
use App\GptCaveBundle\Entity\Person;
use Doctrine\ORM\Id\AssignedGenerator;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Person controller.
 */
class PersonController extends AbstractController
{

    /**
     * Get config and routes for view
     * @param mixed $action method (args if needed) for controller action
     * @return array
     * @throws Exception
     */
    private function getParams($action)
    {
        return call_user_func_array(
            [ new PersonParams(
                $this->get('translator'),
                $this->get('router'),
                $this->getParameter('cave_backend')
                ), 'getActionParams'
            ],
            func_get_args()
        );
    }

    /**
     * Formulario de busqueda y página de inicio
     *
     * @Route("/person",
     *     name="cave_backend_person_index")
     * @return Response
     * @throws \Exception
     */
    public function index()
    {
        $xparams= $this->getParams('index');

        $form = $this->createForm(PersonsearchType::class, new Person() , [
            'method' => 'POST',
            'attr'=> ['id'=>'search_form', 'novalidate'=>'novalidate'],//ajax
            'parameters'=>$xparams->getNode('cave_backend')
        ])->createView();

        return $this->render('@GptCavebackend/content/person/index.html.twig',
            array(
                'arrayParams'=>$this->controllerParams->indexParams(),
                'form'   => $form
        ));
    }

    /**
     * filtra el formulario de búsqueda y retorna html para  paginar
     *
     * @Route("/person/ajaxpager",
     *     name="cave_backend_person_ajaxpager",
     *     methods={"GET","POST"})
     * @param Request $request
     * @return Response
     * @throws Exception
     * @throws NonUniqueResultException
     */
    public function ajaxpagerAction(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository('GptCaveBundle:Person');
        $xparams= $this->getParams('ajaxpager');
        $page       = (int) $request->get('page', 1);
        $ipp        = (int) $request->get('ipp', $xparams->get('page:itemsPerPage', 20));
        $search_form = $this->createForm(PersonsearchType::class, new Person() , [
            'method' => 'POST',
            'action'=>$this->generateUrl('cave_backend_person_index'),
            'attr'=> ['id'=>'cave_person_search'],
            'parameters'=>$xparams->getNode('cave_backend')
        ])->handleRequest($request);

        if($search_form->isSubmitted()){//POST
            if($search_form->isValid()){
                $person = $search_form->getData();
            }else{
                return $this->render('@GptCavebackend/partial/form/error/error_fielddefinitionserializer.html.twig',
                    ['error'=> FormErrorsFielddefinitionSerializer::serializeFielddefinitions(
                        $search_form,
                        $this->getDoctrine()->getRepository('GptCaveBundle:Fielddefinition'),
                        $request->getLocale()
                    )]);
            }
        }else{//GET
            $person= new Person();
        }

        list($paginator, $result) = $repository->pageByPerson($person ,$page,$ipp );

        return $this->render(
        '@GptCavebackend/content/person/index_ajax.html.twig',
        array(
            'arrayParams'=>$this->controllerParams->indexParams(),
            'entities' => $result,//resultados filtrados
            "entity_token"=>$this->container->get('security.csrf.token_manager')->getToken('person_token'),
            'paginator'=>$paginator
        ));
    }

    /**
     * Crea una nuevo registro
     *
     * @Route("/person/new",
     *     name="cave_backend_person_new",
     *     methods={"GET","POST"})
     * @param Request $request
     * @return Response
     * @throws NonUniqueResultException
     */
    public function newAction(Request $request)
    {
        $xparams= $this->getParams('new');
        $systemparameterService = $this->get('cave_backend.service.system_params');//parámetros en db
        $view = array();//array para la vista
        $entity = new Person();
        $em = $this->getDoctrine()->getManager();
        $availableForms = (array)$xparams->get('cave_backend:table_generate_keys:person', 'auto');
        $formTypes  = $availableForms;
        $organisationRepository = $em->getRepository('GptCaveBundle:Organisation');
        $organisationDbm = $systemparameterService->getOrganisationdbm();

        if($organisationDbm == null){
            return $this->redirectToRoute('cave_backend_organisation_new');
        }

        if(in_array('man', $availableForms) &&
            $organisationRepository->countIdGenerators($organisationDbm)>0) {
            $formTypes[] = 'man';
        }else{
            /**
             * Si NO hay otras organizaciones creadoras de registros, no puedo crear registros manualmente.
             */
            $formTypes = array_diff($formTypes,['man']);
        }

        $view['activeForm'] = current($formTypes);

        foreach($formTypes as $type){

            $class= 'App\GptCavebackendBundle\Form\Type\Person\\'.'Id'.ucfirst($type).'FormType';
            if(!class_exists($class)) continue;
            $form = $this->createForm($class, $entity, array(
                'method' => 'POST',
                'parameters'=>[
                    'xparams'=>$xparams->getNode('cave_backend'),
                    'adminorg'=>$organisationDbm
                ]));

            /*
             * Posible TranslatableException en EventListener
             */
            try {
                $form->handleRequest($request);
            }catch (Exception $ex){
                if($form->isSubmitted()){
                    $view['activeForm'] = $type;
                }
                if($ex instanceof TranslatableException){
                    $ex->trans($this->get('translator'));
                }
                $form->addError(new FormError($ex->getMessage()));
                $view['forms'][$type]= $form->createView();
                break;
            }

            if($form->isSubmitted() && $form->isValid())
            {//tratamos de guardar

                $view['activeForm'] = $type;

                try{

                    if($type=='man'){
                        $metadata = $em->getClassMetaData(get_class($entity));
                        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);
                        $metadata->setIdGenerator(new AssignedGenerator());
                    }
                    $em->persist($entity);
                    $em->flush();
                    $em->clear();
                    return $this->redirectToRoute('cave_backend_person_edit', array('id' => $entity->getPersonid()));
                }catch (Exception $ex){
                    if($ex instanceof TranslatableException){
                        $ex->trans($this->get('translator'));
                    }
                    $form->addError(new FormError($ex->getMessage()));
                }
            }
            $view['forms'][$type]= $form->createView();
        }

      return $this->render(
            '@GptCavebackend/content/person/new.html.twig',
            array_merge($view, ['xparams'=>$xparams])
      );
    }

    /**
     * Edita y guarda un registro
     *
     * @Route("/person/edit/{id}",
     *     name="cave_backend_person_edit",
     *     methods={"GET","POST"})
     * @param Request $request
     * @param Person $person
     * @return Response|JsonResponse
     * @throws Exception
     * @throws NonUniqueResultException
     */
    public function editAction(Request $request, Person $person)
    {
        $data = array();
        $xparams= $this->getParams('edit', $person);

        $deleteForm = $this->createDeleteForm($person);
        $form = $this->createForm( PersonType::class,
                                        $person, array(
                                            'attr'=> ['id'=>'cave_person_form'],
                                            'method' => 'POST',
                                            'parameters'=>$xparams->getNode('cave_backend'),
                                            'translator'=>$this->get("translator")
                                            )
                                        )->handleRequest($request);

        if (!$form->isSubmitted())
        {//pidiendo el formulario
            return $this->render('@GptCavebackend/content/person/edit.html.twig', array(
                'arrayParams'=>$this->controllerParams->indexParams(),
                'form' => $form->createView(),
                'delete_form' => $deleteForm->createView(),
                'person'=>$person
            ));
        }
        if(!$request->isXmlHttpRequest())
        {
            throw new HttpException(403, sprintf("Forbidden request method %s", $request->getMethod()));
        }

        $em = $this->getDoctrine()->getManager();

        if ($form->isValid()) {
            $entity = $form->getData();
//            $em->getConnection()->beginTransaction(); // Tansacciones. suspend auto-commit
            try{
                $em->persist($entity);
                $em->flush();
                $em->clear();
//                $em->getConnection()->commit();
            }catch (Exception $ex){
//                    $em->getConnection()->rollBack();
                if($ex instanceof TranslatableException){
                    $ex->trans($this->get("translator"));
                }
                //error para mustache
                return new JsonResponse($data = ['error'=>['form'=>PersonType::class,'global'=>[$ex->getMessage()]]]);
            }           
        }else{//catch errors
            $data['error']= FormErrorsFielddefinitionSerializer::serializeFielddefinitions($form,
                $this->getDoctrine()->getRepository('GptCaveBundle:Fielddefinition'),
                $request->getLocale());
        }
        return new JsonResponse($data);
    }

    /**
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
     * Genera el Delete form
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
