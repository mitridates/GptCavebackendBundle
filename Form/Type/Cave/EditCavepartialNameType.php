<?php
namespace App\GptCavebackendBundle\Form\Type\Cave;
use App\GptCavebackendBundle\EventListener\Form\AddAreaFieldSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\GptCavebackendBundle\EventListener\Form\AddCountryFieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddAdmin1FieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddAdmin2FieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddAdmin3FieldSubscriber;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use Symfony\Contracts\Translation\TranslatorInterface;

class EditCavepartialNameType extends AbstractType
{

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $defaults)
    {
        $factory = $builder->getFormFactory();
        $this->translator = $defaults['translator'];


        $fields = '70:name;512:updatestatus;9:quantityofentrances;27:nearestlocality;'
            . '54:percentmapped;77:serial;'
            . '10380:hide;13075:geographiclocation;13076:system;10380:hide;229:localgovernmentarea';


        foreach(explode(';', $fields) as $el){
            if(!strpos($el,':')) {var_dump ($el);exit;}
            $field = explode(':', $el);
            $arr = ['attr'=>['code_id'=>$field[0]]];
            if($field[1]=='name'){ $arr['required']=true; }
            $builder->add($field[1], NULL, $arr);
        }

        foreach(explode(';','1:featuretype;10:entrancetype;20:penetrability;29:degreeexplored;78:entrancemarking;512:updatestatus') as $el){
            $field = explode(':', $el);
            $builder->add(
                $field[1], EntityType::class, array(
                    'class'=>'GptCaveBundle:Fieldvaluecode',
                    'attr'=>array('code_id'=>$field[0]),
                    'required' => false,
                    'choice_label' => 'value',
                    'choice_value'=>'id',
                    'query_builder' => function(EntityRepository $e) use ($field){
                        return $e->createQueryBuilder('f')
                            ->select('f')
                            ->where('f.field = :field')
                            ->setParameter('field', $field[0]);
                    },
                )
            );
        }


        $countrySubscriber = new AddCountryFieldSubscriber($factory, array(
            'options'=>array(
                'attr'=>array(
                    'code_id'=>220
                )
            )
        ));
        $builder->addEventSubscriber($countrySubscriber);

        $areaSubscriber = new AddAreaFieldSubscriber($factory, $countrySubscriber->getCountry(),  null, array(
            'options'=>array(
                'attr'=>array(
                    'code_id'=>303
                )
            )
        ));
        $builder->addEventSubscriber($areaSubscriber);

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

    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\GptCaveBundle\Entity\Cave',
            'translator'=>TranslatorInterface::class
        ));
    }
}