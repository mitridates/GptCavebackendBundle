<?php
namespace App\GptCavebackendBundle\Form\Type\Organisation;
use App\GptCavebackendBundle\EventListener\Form\AddAdmin1FieldSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\GptCavebackendBundle\EventListener\Form\AddCountryFieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddAdmin2FieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddAdmin3FieldSubscriber;


class OrganisationsearchType extends AbstractType
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
                    'code_id'=>376,
                    'class'=>'country',
                    'onChange'=>"$('.admin2, .admin3').html('')"
                )
            )
        ));
        $builder->addEventSubscriber($countrySubscriber);


        $admin1 = array(
            'options'=>array(
                'attr'=>array(
                    'code_id'=>377,
                    'class'=>'select2 admin1',//css
                )
            )
        );
        $admin1Subscriber = new AddAdmin1FieldSubscriber($factory,$countrySubscriber->getCountry(),$admin1);
        $builder->addEventSubscriber($admin1Subscriber);

        $admin2 = array('name'=>'admin2',
                        'options'=>array(
                                'attr'=>array(
                                    'class'=>'select2 admin2',//css
                                    )
                                )
                        );
        $admin2Subscriber = new AddAdmin2FieldSubscriber(
            $factory,
            $countrySubscriber->getCountry(),
            $admin1Subscriber->getAdmin1(),
            $admin2
        );
        $builder->addEventSubscriber($admin2Subscriber);

        $admin3= array('name'=>'admin3',
            'options'=>array(
                'attr'=>array(
                    'class'=>'select2 admin3',//css
                )
            )
        );
        $admin3Subscriber= new AddAdmin3FieldSubscriber(
            $factory,
            $admin2Subscriber->getAdmin2(),
            $admin3
        );
        $builder->addEventSubscriber($admin3Subscriber);

        foreach(explode(',','type:381,coverage:393,grouping:394') as $el){
            $field = explode(':', $el);
            $builder->add(
                   $field[0], EntityType::class, array(
                        'class'=>'GptCaveBundle:Fieldvaluecode',
                        'attr'=>array('code_id'=>$field[1]),
                        'required' => false,//show empty option
                        'choice_label' => 'value',
                        'choice_value'=>'id',
                        'query_builder' => function(EntityRepository $e) use ($field){
                                return $e->createQueryBuilder('f')
                                ->select('f')
                                ->where('f.field = :field')
                                ->orderBy('f.value', 'ASC')
                                ->setParameter('field', $field[1]);
                            },
                        )
                    );
        }

        foreach(explode(',', 'code:178,initials:390,name:391') as $el){
                            $field = explode(':', $el);
                            $builder->add($field[0], NULL, array(
                                'label'=>'cod'.$field[1],
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
            'data_class' => 'App\GptCaveBundle\Entity\Organisation',
            'parameters'=>array(),
        ));
    }
}
