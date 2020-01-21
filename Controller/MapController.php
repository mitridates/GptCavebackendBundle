<?php
namespace App\GptCavebackendBundle\Controller;
use App\GptCavebackendBundle\Form\Error\FormErrorsFielddefinitionSerializer;
use App\GptCavebackendBundle\Form\Type\Map\MapsearchType;
use App\GptCavebackendBundle\Service\BackendParams\MapParams;
use App\GptCaveBundle\Entity\Map;
use App\Cave\LibBundle\Format\Select2;
use Doctrine\ORM\Id\AssignedGenerator;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
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
 * Map controller.
 */
class MapController extends AbstractController
{

    /**
     * Retorna los parámetros para controlador y action
     * @param mixed $action prefijo de la action y parámetros necesarios
     * @return Arraypath
     * @throws Exception
     */
    private function getParams($action)
    {
        $params = new MapParams(
            $this->get('translator'),
            $this->get('router'),
            $this->getParameter('cave_backend')
        );

        return call_user_func_array([$params, 'getActionParams'], func_get_args());
    }

    /**
     * Retorna una instancia de Map+sufijo o Map
     *
     * @param Map $map
     * @param string $name
     * @return object Map|Map{name}
     * @throws \UnexpectedValueException
     */
    private function getInstanceByName(Map $map, $name){
        if($name == "map"){
            return $map;
        }else{
            $class = "App\GptCaveBundle\Entity\Map".$name;
            if(!class_exists($class)){
                throw new UnexpectedValueException(sprintf('Unknow class suffix: %s'), $name);
            }
            return new $class($map);
        }
    }
    
    /**
     * Formulario de busqueda y página de inicio
     *
     * @Route("/map",
     *      name="cave_backend_map_index",
     *      methods={"GET"})
     * @return Response
     * @throws Exception
     */
    public function indexAction()
    {
        $xparams= $this->getParams('index');

        $form = $this->createForm( MapsearchType::class, new Map() , [
            'method' => 'POST',
            'attr'=> ['id'=>'search_form', 'novalidate'=>'novalidate'],//ajax
            'parameters'=>$xparams->getNode('cave_backend')
        ])->createView();

        return $this->render('@GptCavebackend/content/map/index.html.twig',
            array(
                'arrayParams'=>$this->controllerParams->indexParams(),
                'form'   => $form
            ));
    }

    /**
     * filtra el formulario de búsqueda y retorna html para  paginar
     *
     * @Route("/map/ajaxpager",
     *     name="cave_backend_map_ajaxpager",
     *     methods={"GET","POST"})
     * @param Request $request
     * @return Response
     * @throws Exception
     * @throws NonUniqueResultException
     */
    public function ajaxpagerAction(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository('GptCaveBundle:Map');

        $xparams= $this->getParams('ajaxpager');

        $page       = $request->get('page', 1);
        $ipp        = $request->get('ipp', $xparams->get('page:itemsPerPage', 20));

        $search_form = $this->createForm(MapsearchType::class, new Map() , [
            'method' => 'POST',
            'action'=>$this->generateUrl('cave_backend_map_index'),
            'attr'=> ['id'=>'cave_map_search'],
            'parameters'=>$xparams->getNode('cave_backend')
        ])->handleRequest($request);

        if($search_form->isSubmitted()){//POST
            if($search_form->isValid()){
                $entity = $search_form->getData();
            }else{
                return $this->render('@GptCavebackend/partial/form/error/error_fielddefinitionserializer.html.twig',
                    ['error'=> FormErrorsFielddefinitionSerializer::serializeFielddefinitions(
                        $search_form,
                        $this->getDoctrine()->getRepository('GptCaveBundle:Fielddefinition'),
                        $request->getLocale()
                    )]);
            }
        }else{//GET
            $entity= new Map();
        }

        list($paginator, $result) = $repository->pageByMap($entity, $page, $ipp);

        return $this->render(
        '@GptCavebackend/content/map/index_ajax.html.twig',
            array(
            'arrayParams'=>$this->controllerParams->indexParams(),
            'entities' => $result,//resultados filtrados
            "entity_token"=>$this->container->get('security.csrf.token_manager')->getToken('map_token'),
            'paginator'=>$paginator
        ));               
    }

