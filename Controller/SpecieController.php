<?php
namespace App\GptCavebackendBundle\Controller;
use App\GptCavebackendBundle\Form\Error\FormErrorsFielddefinitionSerializer;
use App\GptCavebackendBundle\Form\Type\Specie\SpecieType;
use App\GptCavebackendBundle\Form\Type\Specie\SpeciesearchType;
use App\GptCavebackendBundle\Service\BackendParams\SpecieParams;
use App\GptCaveBundle\Entity\Specie;
use App\Cave\LibBundle\Format\Select2;
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
 * Specie controller.
 */
class SpecieController extends AbstractController
{

    /**
     * Retorna los parámetros para controlador y action
     * @param mixed $action prefijo de la action y parámetros necesarios
     * @return Arraypath
     * @throws Exception
     */
    private function getParams($action)
    {
        $params = new SpecieParams(
            $this->get('translator'),
            $this->get('router'),
            $this->getParameter('cave_backend')
        );

        return call_user_func_array([$params, 'getActionParams'], func_get_args());
    }

    /**
     * Formulario de busqueda y página de inicio
     *
     * @Route("/specie",
     *     name="cave_backend_specie_index")
     * @return Response
     * @throws \Exception
     */
    public function indexAction()
    {
        $xparams= $this->getParams('index');

        $form = $this->createForm(SpeciesearchType::class, new Specie() , [
            'method' => 'POST',
            'attr'=> ['id'=>'search_form', 'novalidate'=>'novalidate'],//ajax
            'parameters'=>$xparams->getNode('cave_backend')
        ])->createView();

        return $this->render('@GptCavebackend/content/specie/index.html.twig',
            array(
                'arrayParams'=>$this->controllerParams->indexParams(),
                'form'   => $form
        ));
    }

    /**
     * filtra el formulario de búsqueda y retorna html para  paginar
     *
     * @Route("/specie/ajaxpager",
     *     name="cave_backend_specie_ajaxpager",
     *     methods={"GET","POST"})
     * @param Request $request
     * @return Response
     * @throws Exception
     * @throws NonUniqueResultException
     */
    public function ajaxpagerAction(Request $request){

        $repository = $this->getDoctrine()->getRepository('GptCaveBundle:Specie');

        $xparams= $this->getParams('ajaxpager');

        $page       = (int) $request->get('page', 1);
        $ipp        = (int) $request->get('ipp', $xparams->get('page:itemsPerPage', 20));

        $search_form = $this->createForm(SpeciesearchType::class, new Specie() , [
            'method' => 'POST',
            'action'=>$this->generateUrl('cave_backend_specie_index'),
            'attr'=> ['id'=>'cave_specie_search'],
            'parameters'=>$xparams->getNode('cave_backend')
        ])->handleRequest($request);

        if($search_form->isSubmitted()){//POST
            if($search_form->isValid()){
                $specie = $search_form->getData();
            }else{
                return $this->render('@GptCavebackend/partial/form/error/error_fielddefinitionserializer.html.twig',
                    ['error'=> FormErrorsFielddefinitionSerializer::serializeFielddefinitions(
                        $search_form,
                        $this->getDoctrine()->getRepository('GptCaveBundle:Fielddefinition'),
                        $request->getLocale()
                    )]);
            }
        }else{//GET
            $specie= new Specie();
        }

        list($paginator, $result) = $repository->pageBySpecie($specie ,$page,$ipp );

        return $this->render(
        '@GptCavebackend/content/specie/index_ajax.html.twig',
        array(
            'arrayParams'=>$this->controllerParams->indexParams(),
            'entities' => $result,//resultados filtrados
            "entity_token"=>$this->container->get('security.csrf.token_manager')->getToken('specie_token'),
            'paginator'=>$paginator
        ));
    }

    /**
     * Crea una nuevo registro
     *
     * @Route("/specie/new",
     *     name="cave_backend_specie_new",
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
        $entity = new Specie();
        $em = $this->getDoctrine()->getManager();
        $availableForms = (array)$xparams->get('cave_backend:table_generate_keys:specie', 'auto');
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

            $class= 'App\GptCavebackendBundle\Form\Type\Specie\\'.'Id'.ucfirst($type).'FormType';
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
                    return $this->redirectToRoute('cave_backend_specie_edit', array('id' => $entity->getSpecieid()));
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
            '@GptCavebackend/content/specie/new.html.twig',
            array_merge($view, ['xparams'=>$xparams])
        );
    }

    /**
     * Edita y guarda un registro
     *
     * @Route("/specie/edit/{id}",
     *     name="cave_backend_specie_edit",
     *     methods={"GET","POST"})
     * @param Request $request
     * @param Specie $specie
     * @return Response|JsonResponse
     * @throws Exception
     */
    public function editAction(Request $request, Specie $specie)
    {
        $data = array();
        $xparams= $this->getParams('edit', $specie);

        $deleteForm = $this->createDeleteForm($specie);
        $form = $this->createForm(SpecieType::class,
                                        $specie, array(
                                            'attr'=> ['id'=>'cave_specie_form'],
                                            'method' => 'POST',
                                            'parameters'=>$xparams
                                            )
                                        )->handleRequest($request);

        if (!$form->isSubmitted())
        {//pidiendo el formulario
            return $this->render('@GptCavebackend/content/specie/edit.html.twig', array(
                'arrayParams'=>$this->controllerParams->indexParams(),
                'form' => $form->createView(),
                'delete_form' => $deleteForm->createView(),
                'specie'=>$specie
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
                return new JsonResponse($data = ['error'=>['form'=>SpecieType::class,'global'=>[$ex->getMessage()]]]);
            }           
        }else{//catch errors
            $data['error']= FormErrorsFielddefinitionSerializer::serializeFielddefinitions($form,
                $this->getDoctrine()->getRepository('GptCaveBundle:Fielddefinition'),
                $request->getLocale());
        }
        return new JsonResponse($data);
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
                //TODO no debería eliminarse de la base de datos ya que
                // el registro no puede reutilizarse
                $em->remove($specie);
                $em->flush();
                $this->addFlash('success', $msg);
            }catch(Exception $ex){
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