<?php
namespace App\GptCavebackendBundle\Util\ControllerParameters;
use App\GptCaveBundle\Entity\Sysparam;

class SysparamParams extends BackendParams
{
    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'sysparam';
    }

    /**
     * @param Sysparam|null $sysparam
     * @return array
     */
    public  function editParams(Sysparam $sysparam = null): array
    {
        return $this->add('name', 'edit')
            ->add('title', $this->translator->trans($this->getName().'.edit.page.title',['%name%'=>$sysparam? $sysparam->getName() : 'undefined' , '%id%'=> $sysparam ? $sysparam->getId() : 'undefined'], 'cavepages'))
            ->addCrumb('edit', [
                'text'=>$this->translator->trans($this->getName().'.menu.edit',[], 'cavepages'),
                'title'=> sprintf('%s. ID %s', $sysparam? $sysparam->getName() : 'undefined',  $sysparam ? $sysparam->getId() : 'undefined'),
                'path'=>['cave_backend_'.$this->getName().'_edit']
            ])
            ->add('breadcrumb', ['index', 'edit'])
            ->getParametersbag();

    }


}