    /**
     * Crea una nuevo registro
     *
     * @Route("/map/new",
     *     name="cave_backend_map_new",
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
        $entity = new Map();
        $em = $this->getDoctrine()->getManager();
        $availableForms = (array)$xparams->get('cave_backend:table_generate_keys:map', 'auto');
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

        foreach($formTypes as $type)
        {
            $class= 'App\GptCavebackendBundle\Form\Type\Map'.'\Id'.ucfirst($type).'FormType';
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
                    return $this->redirectToRoute('cave_backend_map_edit', array('id' => $entity->getMapid()));
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
            '@GptCavebackend/content/map/new.html.twig',
            array_merge($view, ['xparams'=>$xparams])
        );
    }

    /**
     * Carga la página de edición para un mapa o entidad relacionada.<br>
     * Si isXmlHttpRequest cargará sólo el formulario solicitado,<br>
     * si no, el esqueleto sin formulario
     *
     * @Route("/map/edit/{id}/{name}",
     *     name="cave_backend_map_edit",
     *     methods={"GET","POST"})
     * @param Request $request
     * @param Map $map
     * @param string|bool $name Nombre del formulario|entidad a editar
     * @return Response|JsonResponse
     * @throws Exception
     */
    public function editAction(Request $request, Map $map, $name=null)
    {
        $xparams= $this->getParams('edit', $map);
        $forms = [];
        $formnames = array_merge($xparams->get('page:onetomany'), $xparams->get('page:onetoone'), ['map']);

        /*Variables para el template*/
        $arr= [
            'arrayParams'=>$this->controllerParams->indexParams(),
            'name'=>$name,
            "map"=>$map,
            'onetoone'=> $xparams->get('page:onetoone'),
            "onetomany"=>  $xparams->get('page:onetomany'),
            "delete_token"=>$this->container->get('security.csrf.token_manager')->getToken('delete_token')
        ];
        /*
         * En la petición XmlHttpRequest, sólo cargamos el formulario solicitado.
         */
        if($request->isXmlHttpRequest()){
            //cargamos el type según $name
            $instance = $this->getInstanceByName($map, $name);
            //en las entidades distintas a Map la propiedad para map_id es map
            $property = ($map instanceof $instance)? 'mapid' : 'map';

            $registry= $this->getDoctrine()->getRepository(get_class($instance))
                ->findOneBy([$property=>$map->getMapid()]);

            //estamos editando un registro o creando uno nuevo si no existe
            $arr['form'] =   $this->createForm(
                sprintf('%s\%s', 'App\GptCavebackendBundle\Form\Type\Map', 'Edit'.  ucfirst($name)."Type"),
                ($registry===null) ? $instance : $registry,
                [
                    "attr"=> ['id'=>'map_'.$name.'_form', "autocomplete" => "off"],
                    "parameters"=>$xparams->getNode('cave_backend'),
                ])->createView();

            return $this->render(
                "@GptCavebackend/content/map/edit_ajax.html.twig", $arr);
        }

        /*
         * En la petición de http://.../map/edit cargamos la página
         * y mandamos un array de formularios disponibles para el menú
         */
        foreach($formnames as $n){
            $forms[$n] = false;
        }
        $arr['forms']= $forms;
        $arr['delete_form']=  $this->createDeleteForm($map)->createView();
        return $this->render("@GptCavebackend/content/map/edit.html.twig", $arr);
    }

