<?php
namespace App\GptCavebackendBundle\Form\Type\Person;
use App\GptCavebackendBundle\EventListener\Form\AddAdmin1FieldSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\GptCavebackendBundle\EventListener\Form\AddAdmin3FieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddCountryFieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddAdmin2FieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddOrganisationFieldSubscriber;

class PersonType extends AbstractType
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

        $organisationSubscriber = new AddOrganisationFieldSubscriber(
            $factory, array(
            'name'=>'organisation2',
            'getMethod'=>'getOrganisation2',
            'options'=>array(
                'attr'=>array(
                    'code_id'=>502,
                    'class'=>'select2 organisation'
                )
            )
        ));
        $builder->addEventSubscriber($organisationSubscriber);

        $organisationSubscriber = new AddOrganisationFieldSubscriber(
            $factory, array(
            'name'=>'organisation3',
            'getMethod'=>'getOrganisation3',
            'options'=>array(
                'attr'=>array(
                    'code_id'=>503,
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

        $builder->add('email', EmailType::class, [
            'required'=>false,
            'attr'=>[
                'code_id'=>494,
                'class'=>'js-email',//css
                ]
        ])->add('gender', ChoiceType::class, array(
            'required'=>false,
            'choice_translation_domain' => 'cavemessages',
            'choices' =>[
                'select.gender.male' => 'M',
                'select.gender.female' => 'F'
            ]
        ));



        foreach(explode(',', 'surname:479,name:480,title:483,middleinitial:481,initialforgivennames:482'
                            . ',addressline1:484,addressline2:485,addressline3:486,addressline4:487,cityorsuburb:488'
                            . ',postcode:491,phoneprefix:495,homephonenumber:496,workphonenumber:497'
                            . ',mobilephonenumber:498,faxphonenumber:499,pagerphonenumber:500'

                            ) as $el){

                                $field = explode(':', $el);

                                $arr = ['attr'=>['code_id'=>$field[1]]];
                                if($field[1]=='478'){
                                    $arr['read_only']=true;
                                }
                                if(in_array($field[1],['501', '502', '503'])){
                                    $arr['attr']['class']='organisation select2';
                                }
                                $builder->add($field[0], NULL, $arr);
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


