<?php
namespace App\GptCavebackendBundle\Form\Type\Area;
use App\{GptCavebackendBundle\EventListener\Form\AddAdmin1FieldSubscriber,
    GptCavebackendBundle\EventListener\Form\AddCountryFieldSubscriber,
    GptCavebackendBundle\EventListener\Form\AddMapFieldSubscriber};
use Symfony\Component\{Form\AbstractType,
    Form\FormBuilderInterface,
    OptionsResolver\OptionsResolver};

class AreasearchType extends AbstractType
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
                'attr'=>array(
                    'code_id'=>224
                )
            )
        ));
        $builder->addEventSubscriber($countrySubscriber);

        $admin1Subscriber = new AddAdmin1FieldSubscriber($factory,$countrySubscriber->getCountry(), array(
            'options'=>array(
                'attr'=>array(
                    'code_id'=>225
                )
            )
        ));
        $builder->addEventSubscriber($admin1Subscriber);

//        $builder->addEventSubscriber(new AddMapFieldSubscriber($factory));

        foreach(explode(',', 'name:80,code:81,mapsheet:618') as $el){
                $field = explode(':', $el);
                $builder->add($field[0], NULL, array(
                    'required'=>false,
                     'attr'=>['code_id'=>$field[1]]
                        ));
        }

    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\GptCaveBundle\Entity\Area',
        ));
    }
}
