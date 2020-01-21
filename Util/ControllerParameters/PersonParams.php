<?php
namespace App\GptCavebackendBundle\Util\ControllerParameters;
use App\Cave\CaveBundle\Entity\Person;
use App\Cave\LibBundle\Format\Arraypath;

class PersonParams extends BackendParams
{
    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'person';
    }

    /**
     * @inheritDoc
     */
    protected function init(): void
    {
        $this->parametersbag['page'] = array_merge(
            $this->bundleparams['section']['default'], $this->bundleparams['section']['person'],
            ['section'=>'person']
        );

        $this->breadcrumb->addCrumb('index', [
            'text'=> ['person.index.page.title',[], 'cavepages'],
            'route'=>'cave_backend_person_index'
        ])
        ->addCrumb('new', [
            'text'=> ['person.menu.new',[], 'cavepages'],
            'route'=>'cave_backend_person_new'
        ]);
    }

    /**
     * @return Arraypath
     */
    public function indexParams(){

        return $this->params->set('page:name', 'index')
            ->set('page:title', $this->translator->trans('person.index.page.title',[], 'cavepages'))
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
     * @param Person $person
     * @return Arraypath
     */
    public  function editParams(Person $person){

        $this->params->set('page:router:edit',
            $this->getCrumb([
                'text'=> $this->translator->trans('person.menu.edit',['id'=>$person->getPersonid()], 'cavepages'),
                'route'=>['cave_backend_person_edit',['id'=>$person->getPersonid()]]
            ])
        );
        return $this->params->set('page:name', 'edit')
            ->set('page:title', $this->translator->trans('person.edit.page.title',['%name%'=> $person->getName(), '%id%'=> $person->getPersonid()], 'cavepages'))
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
            ->set('page:title', $this->translator->trans('person.new.page.title',[],'cavepages'))
            ->set('page:breadcrumb', [
                    $this->params->get('router:home'),
                    $this->params->get('page:router:new')
                ]);

    }


}