    /**
     * Paginador para entidades con relaciones OneToMany
     *
     * @Route("/map/onetomanypager/{id}/{name}",
     *     name="cave_backend_map_onetomany",
     *     methods={"GET","POST"})
     * @param Request $request
     * @param Map $map
     * @param string $name sufijo del formulario|entidad a cargar
     * @return Response
     * @throws NonUniqueResultException
     */
    public function onetomanypagerAction(Request $request, Map $map, $name)
    {
        $repository = $this->getDoctrine()->getRepository("CaveBundle:Map");
        $xparams = new Arraypath($this->getParameter('cave_backend'));
        $page       = $request->get('page', 1);
        $ipp        = $request->get('ipp', $xparams->get('section:map:itemsPerPage', $xparams->get('section:default:itemsPerPage', 20)));

        list($paginator, $result) = $repository->pageOnetomanyByMapId('App\GptCaveBundle\Entity\Map'.$name, $map->getMapid(), $page, $ipp);

        return $this->render(
            "@GptCavebackend/content/map/tabs/".$name."/search_result/index.html.twig", array(
            "name"=>$name,
            "entity_token"=>$this->container->get('security.csrf.token_manager')->getToken('entity_token'),
            "map"=>$map,
            "entities" =>$result,
            "paginator"=>$paginator
        ));
    }

