<?php
namespace App\GptCavebackendBundle\Form\Type\Map;
use App\GptCavebackendBundle\EventListener\Form\AddMapserieFieldSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\GptCavebackendBundle\EventListener\Form\AddCountryFieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddAdmin1FieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddAdmin2FieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddAdmin3FieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddOrganisationFieldSubscriber;

class MapsearchType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $defaults)
    {
        $factory = $builder->getFormFactory();

        $mapserieSubscriber = new AddMapserieFieldSubscriber($factory, array(
            'options'=>array(
                'attr'=>array(
                    'code_id'=>366
                )
            )
        ));
        $builder->addEventSubscriber($mapserieSubscriber);


       //LOCATION map scope
        $countrySubscriber = new AddCountryFieldSubscriber($factory, array(
            'name'=>'scopecountry',
            'getMethod'=>'getScopecountry',
            'options'=>array(
                'attr'=>array(
                    'code_id'=>196
                )
            )
        ));
        $builder->addEventSubscriber($countrySubscriber);

        $admin1Subscriber = new AddAdmin1FieldSubscriber($factory,$countrySubscriber->getCountry(), array(
            'name'=>'scopeadmin1',
            'getMethod'=>'getScopeadmin1',
            'options'=>array(
                'attr'=>array(
                    'code_id'=>197,
                )
            )
        ));
        $builder->addEventSubscriber($admin1Subscriber);

        $admin2Subscriber = new AddAdmin2FieldSubscriber($factory, $countrySubscriber->getCountry(), $admin1Subscriber->getAdmin1(), array(
                'name'=>'scopeadmin2',
                'getMethod'=>'getScopeadmin2',
                'options'=>array(
                    'attr'=>array()
                )
            )
        );
        $builder->addEventSubscriber($admin2Subscriber);

        $admin3Subscriber= new AddAdmin3FieldSubscriber($factory, $admin2Subscriber->getAdmin2(),array(
            'name'=>'scopeadmin3',
            'getMethod'=>'getScopeadmin3',
            'options'=>array(
                'attr'=>array()
            )
        ));
        $builder->addEventSubscriber($admin3Subscriber);


        //LOCATION source

        $countrySubscriber = new AddCountryFieldSubscriber($factory, array(
            'name'=>'sourcecountry',
            'getMethod'=>'getSourcecountry',
            'options'=>array(
                'attr'=>array(
                    'code_id'=>370
                )
            )
        ));
        $builder->addEventSubscriber($countrySubscriber);

        $organisationSubscriber = new AddOrganisationFieldSubscriber($factory, array(
            'name'=>'sourceorg',
            'getMethod'=>'getSourceorg',
            'options'=>array(
                'attr'=>array(
                    'code_id'=>200,
                    'class'=>'select2 organisation',
                )
            )
        ));
        $builder->addEventSubscriber($organisationSubscriber);

        foreach(explode(',','367:type,396:sourcetype') as $el){
            $field = explode(':', $el);
            if($field[0]== '396'){
                $field[0] = 623;
            }
            $builder->add(
                $field[1], EntityType::class, array(
                    'class'=>'GptCaveBundle:Fieldvaluecode',
                    'attr'=>array('code_id'=>$field[0], 'class'=>'select2', 'style'=>'width:100%'),
                    'required' => false,//show empty option
                    'choice_label' => 'value',
                    'choice_value'=>'id',
                    'query_builder' => function(EntityRepository $e) use ($field){
                        return $e->createQueryBuilder('f')
                            ->select('f')
                            ->where('f.field = :field')
                            ->orderBy('f.value', 'ASC')
                            ->setParameter('field', $field[0]);
                    },
                )
            );
        }
        

        $fields = '202:name';

        foreach(explode(',', $fields) as $el){
                          $field = explode(':', $el);
                          $builder->add($field[1], NULL, array(
                              'required'=>false,
                               'attr'=>['code_id'=>$field[0]]
                                  ));
        }

        //$builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event){
            /**
             * @var Map $entity
             */
          //  $entity = $event->getData();
          //  $form = $event->getForm();
        //});
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array('data_class' => 'App\GptCaveBundle\Entity\Map'));
    }
}
