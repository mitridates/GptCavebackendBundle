<?php
namespace App\GptCavebackendBundle\Form\Type\Fielddefinition;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
class FielddefinitionlangType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $defaults
     */
    public function buildForm(FormBuilderInterface $builder, array $defaults)
    {
        foreach(explode(',', 'name,abbreviation,definition,example,comment,uso,review') as $el){
            $builder->add($el);
        }
        $builder->add('save', SubmitType::class, [//enable submit on press enter
            'attr' => ['class' => 'hide'],
        ]);    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\GptCaveBundle\Entity\Fielddefinitionlang',
             'parameters'=>array()));
    }
}


