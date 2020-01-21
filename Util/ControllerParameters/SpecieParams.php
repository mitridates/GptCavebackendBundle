<?php
namespace App\GptCavebackendBundle\Util\ControllerParameters;
use App\Cave\CaveBundle\Entity\Specie;
use App\Cave\LibBundle\Format\Arraypath;

class SpecieParams extends BackendParams
{
    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'specie';
    }

    /**
     * @inheritDoc
     */
    protected function init(): void
    {

        $this->params
            ->set('page', array_merge(
                        $this->params->get('cave_backend:section:default'),
                        $this->params->get('cave_backend:section:specie', [])
                ))
            ->set('page:section', 'specie')
            ->set('page:router', [
                'index'=> $this->getCrumb([
                    'text'=> $this->translator->trans('specie.menu.index',[], 'cavepages'),
                    'route'=>'cave_backend_specie_index'
                    ]
                ),
                'new'=> $this->getCrumb([
                        'text'=> $this->translator->trans('specie.menu.new',[], 'cavepages'),
                        'route'=>'cave_backend_specie_new'
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
            ->set('page:title', $this->translator->trans('specie.index.page.title',[], 'cavepages'))
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
     * @param Specie $specie
     * @return Arraypath
     */
    public  function editParams(Specie $specie){

        $this->params->set('page:router:edit',
            $this->getCrumb([
                'text'=> $this->translator->trans('specie.menu.edit',['id'=>$specie->getSpecieid()], 'cavepages'),
                'route'=>['cave_backend_specie_edit',['id'=>$specie->getSpecieid()]]
            ])
        );
        return $this->params->set('page:name', 'edit')
            ->set('page:title', $this->translator->trans('specie.edit.page.title',['%name%'=> $specie->getName(), '%id%'=> $specie->getSpecieid()], 'cavepages'))
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
            ->set('page:title', $this->translator->trans('specie.new.page.title',[],'cavepages'))
            ->set('page:breadcrumb', [
                    $this->params->get('router:home'),
                    $this->params->get('page:router:new')
                ]);

    }


}