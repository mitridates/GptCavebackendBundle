<?php
namespace App\GptCavebackendBundle\Util\ControllerParameters;

class CommonParams extends BackendParams
{

    /**
     * @return array
     */
    public function indexParams(): array
    {
        return $this->add('name', 'index')
            ->add('title', $this->translator->trans($this->name.'.index.page.title',[], 'cavepages'))
            ->add('breadcrumb', ['home', 'index'])
            ->getParametersbag()
            ;
    }

    /**
     * @param string $id
     * @param string $description
     * @return array
     */
    public  function editParams($id, $description): array
    {
        return $this->add('name', 'edit')
            ->add('title', $this->translator->trans($this->name.'.edit.page.title',['%name%'=> $description, '%id%'=> $id], 'cavepages'))
            ->addCrumb('edit', [
                'text'=>$this->translator->trans($this->name.'.menu.edit',[], 'cavepages'),
                'title'=> sprintf('%s. ID %s', $description, $id),
                'path'=>['cave_backend_'.$this->name.'_edit',['id'=>$id]]
            ])
            ->add('breadcrumb', ['home', 'index', 'edit'])
            ->getParametersbag();
    }

    /**
     * @return array
     */
    public function newParams(): array
    {
        return $this->add('name', 'new')
            ->add('title', $this->translator->trans($this->name.'.new.page.title',[], 'cavepages'))
            ->add('breadcrumb', ['home', 'new'])
            ->getParametersbag();
    }
    
}