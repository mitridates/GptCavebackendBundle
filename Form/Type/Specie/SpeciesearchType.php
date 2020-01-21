<?php
namespace App\GptCavebackendBundle\Form\Type\Specie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SpeciesearchType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $defaults
     */
    public function buildForm(FormBuilderInterface $builder, array $defaults)
    {
        $bundle = $defaults['parameters'];//Arraypath parametros del bundle

        foreach(explode(',', 'name:282,commonname:606,genus:281,phylum:605,class:604,orden:603,family:602') as $el)
        {
            $field = explode(':', $el);

            $arr = [
                'required'=>false,
                'attr'=>['code_id'=>$field[1]]
                ];
            $builder->add($field[0], NULL, $arr);
        }
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\GptCaveBundle\Entity\Specie',
             'parameters'=>array()));
    }
}