    /**
     * Guarda formularios para Map y entidades OneToOne
     *
     * @Route("/map/save/{id}/{name}",
     *     name="cave_backend_map_save",
     *     methods={"GET","POST"})
     * @param Request $request
     * @param Map $map
     * @param string $name nombre del formulario|entidad a cargar
     * @return JsonResponse
     * @throws Exception
     */
    public function saveAction(Request $request, Map $map, $name)
    {
        $xparams= $this->getParams('getBase');

        if(!$request->isXmlHttpRequest()){
            throw new HttpException(403, sprintf("Forbidden request method %s", $request->getMethod()));
        }

        $data = [];

        if($name !=='map' && !in_array($name, $xparams->get('page:onetoone'))){//error para dev
            return new JsonResponse(['error'=> [
                'title'=>$this->get("translator")->trans('critical.unknow.form',['%name%'=>$name,] , "caveerrors")
            ]]);
        }  

        $em = $this->getDoctrine()->getManager();

        $instance = $this->getInstanceByName($map, $name);
        $registry= $this->getDoctrine()
                ->getManager()
                ->getRepository(get_class($instance))
                ->findOneBy([(($instance instanceof $map)? 'mapid': 'map') =>$map->getMapid()]);

        $form = $this->createForm(
                sprintf('%s\\%s', 'App\GptCavebackendBundle\Form\Type\Map', 'Edit'.ucfirst($name)."Type"),
                ($registry===null) ? $instance : $registry,
                [
                    'parameters'=>$xparams->getNode('cave_backend'),
                    'translator'=>$this->get("translator")
                ])->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            /*$em->getConnection()->beginTransaction(); // Tansacciones. suspend auto-commit*/
            $entity = $form->getData();

            try{
                $em->persist($entity);
                $em->flush();
                $em->clear();
                /*$em->getConnection()->commit();*/
            }catch (Exception $ex){
                /*$em->getConnection()->rollBack();*/
                if($ex instanceof TranslatableException){
                    $ex->trans($this->get("translator"));
                }
                $data = ['error'=> ['global'=>$ex->getMessage()]];
            }
        }else{
            $data['error']= FormErrorsFielddefinitionSerializer::serializeFielddefinitions($form,
                $this->getDoctrine()->getRepository('GptCaveBundle:Fielddefinition'),
                $request->getLocale());
        }
        return new JsonResponse($data);
    }

    /**
     * Edición en ventana modal para entidades onetomany
     * La request es XmlHttpRequest
     *
     * @Route("/map/editonetomany/{id}/{sequence}/{name}",
     *     name="cave_backend_map_editonetomany",
     *     methods={"POST"})
     * @param Request $request
     * @param Map $map
     * @param int $sequence
     * @param string $name nombre del formulario|entidad a cargar
     * @return JsonResponse
     * @throws NonUniqueResultException
     */
    public function editonetomanyAction(Request $request, Map $map, $sequence, $name)
    {
        if(!$request->isXmlHttpRequest()){
            throw new HttpException(403, sprintf("Forbidden request method %s", $request->getMethod()));
        }
        
        $data = [];
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository("App\GptCaveBundle\Entity\Map".$name)->findOneBy(['map'=>$map->getMapid(), 'sequence'=>$sequence]);
        $options= array(
                    "attr"=> ['class'=>'map-onetomany map-'.$name.'-modal-form','id'=>'map-'.$name.'-modal-form'],
                    "parameters"=>new Arraypath($this->getParameter('cave_backend')),
                    "translator"=>$this->get('translator')
        );           

        if (null=== $entity) {
            $data = ['error'=> [
                'title'=>$this->get("translator")->trans('error.registry.not.found', ['%more%'=>sprintf('Map ID: %s, sequence: %s', $map->getMapid(), $sequence)], "caveerrors")
            ]];    
            return new JsonResponse($data);
        }

        /**
         * Unica forma de pasar los attr al formulario
         * Necesario para modificar el id del formulario y evitar colisiones
         * con otro formulario en la misma ventana
         * los id quedan map-'.$name.'-modal-form_{fieldname}
         */
        $form = $this->get('form.factory')->createNamedBuilder(
            'edit-modal-'.$name,
            sprintf('%s\\%s', 'App\GptCavebackendBundle\Form\Type\Map', 'Edit'.ucfirst($name)."Type"),
            $entity, $options)->getForm();

        $form->handleRequest($request);

        if (!$form->isSubmitted())
        {
            return $this->render("@Backend/load/map/page.html.twig", array(
                "page"=>"onetomany_modal_form",
                "form" => $form->createView(),
                "name" => $name,
                "entity"=>$entity,
                "map"=>$map
            ));            
        }

        if ($form->isValid()) {
            /*$em->getConnection()->beginTransaction(); // Tansacciones. suspend auto-commit*/
            try{
                $em->persist($entity);
                $em->flush();
                $em->clear();
                /*$em->getConnection()->commit();*/
            }catch (Exception $ex){
                /*$em->getConnection()->rollBack();*/
                if($ex instanceof TranslatableException){
                    $ex->trans($this->get("translator"));
                }
                $data = ['error'=> ['global'=>['Unknow Exception: '.$ex->getMessage()]]];
            }
        }else{
            $data['error']=  FormErrorsFielddefinitionSerializer::serializeFielddefinitions($form,
                $this->getDoctrine()->getRepository('GptCaveBundle:Fielddefinition'),
                $request->getLocale());
        }
         return new JsonResponse($data);
    }

    /**
     * Formulario para entidades onetomany
     * La request es XmlHttpRequest
     *
     * @Route("/map/newonetomany/{id}/{name}",
     *     name="cave_backend_map_newonetomany",
     *     methods={"POST"})
     * @param Request $request
     * @param Map $map
     * @param string $name nombre del formulario|entidad a cargar
     * @return JsonResponse
     * @throws NonUniqueResultException
     */
    public function newonetomanyAction(Request $request, Map $map, $name)
    {
        if(!$request->isXmlHttpRequest()){
            throw new HttpException(403, sprintf("Forbidden request method %s", $request->getMethod()));
        }        
        $data = [];
        $options = [
            'parameters'=>new Arraypath($this->getParameter('cave_backend')),
            'translator'=>$this->get('translator')
        ];
        $form = $this->createForm(
                sprintf('%s\\%s', 'App\GptCavebackendBundle\Form\Type\Map', 'Edit'.ucfirst($name)."Type"),
               $this->getInstanceByName($map, $name), $options);

        $form->handleRequest($request);
        
        if (!$form->isSubmitted()) {
            $data = ['error'=> [
                'global'=>[$this->get("translator")->trans('error.form.not.found', [], "caveerrors")]]
            ];
            return new JsonResponse($data);
        }

        $em = $this->getDoctrine()->getManager();

        if ($form->isValid()) {
            $entity = $form->getData();
            $entity->setMap($map);

            try{
                $em->persist($entity);
                $em->flush();
                $em->clear();
                /*$em->getConnection()->commit();*/
            }catch (Exception $ex){
                /*$em->getConnection()->rollBack();*/
                if($ex instanceof TranslatableException){
                    $ex->trans($this->get("translator"));
                }

                $data = ['error'=> [
                    'global'=>[$ex->getMessage()]]
                ];
            }
        }else{//catch errors
            $data['error']=  FormErrorsFielddefinitionSerializer::serializeFielddefinitions($form,
                $this->getDoctrine()->getRepository('GptCaveBundle:Fielddefinition'),
                $request->getLocale());
        }

        return new JsonResponse($data);
    } 

    /**
     * Borrado mediante botones para entidades OnetoMany.
     * Validado mediante el token 'entity_token'.
     *
     * @Route("/map/deleteonetomany/{id}/{sequence}/{name}",
     *     name="cave_backend_map_deleteonetomany",
     *     methods={"POST"})
     * @param Request $request
     * @param Map $map
     * @param int $sequence PK
     * @param string $name nombre del formulario|entidad a borrar
     * @return JsonResponse
     */
    public function deleteonetomanyAction(Request $request, Map $map, $sequence, $name)
    {
        $data = [];
        if(!$request->isXmlHttpRequest()){
            throw new HttpException(403, sprintf("Forbidden request method %s", $request->getMethod()));
        }        
        if ($this->isCsrfTokenValid('entity_token', $request->get('_token'))) {
                $em = $this->getDoctrine()->getManager();
                $entity= $em->getRepository("App\GptCaveBundle\Entity\Map".$name)->findOneBy(['map'=>$map->getMapid(), 'sequence'=>$sequence]);
            try{
                $em->remove($entity);
                $em->flush();
            }catch (Exception $ex){
                if($ex instanceof TranslatableException){
                    $ex->trans($this->get("translator"));
                }
                $data = ['error'=> [
                    'global'=>[$ex->getMessage()]]
                ];
            }
        }else{
            $data = ['error'=> [
                'global'=>[$this->get("translator")->trans('warning.invalid.token',[], "caveerrors")]]
            ];
        }
            return new JsonResponse($data);
    }  

    /**
     * Borrado mediante botones para entidades OnetoOne.
     * Validado mediante el token 'entity_token'
     *
     * @Route("/map/deleteonetoone/{id}/{name}",
     *     name="cave_backend_map_deleteonetoone",
     *     methods={"POST"})
     * @param Request $request
     * @param Map $map
     * @param string $name nombre del formulario|entidad a borrar
     * @return JsonResponse
     */
    public function deleteonetooneAction(Request $request, Map $map, $name)
    {
        if(!$request->isXmlHttpRequest()){
            throw new HttpException(403, sprintf("Forbidden request method %s", $request->getMethod()));
        }        
        if ($this->isCsrfTokenValid('delete_token', $request->get('delete_token')))
        {
            $em = $this->getDoctrine()->getManager();
            $entity= $em->getRepository("App\GptCaveBundle\Entity\Map".$name)->findOneBy(['map'=>$map->getMapid()]);

            if(null!==$entity){
                $em->remove($entity);
                $em->flush();
            }
            $data = ['success'=> [
                'title'=>$this->get("translator")->trans('form.successfully.deleted',['%id%'=>$map->getMapid()], "cavemessages")
            ]];  
        }else{
            $data = ['error'=> [
                'global'=>[$this->get("translator")->trans('warning.invalid.token',[], "caveerrors")]]
            ];                         
        }
        return new JsonResponse($data);
    }   

    /**
     * Delete Map
     *
     * @Route("/map/delete/{id}",
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
            $msg= $this->get('translator')->trans('id.successfully.deleted', array('%id%'=>$map->getMapid()), 'cavemessages');
            $em = $this->getDoctrine()->getManager();
            try{
                //TODO no debería eliminarse de la base de datos ya que
                // el registro no puede reutilizarse
                $em->remove($map);
                $em->flush();
                $this->addFlash('success', $msg);

            }catch(Exception $ex){
                $this->addFlash('danger', $ex->getMessage() );
                return $this->redirectToRoute('cave_map_edit', array('id' => $map->getMapid()));
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

    /**
     * Respuesta json para select2.
     *
     * @Route("/map/json",
     *     name="cave_backend_map_json",
     *     methods={"GET","POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function jsonAction(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository('GptCaveBundle:Map');
        $string     = $request->get('term');
        $maps       = $repository->filterByString($string);

        return new JsonResponse(
            [
                'out'=>(new Select2($maps))->getVsprintfArray('mapid', '%s' , ['name'])
            ]
        ); 
    }
}