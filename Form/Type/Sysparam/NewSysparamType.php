<?php
namespace App\GptCavebackendBundle\Form\Type\Sysparam;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\GptCavebackendBundle\EventListener\Form\AddCountryFieldSubscriber;

class NewSysparamType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $defaults
     */
    public function buildForm(FormBuilderInterface $builder, array $defaults)
    {
        $factory = $builder->getFormFactory();

        $countrySubscriber = new AddCountryFieldSubscriber($factory, array(
            'options'=>array(
                'required' => true,
                'attr'=>array(
                    'code_id'=>156,
                    'class'=>'select2 countryid',//css
                    'style'=>'width:100%',//css
                )
            )
        ));
        $builder->addEventSubscriber($countrySubscriber);

        $builder->add('name', NULL, array('required' => true) );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\GptCaveBundle\Entity\Sysparam',
        ));
    }
}


