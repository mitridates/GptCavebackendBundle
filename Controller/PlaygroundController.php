<?php
namespace App\GptCavebackendBundle\Controller;
use App\GptCavebackendBundle\Form\Type\Cave\CavemapsearchType;
use App\GptCaveBundle\Entity\Cave;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PlaygroundController extends AbstractController
{
    /**
     * página de inicio
     *
     * @Route("/playground",
     *     name="cave_backend_playground_index",
     *     methods={"GET","POST"})
     * @return Response
     * @throws \Exception
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->getConfiguration()->setSQLLogger(null);        
        $xparams    = new Arraypath($this->getParameter('cave_backend'));

        //formulario de búsqueda para cavidades
        $params=[
            'method' => 'POST',
            'action'=>$this->generateUrl('cave_backend_cave_index'),
            'attr'=> ['id'=>'cave_cave_search'],//ajax
            'parameters'=>$xparams
        ];
        unset($em, $xparams);
        $search_form = $this->createForm(CavemapsearchType::class , new  Cave() , $params);

        return $this->render('@Backend/load/playground/page.html.twig', array(
            'form'   => $search_form->createView(),
            "entity_token"=>$this->container->get('security.csrf.token_manager')->getToken('cave_token'),
            'page'=>'index'
        ));
    }

    /**
     * filtra el formulario de búsqueda y retorna html $().load()
     * El resultado es un div con paginación y tabla de resultados
     *
     * @Route("/playground/ajaxpager",
     *     name="cave_backend_playground_ajaxpager",
     *     methods={"GET","POST"})
     * @param Request $request
     * 
     * @return Response
     */
    public function ajaxpagerAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->getConfiguration()->setSQLLogger(null);
        $repository = $em->getRepository('GptCaveBundle:Cave');
        $xparams    = new Arraypath($this->getParameter('cave_backend'));
        $page       = $request->get('page', 1);
        $ipp        = $request->get('ipp',$xparams->get('section:cave:itemsPerPage', 50));

        //Formulario
        $params=[
            'method' => 'POST',
            'action'=>$this->generateUrl('cave_backend_cave_index'),
            'attr'=> ['id'=>'cave_cave_search'],//ajax
            'parameters'=>$xparams
        ];
        $search_form = $this->createForm(CavemapsearchType::class, new  Cave() , $params);
        /**
         * añadimos filtros a la búsqueda.
         */
        $search_form->handleRequest($request);
        $caveExprFilter =null;
        exit('todo mal');

      //  $caveExprFilter = new PlaygroundExpr();
        if($search_form->isSubmitted() && $search_form->isValid()){
            $caveExprFilter->filter($search_form->getData());
        }else{
            $caveExprFilter->filter(NULL); //a chorro
        }

        $caveExprFilter->selectDataTableFields();


        $filter=  $caveExprFilter->getFilter()->setPagination($page, $ipp);      

        $data = $repository->getFilter($filter);
        $em->clear();
        unset($caveExprFilter, $search_form, $xparams, $page, $ipp, $repository, $em, $request);//free memo

        //// Parse the dbquery into geojson
        //// ================================================
        //// ================================================
        //// Return markers as GeoJSON

        $geojson = array(
            'type'      => 'FeatureCollection',
            "bbox"=> [],
            'features'  => []
         );
        foreach($data as $row) {
                if($row['latitude']!==null && $row['longitude']!==null){
                $feature = array(
                    'type' => 'Feature', 
                  'geometry' => array(
                    'type' => 'Point',
                    'coordinates' => array((float)$row['latitude'],(float)$row['longitude'])
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
        }       
        $paginator = $filter->getPaginator();

        unset( $filter, $feature, $row);//free memo

        return $this->render(
            '@Backend/load/playground/page.html.twig', array(
            'page'=>'index_ajax',
            'data'=>$data,
            'geojson'   => $geojson,
            "entity_token"=>$this->container->get('security.csrf.token_manager')->getToken('cave_map_token'),
            'paginator'=>$paginator
        ));

    }

    /**
     * test plantilla
     *
     * @Route("/playground/test/{name}",
     *     name="cave_backend_playground_test",
     *     methods={"GET"})
     * @return Response
     */
    public function testAction($name)
    {
        return $this->render('@Backend/load/playground/page.html.twig', array(
            'page'=>$name
        ));
    }

}
