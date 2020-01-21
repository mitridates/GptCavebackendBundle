<?php
namespace App\GptCavebackendBundle\Controller;
use App\GptCaveBundle\Entity\Cave;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * Cave controller.
 */
class GmapController extends AbstractController
{

    /**
     * Datos en csv
     *
     * @Route("/listado",
     *     name="cave_backend_gmap_listado",
     *     methods={"GET","POST"})
     * @param Request $request
     * @return Response
     */
    public function listadoAction(Request $request){
        // instantiation, when using it inside the Symfony framework
        $serializer = new Serializer([new ObjectNormalizer()], [new CsvEncoder(';')]);

        $data = $this->getDoctrine()->getManager()->getRepository(Cave::class)->findAll();

        //array output
        $out= array();
//        $out[]=['nombre', 'lat', 'lng', 'localizacion', 'historia', 'url'];

        foreach($data as $row) {
            if(empty($row->getLatitude())){ continue;}

            $out[]=[
                'nombre'=>$row->getName(),
                'lat'=>$row->getLatitude(),
                'lng'=>$row->getLongitude(),

                'url'=>'<a href="localhost/'.$this->generateUrl('cave_backend_cave_edit', array('id'=>$row->getCaveid())).'">editar</a>'
            ];
        }
        // encoding contents in CSV format
        ;

        $response = new Response($serializer->encode($out, 'csv'));

        return $response;
    }


    /**
     * filtra el formulario de búsqueda y retorna html para  paginar
     *
     * @Route("/boundarypoints",
     *     name="cave_backend_gmap_boundarypoints",
     *     methods={"GET","POST"})
     * @param Request $request
     * @return Response
     */
    public function boundarypointsAction(Request $request){

        $repository = $this->getDoctrine()->getRepository('GptCaveBundle:Backend');
        $xparams    = new Arraypath($this->getParameter('cave_backend'));
        $itemsPerMap= $xparams->get('admin:cave:itemsPerMap', 500);
        $page  = $request->get('page', 1);
        //bounds
        $nelat =  $request->get('nelat', 39.220);
        $nelng =  $request->get('nelng', 3.269);
        $swlat =  $request->get('swlat', 37.060);
        $swlng =  $request->get('swlng', -5.355);

        //formulario de búsqueda para cavidades
        $params=[
            'method' => 'POST',
            'action'=>$this->generateUrl('cave_backend_cave_index'),
            'attr'=> ['id'=>'cave_cave_search'],//ajax
            'parameters'=>$xparams
        ];

        $search_form = $this->createForm(\App\GptCavebackendBundle\Form\Type\App\Cave\BackendmapsearchType::class , new  Cave() , $params);

        $search_form->handleRequest($request);        
        //TODO Eliminar filter()
        $caveFilter = (new BackendFilter())
                ->filter(($search_form->isSubmitted() && $search_form->isValid())? $search_form->getData() : null)
                ->selectBasicos()
                ->findBounds($nelat, $nelng, $swlat, $swlng)
                ->getFilter()
                ->setPagination($page, $itemsPerMap);     

        $data = $repository->getFilter($caveFilter->resultAs('array'));
        //// Parse the dbquery into geojson 
        //// ================================================
        //// ================================================
        //// Return markers as GeoJSON

        $geojson = array(
            'type'      => 'FeatureCollection',
            "bbox"=> [$swlat, $swlng, $nelat, $nelng],
            'features'  => []
         );
        foreach($data as $row) {
            $feature = array(
                'type' => 'Feature', 
              'geometry' => array(
                'type' => 'Point',
                'coordinates' => array((float)$row['longitude'], (float)$row['latitude'])
                ),
              'properties' => array()
                );
            $feature['properties']['edit']= $this->generateUrl('cave_backend_cave_edit', ['id'=>$row['caveid']]);
            foreach($row as $k=>$v){
                if(!in_array($k, ['latitude', 'longitude'])){
                    $feature['properties'][$k]= $v;
                }
            }
            \array_push($geojson['features'], $feature);
        }

        return new \Symfony\Component\HttpFoundation\JsonResponse(
            [
            'data'=>$data,
            'geojson' => $geojson,
            "entity_token"=>$this->container->get('security.csrf.token_manager')->getToken('cave_token'),
            ]
        ); 
    }    

    /**
     * filtra el formulario de búsqueda
     * @param Request $request
     * 
     * @return Response
     */
/*    public function ajaxformfilterAction(Request $request){

        $repository = $this->getDoctrine()->getRepository('GptCaveBundle:Backend');
        $xparams    = new Arraypath($this->getParameter('cave_backend'));
        $itemsPerMap= $xparams->get('admin:cave:itemsPerMap', 500);
        $page  = $request->get('page', 1);

        //formulario de búsqueda para cavidades
        $params=[
            'method' => 'POST',
            'action'=>$this->generateUrl('cave_backend_cave_index'),
            'attr'=> ['id'=>'cave_cave_search'],//ajax
            'parameters'=>$xparams
        ];

        $search_form = $this->createForm(\App\GptCavebackendBundle\Form\Type\App\Cave\BackendmapsearchType::class , new  Cave() , $params);

        $search_form->handleRequest($request);        

        $caveFilter = (new BackendFilter())
                ->filter(($search_form->isSubmitted() && $search_form->isValid())? $search_form->getData() : null)
                ->toArray()
                ->getFilter()
                ->setPagination($page, $itemsPerMap);     

        $positionFilter = (new BackendpositionFilter());

        $caveFilter->addSelect($positionFilter->getAlias().'.latitude')
                    ->addSelect($positionFilter->getAlias().'.longitude');        

        $caveFilter->leftJoin('caveposition', $positionFilter->getFilter());

        $data = $repository->getFilter($caveFilter);

        return $this->render(
            '@Backend/load/cave/page.html.twig', array(
            'page'=>'index_ajax',
            'entities' => $repository->getFilter($caveFilter),
            'geojson' => $this->getGeojson($data),   
            "entity_token"=>$this->container->get('security.csrf.token_manager')->getToken('cave_token'),
            'paginator'=>$caveFilter->getPaginator()
        ));
    }*/

    private function getGeojson($data){
    //// Return markers as GeoJSON
        $geojson = array(
            'type'      => 'FeatureCollection',
            "bbox"=>[],//[$swlat, $swlng, $nelat, $nelng],
            'features'  => []
         );
        foreach($data as $row) {
            $feature = array(
                'type' => 'Feature', 
              'geometry' => array(
                'type' => 'Point',
                'coordinates' => array((float)$row['longitude'], (float)$row['latitude'])
                ),
              'properties' => array()
                );
            $feature['properties']['edit']= $this->generateUrl('cave_backend_cave_edit', ['id'=>$row['caveid']]);
            foreach($row as $k=>$v){
                if(!in_array($k, ['latitude', 'longitude'])){
                    $feature['properties'][$k]= $v;
                }
            }
            \array_push($geojson['features'], $feature);
        }        
        return $geojson;
    }

}