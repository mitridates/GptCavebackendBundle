<?php
namespace App\GptCavebackendBundle\Controller;
use App\GptCavebackendBundle\Import\Speleobase;
use App\GptCaveBundle\Doctrine\IdGenerator;
use App\GptCaveBundle\Entity\Cave;
use App\Cave\LibBundle\Geo\gPoint;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\Exception\NoConfigurationException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Peticiones asíncronas comunes para muchos formularios
 */
class ImportController extends AbstractController
{

    /**
     * Carga un fichero exportado de Speleobase.
     * Para una carga óptima es necesario:
     * - Convertir los nombres asociaciones "CAEXPLNAME" a los ID de Organización existentes en la base de datos
     * - Establecer los campos admin2, admin3 e indicar el metodo segun la columna (CAPROVDEP, CALOCAL, CAAREA)
     *      ya que su uso depende del usuario.
     * - Estandarizar CAXYMETHOD como [metodo año], siendo "metodo" uno de los establecidos en la base de datos. Ej. GPS 2002
     * - Estandarizar CAZMETHOD como [metodo], siendo "metodo" uno de los establecidos en la base de datos. Ej. GPS
     * - Estandarizar CACORSYS como [gridzone geogeodetdatum]". Ej. "30S WGS84"
     *
     * @Route("/speleobase",
     *     name="cave_backend_import_speleobase",
     *     methods={"GET", "POST"})
     * @param Request $request
     * @return Response
     * @throws \Doctrine\ORM\Mapping\MappingException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function speleobaseAction(Request $request)
    {
        $systemparameterService = $this->get('cave_backend.service.system_params');

        if($systemparameterService->isEmpty()){
            throw new NoConfigurationException('No hay parametros activos en este momento');
        }

        $em = $this->getDoctrine()->getManager();

        $formCSV = $this->createFormBuilder()
                ->add('submitSpeleobaseCSV', \Symfony\Component\Form\Extension\Core\Type\FileType::class, array('label' => 'File to Submit'))
                ->getForm();
        $formGPX = $this->createFormBuilder()
                ->add('submitSpeleobaseGPX', \Symfony\Component\Form\Extension\Core\Type\FileType::class, array('label' => 'File to Submit'))
                ->getForm();        
        // Check if we are posting stuff
        if ($request->getMethod('post') == 'POST') {
            $formCSV->handleRequest($request);
            $formGPX->handleRequest($request);
            // If form is valid
            if($formCSV->isSubmitted() && $formCSV->isValid()) {//tratamos de guardar
                $this->processCvs($formCSV->get('submitSpeleobaseCSV'));
            }

            if($formGPX->isSubmitted() && $formGPX->isValid()) {//tratamos de guardar
                $this->processGpx($formGPX->get('submitSpeleobaseGPX'));
            }

         }

         return $this->render('@Backend/load/import/page.html.twig', array(
            'page'=>'speleobase',
            'formCSV'   =>$formCSV->createView(),
            'formGPX'   =>$formGPX->createView()
        ));
    }


    /**
     * @Route("/updateutmtolatlng",
     *     name="cave_backend_updateutmtolatlng",
     *     methods={"GET"})
     * @return Response
     */
    public function updateutmtolatlngAction(){
        $CaveRepository = $this->getDoctrine()->getManager()->getRepository(Cave::class);
        $data = $CaveRepository->getEastingNorthingDiscardNulllatlng();

        if(!empty($data)) {
        
            $gp = new gPoint();
            foreach ($data as $pt) {
                $gp->setUTM($pt['easting'], $pt['northing'], $pt['gridzone']);

                $gp->convertTMtoLL();
                $insert = [
                    'position_latitude' =>     $gp->Lat(),
                    'position_longitude' =>    $gp->Long(),
                    'geographic_geodetic_datum' => $pt['gridzone'],
                ];

                
                $this->apiUpdate(Cave::class, $insert, ['cave_id' => $pt['caveid']]);
            }
        }

        $response = new Response(
            sprintf('Actualizados: %s', count($data)),
            200,
            array('content-type' => 'text/html')
        );
        return $response;
    }

