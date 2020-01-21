<?php
namespace App\GptCavebackendBundle\Util\ControllerParameters;
use App\Cave\CaveBundle\Entity\Map;
use App\Cave\LibBundle\Format\Arraypath;

class MapParams extends BackendParams
{
    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'map';
    }

    /**
     * @inheritDoc
     */
    protected function init(): void
    {

        $this->params
            ->set('page', array_merge(
                        $this->params->get('cave_backend:section:default'),
                        $this->params->get('cave_backend:section:map', [])
                ))
            ->set('page:section', 'map')
            ->set('page:onetomany', [
                'comment',
                'citation',/*'citation==publication*/
                'furtherpc',
                'furthergc',
                'surveyor',
                'drafter',
                'cave'
                /*'specialsheet'???*/
            ])
            ->set('page:onetoone', [
                'details',
                'controller',
                'updater'
            ])
            ->set('page:router', [
                'index'=> $this->getCrumb([
                    'text'=> $this->translator->trans('map.menu.index',[], 'cavepages'),
                    'route'=>'cave_backend_map_index'
                    ]
                ),
                'new'=> $this->getCrumb([
                        'text'=> $this->translator->trans('map.menu.new',[], 'cavepages'),
                        'route'=>'cave_backend_map_new'
                        ]
                )
            ])
        ;
    }

    /**
     * @return Arraypath
     */
    public function indexParams(){
        return $this->params->set('page:name', 'index')
            ->set('page:title', $this->translator->trans('map.index.page.title',[], 'cavepages'))
            ->set('page:breadcrumb', [
                $this->params->get('router:home'),
                $this->params->get('page:router:index'),
            ] );
    }
    /**
     * @return Arraypath
     */
    public  function ajaxpagerParams(){
        return $this->params
            ->set('page:name', 'index_ajax');
    }

    /**
     * @param Map $map
     * @return Arraypath
     */
    public  function editParams(Map $map){

        $this->params->set('page:router:edit',
            $this->getCrumb([
                'text'=> $this->translator->trans('map.menu.edit',['id'=>$map->getMapid()], 'cavepages'),
                'route'=>['cave_backend_map_edit',['id'=>$map->getMapid()]]
            ])
        );
        return $this->params->set('page:name', 'edit')
            ->set('page:title', $this->translator->trans('map.edit.page.title',['%name%'=> $map->getName(), '%id%'=> $map->getMapid()], 'cavepages'))
            ->set('page:breadcrumb', [
                $this->params->get('router:home'),
                $this->params->get('page:router:index'),
                $this->params->get('page:router:edit'),
            ] );
    }
    /**
     * @return Arraypath
     */
    public  function newParams(){
        return $this->params
            ->set('page:name', 'new')
            ->set('page:title', $this->translator->trans('map.new.page.title',[],'cavepages'))
            ->set('page:breadcrumb', [
                    $this->params->get('router:home'),
                    $this->params->get('page:router:new')
                ]);

    }


}