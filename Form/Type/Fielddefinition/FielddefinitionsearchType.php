<?php
namespace App\GptCavebackendBundle\Form\Type\Fielddefinition;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
class FielddefinitionsearchType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $defaults
     */
    public function buildForm(FormBuilderInterface $builder, array $defaults)
    {
        $choices = $defaults['choices'];

        $builder->add('datatype', ChoiceType::class, array(
            'placeholder' => 'Choose an option',
            'required'=>false,
            'choices' => array_flip($choices['datatype'])
        ));
        $builder->add('entity', ChoiceType::class, array(
            'placeholder' => 'Choose an option',
            'required'=>false,
            'choices' => array_flip($choices['entity'])
        ));        
        
        
        $builder->add('coding', ChoiceType::class, array(
            'placeholder' => 'Choose an option',
            'required'=>false,
            'choices' => array_flip($choices['coding'])

        ));
        $builder->add('singlemultivalued', ChoiceType::class, array(
            'placeholder' => 'Choose an option',
            'required'=>false,
            'choices' => array_flip($choices['singlemultivalued'])
        ));        
        foreach(explode(',', 'code,valuecode,name,maxlength') as $el){
            $builder->add($el, NULL, array('required'=>false));
        }
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\GptCaveBundle\Entity\Fielddefinition',
            'choices'=>[]
        ));

    }
}