    /**
     * Insertar una fila en la tabla dada usando pares de datos key value.
     * @see http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/data-retrieval-and-manipulation.html#insert
     */
    private function processGpx($file){    
    //tratamos de guardar
        $gpoint = new gPoint();

        $gpx = \simplexml_load_file($file->getData());
        $getCaveIdFromSpeleobaseID= function($id){
            $em = $this->getDoctrine()->getManager();
            $qb = $em->createQueryBuilder('o');
            $qb->select('IDENTITY(o.cave) AS caveid, o.otherdbid, o.sequence')
                ->from('GptCaveBundle:Caveotherdbid', 'o')
                ->where('o.otherdbid = :id')
                ->setParameter('id', $id);                    
            $rs = $qb->setMaxResults(1)->getQuery()->getOneOrNullResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
            return $rs;

        };

        foreach ($gpx->wpt as $pt) {
            $gpoint->setLongLat((string) $pt['lon'], (string) $pt['lat']);
            $gpoint->convertLLtoTM(false);
            $insert= [
                'position_latitude'=>(string) $pt['lat'],
                'position_longitude'=>(string) $pt['lon'],
                'altitude'=>(string) $pt->ele,
                'position_easting'=>$gpoint->E(),
                'position_northing'=>$gpoint->N(),
                'geographic_geodetic_datum'=>$gpoint->Z(),
                'position_grid_ref_units'=> 4392
                ];

            $rs = $getCaveIdFromSpeleobaseID((string) $pt->name);
            if($rs!=null){
                $this->apiUpdate(Cave::class, $insert, ['cave_id'=> $rs['caveid']]);
            }
        }

        return;
     }

    /**
     * Insertar una fila en la tabla dada usando pares de datos key value.
     * @see http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/data-retrieval-and-manipulation.html#insert
     * @var string $class Class name
     * @throws \Doctrine\ORM\Mapping\MappingException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function processCvs($file){
        $systemparameterService = $this->get('cave_backend.service.system_params');
        $adminOrganisation = $systemparameterService->getParameters()->getOrganisationsite();
        $em = $this->getDoctrine()->getManager();

        $speleobase = new Speleobase($em, $systemparameterService->getParameters());

        $file_array = file($file->getData());

           try {
                $idGenerator = new IdGenerator();
            } catch (\RuntimeException $e) {
                throw $e;
            }
        $cave = new  Cave();
        $cave->setGeneratedby($adminOrganisation);
        
        $nextId = $idGenerator->generate($em, $cave);
        $prefix = substr($nextId, 0,5);//{2}country + {3}organisation
        $suffix = intval(substr($nextId, 5, 5));//{5}serial

        foreach ($file_array as $k=>$string) {
            if($k==0){//encabezamiento
                $speleobase->setHeader($string);
                continue;
            }
            //TODO: validar lineas con escasa/nula información
            if(!$entity = $speleobase->setEntity($string)){
                continue;
            }

            //Cave class array
            $data = $speleobase->data->get('cave_onetoone:'.get_class($cave));
            $this->apiInsert(get_class($cave), array_merge($data, ['cave_id'=>$nextId]));

            //Cave onetoone 1:1
            foreach($speleobase->data->get('cave_onetoone') as $class=> $data){
                if($class === get_class($cave)){
                    continue;
                }

                $data = array_merge($data, ['cave_id'=>$nextId]);
                $this->apiInsert($class, $data);
            }

            //Cave 1:N
            foreach($speleobase->data->get('cave_onetomany') as $class=> $data_arr){
                foreach($data_arr as $data){
                    $data = array_merge($data, ['cave_id'=>$nextId]);

                    $this->apiInsert($class, $data);
                }
            }
            //set next Cave ID
            $nextId = $prefix.\str_pad(++$suffix, 5, 0, \STR_PAD_LEFT);
        }
        return;
     }       

    /**
     * Update usando pares de datos key value.
     * @var string $class Class name
     * @var array $associative_array pair key value
     * @var string $caveid
     * @see http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/data-retrieval-and-manipulation.html#update
     */
    private function apiUpdate($class, $associative_array, $id_array){    
            $em = $this->getDoctrine()->getManager();

            $metadata = $em->getMetadataFactory()->getMetadataFor($class);
            foreach($associative_array as $f=>$v){
                $arr[$metadata->getColumnName($f)] =  $v;
            }            
            $em->getConnection()->update($metadata->getTableName(), $arr,  $id_array);
     }         

    /**
     * Insertar una fila en la tabla dada usando pares de datos key value.
     * @var string $class Class name
     * @var array $associative_array pair key value
     * @see http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/data-retrieval-and-manipulation.html#insert
     */
    private function apiInsert($class, $associative_array){    
            $em = $this->getDoctrine()->getManager();
            $metadata = $em->getMetadataFactory()->getMetadataFor($class);
            foreach($associative_array as $f=>$v){
                $arr[$metadata->getColumnName($f)] =  $v;
            }
            $em->getConnection()->insert($metadata->getTableName(), $arr);
     }    

}
