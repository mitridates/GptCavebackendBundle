<?php
namespace App\GptCavebackendBundle\Controller;
use App\GptCavebackendBundle\Repository\Admin1BackendRepository;
use App\GptCavebackendBundle\Repository\Admin2BackendRepository;
use App\GptCavebackendBundle\Repository\Admin3BackendRepository;
use App\GptCavebackendBundle\Repository\AreaBackendRepository;
use App\GptCavebackendBundle\Repository\ArticleBackendRepository;
use App\GptCavebackendBundle\Repository\CaveBackendRepository;
use App\GptCavebackendBundle\Repository\FielddefinitionBackendRepository;
use App\GptCavebackendBundle\Repository\MapBackendRepository;
use App\GptCavebackendBundle\Repository\MapserieBackendRepository;
use App\GptCavebackendBundle\Repository\OrganisationBackendRepository;
use App\GptCavebackendBundle\Repository\PersonBackendRepository;
use App\GptCavebackendBundle\Repository\SpecieBackendRepository;
use App\GptCavebackendBundle\Repository\SysparamBackendRepository;
use App\GptCavebackendBundle\Util\Select2;
use App\GptCaveBundle\Entity\Area;
use App\GptGeonamesBundle\Entity\Admin1;
use App\GptGeonamesBundle\Entity\Admin2;
use App\GptGeonamesBundle\Entity\Admin3;
use App\GptGeonamesBundle\Entity\Country;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class JsonController extends AbstractController
{

    /**
     * @Route("/json/subset/admin1", name="cave_backend_json_admin1", methods={"GET","POST"})
     * @param Request $request
     * @param Admin1BackendRepository $admin1BackendRepository
     * @return JsonResponse
     */
    public function jsonadmin1Action(Request $request, Admin1BackendRepository $admin1BackendRepository): JsonResponse
    {
        foreach(array('countryid', 'string', 'format', 'emptyOption', 'placeholder', 'page' ) as $val){
            switch ($val){
                case 'format': $req[$val] = $request->get($val) ?? 'suggest'; break;
                default: $req[$val] = $request->get($val);
            }
        }

        $k= array(
            'suggest' => array('label', 'value'),
            'select2' => array('text', 'id')
        );

        $em = $this->getDoctrine()->getManager();
        $alias = 'jsonadm1';
        $select= ['IDENTITY('.$alias.'.country) as country', $alias.'.name', $alias.'.admin1id'];
        $admin1 = new Admin1();

        if(!empty($req['countryid'])){
            $admin1->setCountry($country = $em->getReference(Country::class, $req['countryid']));
        }
        if(!empty($req['string'])){
            $admin1->setName($req['string']);
        }
        $data= $admin1BackendRepository->findByAdmin1($admin1, $alias, $select);

        $a = array();//output array
        //format result
        if(!empty($data)){
            //empty option
            if($req['emptyOption']){
                $block = array(
                    $k[$req['format']][0]=>'No result found',
                    $k[$req['format']][1]=>''
                );
                $a[] = $block;
            }
            foreach($data as $rs){
                $block = array(
                    $k[$req['format']][0]=>$rs['name'],
                    $k[$req['format']][1]=>$rs['admin1id']
                );

                $a[] = $block;
            }
        }
        return new JsonResponse(array('out'=>$a), 200);
    }

    /**
     * @Route("/json/subset/admin2", name="cave_backend_json_admin2", methods={"GET","POST"})
     * @param Request $request
     * @param Admin2BackendRepository $admin2BackendRepository
     * @return JsonResponse
     */
    public function jsonadmin2Action(Request $request, Admin2BackendRepository $admin2BackendRepository): JsonResponse
    {
        foreach(array('countryid', 'string', 'admin1id', 'format', 'emptyOption', 'placeholder', 'page') as $val){
            switch ($val){
                case 'format': $req[$val] = $request->get($val) ?? 'suggest'; break;
                default: $req[$val] = $request->get($val);
            }
        }
        $k= array(
            'suggest' => array('label', 'value'),
            'select2' => array('text', 'id')
        );

        $em = $this->getDoctrine()->getManager();
        $alias = 'jsonadm2';
        $select= ['IDENTITY('.$alias.'.country) as country', $alias.'.name', 'IDENTITY('.$alias.'.admin1) as admin1',  $alias.'.admin2id'];
        $admin2 = new Admin2();


        if(!empty($req['countryid'])){
            $admin2->setCountry($em->getReference(Country::class, $req['countryid']));
        }
        if(!empty($req['string'])){
            $admin2->setName($req['string']);
        }
        if(!empty($req['admin1id'])){
            $admin2->setAdmin1($em->getReference(Admin1::class, $req['admin1id']));
        }

        $data= $admin2BackendRepository->findByAdmin2($admin2, $alias, $select);

        $a = array();//output array
        //format result
        if(!empty($data)){

            //empty option
            if($req['emptyOption']){
                $block = array(
                    $k[$req['format']][0]=>'No result found',
                    $k[$req['format']][1]=>''
                );
                $a[] = $block;
            }

            foreach($data as $rs){
                $block = array(
                    $k[$req['format']][0]=>$rs['name'],
                    $k[$req['format']][1]=>$rs['admin2id']
                );

                $a[] = $block;
            }
        }
        return new JsonResponse(array('out'=>$a));

    }

    /**
     * @Route("/json/subset/admin3", name="cave_backend_json_admin3", methods={"GET","POST"})
     * @param Request $request
     * @param Admin3BackendRepository $admin3BackendRepository
     * @return JsonResponse
     */
    public function jsonadmin3Action(Request $request, Admin3BackendRepository $admin3BackendRepository): JsonResponse
    {
        foreach(array('countryid', 'string', 'admin1id', 'admin2id', 'format' , 'emptyOption', 'placeholder', 'page') as $val){
            switch ($val){
                case 'format': $req[$val] = $request->get($val) ?? 'suggest'; break;
                default: $req[$val] = $request->get($val);
            }
        }

        //key value return
        $k= array(
            'suggest' => array('label', 'value'),
            'select2' => array('text', 'id')
        );

        $em = $this->getDoctrine()->getManager();
        $alias = 'jsonadm3';
        $select= [$alias.'.admin3id', 'IDENTITY('.$alias.'.country) as country', $alias.'.name', 'IDENTITY('.$alias.'.admin1) as admin1',  'IDENTITY('.$alias.'.admin2) as admin2'];
        $admin3 = new Admin3();

        if(!empty($req['countryid'])){
            $admin3->setCountry($em->getReference(Country::class, $req['countryid']));
        }
        if(!empty($req['string'])){
            $admin3->setName($req['string']);
        }
        if(!empty($req['admin1id'])){
            $admin3->setAdmin1($em->getReference(Admin1::class, $req['admin1id']));
        }
        if(!empty($req['admin2id'])){
            $admin3->setAdmin2($em->getReference(Admin2::class, $req['admin2id']));
        }

        $data= $admin3BackendRepository->findByAdmin3($admin3, $alias, $select);

        $a = array();//output array

        //format result
        if(!empty($data)){


            if($req['emptyOption']){
                $block = array(
                    $k[$req['format']][0]=>'No result found',
                    $k[$req['format']][1]=>''
                );
                $a[] = $block;
            }

            foreach($data as $rs){
                $block = array(
                    $k[$req['format']][0]=>$rs['name'],
                    $k[$req['format']][1]=>$rs['admin3id']
                );

                $a[] = $block;
            }

        }
        return new JsonResponse(array('out'=>$a));
    }

    /**
     * @Route("/json/subset/area", name="cave_backend_json_area", methods={"GET","POST"})
     * @param Request $request
     * @param AreaBackendRepository $repository
     * @return JsonResponse
     */
    public function jsonareaAction(Request $request, AreaBackendRepository $repository): JsonResponse
    {
        foreach(array('countryid', 'admin1id', 'string', 'format' , 'emptyOption', 'placeholder', 'page') as $val){
            switch ($val){
                case 'format': $req[$val] = $request->get($val) ?? 'suggest'; break;
                default: $req[$val] = $request->get($val);
            }
        }

        //key value return
        $k= array(
            'suggest' => array('label', 'value'),
            'select2' => array('text', 'id')
        );

        $em = $this->getDoctrine()->getManager();
        $alias = 'jsonareacountry';
        $select= [$alias.'.areaid', 'IDENTITY('.$alias.'.country) as country', $alias.'.name', 'IDENTITY('.$alias.'.admin1) as admin1'];
        $area = new Area();

        if(!empty($req['countryid'])){
            $area->setCountry($em->getReference(Country::class, $req['countryid']));
        }
        if(!empty($req['string'])){
            $area->setName($req['string']);
        }
        if(!empty($req['admin1id'])){
            $area->setAdmin1($em->getReference(Admin1::class, $req['admin1id']));
        }

        $data= $repository->findByArea($area, $alias, $select);

        $a = array();//output array

        //format result
        if(!empty($data)){


            if($req['emptyOption']){
                $block = array(
                    $k[$req['format']][0]=>'No result found',
                    $k[$req['format']][1]=>''
                );
                $a[] = $block;
            }

            foreach($data as $rs){
                $block = array(
                    $k[$req['format']][0]=>$rs['name'],
                    $k[$req['format']][1]=>$rs['areaid']
                );

                $a[] = $block;
            }

        }
        return new JsonResponse(array('out'=>$a));
    }

    /**
     * Area
     *
     * @Route("/json/autocomplete/area",
     *     name="cave_backend_autocomplete_area",
     *     methods={"GET","POST"})
     * @param Request $request
     * @param AreaBackendRepository $repository
     * @return JsonResponse
     */
    public function areajsonAction(Request $request, AreaBackendRepository $repository): JsonResponse
    {
        $string     = $request->get('term');
        $areas  = $repository->filterByString($string);

        return new JsonResponse(
            [
                'out'=>(new Select2($areas))->getVsprintfArray('areaid', '%s' ,['name'])
            ]
        );
    }


    /**
     * Cave
     *
     * @Route("/json/autocomplete/cave",
     *     name="cave_backend_autocomplete_cave",
     *     methods={"GET","POST"})
     * @param Request $request
     * @param CaveBackendRepository $repository
     * @return JsonResponse
     */
    public function cavejsonAction(Request $request, CaveBackendRepository $repository): JsonResponse
    {
        $string     = $request->get('term');
        $caves      = $repository->filterByString($string);

        return new JsonResponse(
            [
                'out'=>(new Select2($caves))->getVsprintfArray('caveid', '%s' ,['name'])
            ]
        );
    }

    /**
     * Organisation
     *
     * @Route("/json/autocomplete/organisation",
     *     name="cave_backend_autocomplete_organisation",
     *     methods={"GET","POST"})
     * @param Request $request
     * @param OrganisationBackendRepository $repository
     * @return JsonResponse
     */
    public function organisationjsonAction(Request $request, OrganisationBackendRepository $repository) : JsonResponse
    {
        $string         = $request->get('term');
        $organisations  = $repository->filterByString($string);

        return new JsonResponse(
            [
                'out'=>(new Select2($organisations))->getVsprintfArray('organisationid','%s' ,['name'])
            ]
        );
    }


    /**
     * Fielddefinition
     *
     * @Route("/json/autocomplete/fielddefinition",
     *     name="cave_backend_autocomplete_fielddefinition",
     *     methods={"GET", "POST"})
     * @param Request $request
     * @param FielddefinitionBackendRepository $repository
     * @return JsonResponse
     */
    public function fielddefinitionjsonAction(Request $request, FielddefinitionBackendRepository $repository) : JsonResponse
    {
        $string     = $request->get('term');
        $fielddefinitions  = $repository->filterByString($string);

        return new JsonResponse(
            [
                'out'=>(new Select2($fielddefinitions))->getVsprintfArray('code', '%s(%s)  %s', ['code', 'entity', 'name'])
            ]
        );
    }

    /**
     * Sysparam
     *
     * @Route("/json/autocomplete/sysparam",
     *     name="cave_backend_autocomplete_sysparam",
     *     methods={"GET","POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function sysparamjsonAction(Request $request, SysparamBackendRepository $repository) : JsonResponse
    {
        $string     = $request->get('term');
        $people     = $repository->filterByString($string);

        return new JsonResponse(
            [
                'out'=>(new Select2($people))->getVsprintfArray('id', '%s' ,['name'])
            ]
        );
    }

    /**
     * Map
     *
     * @Route("/json/autocomplete/map",
     *     name="cave_backend_autocomplete_map",
     *     methods={"GET","POST"})
     * @param Request $request
     * @param MapBackendRepository $repository
     * @return JsonResponse
     */
    public function mapjsonAction(Request $request, MapBackendRepository $repository) : JsonResponse
    {
        $string     = $request->get('term');
        $mapseries  = $repository->filterByString($string);

        return new JsonResponse(
            [
                'out'=>(new Select2($mapseries))->getVsprintfArray('mapid', '%s' ,['name'])
            ]
        );
    }

    /**
     * Mapserie
     *
     * @Route("/json/autocomplete/mapserie",
     *     name="cave_backend_autocomplete_mapserie",
     *     methods={"GET","POST"})
     * @param Request $request
     * @param MapserieBackendRepository $repository
     * @return JsonResponse
     */
    public function mapseriejsonAction(Request $request, MapserieBackendRepository $repository) : JsonResponse
    {
        $string     = $request->get('term');
        $mapseries  = $repository->filterByString($string);

        return new JsonResponse(
            [
                'out'=>(new Select2($mapseries))->getVsprintfArray('mapserieid', '%s' ,['name'])
            ]
        );
    }


    /**
     * Article
     *
     * @Route("/json/autocomplete/article",
     *     name="cave_backend_autocomplete_article",
     *     methods={"GET","POST"})
     * @param Request $request
     * @param ArticleBackendRepository $repository
     * @return JsonResponse
     */
    public function articlejsonAction(Request $request, ArticleBackendRepository $repository) : JsonResponse
    {
        $string     = $request->get('term');
        $articles  = $repository->filterByString($string);

        return new JsonResponse(
            [
                'out'=>(new Select2($articles))->getVsprintfArray('articleid', '%s' ,['name'])
            ]
        );
    }


    /**
     * Specie
     *
     * @Route("/json/autocomplete/specie",
     *     name="cave_backend_autocomplete_specie",
     *     methods={"GET","POST"})
     * @param Request $request
     * @param SpecieBackendRepository $repository
     * @return JsonResponse
     */
    public function speciejsonAction(Request $request, SpecieBackendRepository $repository)
    {
        $string     = $request->get('term');
        $species  = $repository->filterByString($string);

        return new JsonResponse(
            [
                'out'=>(new Select2($species))->getVsprintfArray('specieid', '%s (%s)', ['commonname','name'])
            ]
        );
    }


    /**
     * Person
     *
     * @Route("/json/autocomplete/person",
     *     name="cave_backend_autocomplete_person",
     *     methods={"GET","POST"})
     * @param Request $request
     * @param PersonBackendRepository $repository
     * @return JsonResponse
     */
    public function personjsonAction(Request $request, PersonBackendRepository $repository): JsonResponse
    {
        $string     = $request->get('term');
        $people     = $repository->filterByString($string);

        return new JsonResponse(
            [
                'out'=>(new Select2($people))->getVsprintfArray('personid', '%s %s' ,['name', 'surname'])
            ]
        );
    }

    /**
     * Set _locale parameter in request for LocaleSubscriber
     *
     * @Route("/_locale/{_locale}",
     *     name="cave_backend_json_locale",
     *     methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function localejsonAction(Request $request)
    {
        $_locale = $request->get('_locale');
        $sessLocale= $request->getSession()->get('_locale');

        if($sessLocale == $_locale){
            return new JsonResponse(['locale'=> $sessLocale]);
        }else{
            return new JsonResponse([
                'error'=> sprintf('No se pudo modificar Locale. Session: "%s"  != Request: "%s"', $sessLocale, $_locale )
            ]);
        }
    }
}
