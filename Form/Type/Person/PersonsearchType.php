<?php
namespace App\GptCavebackendBundle\Form\Type\Person;
use App\GptCavebackendBundle\EventListener\Form\AddAdmin1FieldSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\GptCavebackendBundle\EventListener\Form\AddCountryFieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddAdmin2FieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddAdmin3FieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddOrganisationFieldSubscriber;

class PersonsearchType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $defaults)
    {
        $factory = $builder->getFormFactory();

        $organisationSubscriber = new AddOrganisationFieldSubscriber($factory, array(
            'name'=>'organisation1',
            'getMethod'=>'getOrganisation1',
            'options'=>array(
                'attr'=>array(
                    'code_id'=>501,
                    'class'=>'select2 organisation'
                )
            )
        ));

        $builder->addEventSubscriber($organisationSubscriber);

        $countrySubscriber = new AddCountryFieldSubscriber($factory, array(
            'options'=>array(
                'attr'=>array(
                    'code_id'=>493
                )
            )
        ));
        $builder->addEventSubscriber($countrySubscriber);

        $admin1Subscriber = new AddAdmin1FieldSubscriber(
            $factory, $countrySubscriber->getCountry(), [
                'options'=>array(
                    'attr'=>array('code_id'=>490)
                )]
        );
        $builder->addEventSubscriber($admin1Subscriber);


        $admin2 = array('name'=>'admin2',
            'options'=>array(
                'attr'=>array()
            )
        );
        $admin2Subscriber = new AddAdmin2FieldSubscriber(
            $factory,
            $countrySubscriber->getCountry(),
            $admin1Subscriber->getAdmin1(),
            $admin2
        );
        $builder->addEventSubscriber($admin2Subscriber);

        $admin3Subscriber= new AddAdmin3FieldSubscriber($factory, $admin2Subscriber->getAdmin2(),array(
            'name'=>'admin3',
            'options'=>array(
                'attr'=>array()
            )
        ));
        $builder->addEventSubscriber($admin3Subscriber);


        foreach(explode(',', 'surname:479,name:480') as $el){
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
            'data_class' => 'App\GptCaveBundle\Entity\Person',
        ));
    }
}
