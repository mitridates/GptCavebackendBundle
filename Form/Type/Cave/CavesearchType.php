<?php
namespace App\GptCavebackendBundle\Form\Type\Cave;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\GptCavebackendBundle\EventListener\Form\AddCountryFieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddAdmin1FieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddAdmin2FieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddAdmin3FieldSubscriber;
use Symfony\Contracts\Translation\TranslatorInterface;

class CavesearchType extends AbstractType
{

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $defaults)
    {
        $factory = $builder->getFormFactory();

        $countrySubscriber = new AddCountryFieldSubscriber($factory, array(
            'options'=>array(
                'attr'=>array(
                    'code_id'=>220
                )
            )
        ));
        $builder->addEventSubscriber($countrySubscriber);

        $admin1Subscriber = new AddAdmin1FieldSubscriber($factory,$countrySubscriber->getCountry(), array(
            'options'=>array(
                'attr'=>array(
                    'code_id'=>18,
                )
            )
        ));
        $builder->addEventSubscriber($admin1Subscriber);

        $admin2Subscriber = new AddAdmin2FieldSubscriber($factory, $countrySubscriber->getCountry(), $admin1Subscriber->getAdmin1(), array(
                'name'=>'admin2',
                'options'=>array(
                    'attr'=>array()
                )
            )
        );
        $builder->addEventSubscriber($admin2Subscriber);

        $admin3Subscriber= new AddAdmin3FieldSubscriber($factory, $admin2Subscriber->getAdmin2(),array(
            'name'=>'admin3',
            'options'=>array(
                'attr'=>array()
            )
        ));
        $builder->addEventSubscriber($admin3Subscriber);

        foreach(explode(',', 'name:70') as $el){
                            $field = explode(':', $el);
                            $builder->add($field[0], NULL, array(
                                'required'=>false,
                                 'attr'=>['code_id'=>$field[1]]
                                    ));
        }
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array('data_class' => 'App\GptCaveBundle\Entity\Cave'));
    }
}
