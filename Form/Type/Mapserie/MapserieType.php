<?php
namespace App\GptCavebackendBundle\Form\Type\Mapserie;
use App\GptCaveBundle\Entity\Mapserie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Doctrine\ORM\EntityRepository;
use App\GptCavebackendBundle\EventListener\Form\AddOrganisationFieldSubscriber;
use App\Cave\LibBundle\Format\Arraypath;

class MapserieType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $factory = $builder->getFormFactory();

        $organisationSubscriber = new AddOrganisationFieldSubscriber($factory, array(
            'name'=>'publisher',
            'getMethod'=>'getPublisher',
            'options'=>array(
                'attr'=>array(
                    'code_id'=>374,
                    'class'=>'select2 organisation',
                )
            )
        ));
        $builder->addEventSubscriber($organisationSubscriber);


            foreach(explode(';','lengthunits:280;maptype:559') as $el){
                $field = explode(':', $el);

                if($field[1]== '280'){
                    $field['code'] = 298;
                }elseif($field[1]== '559'){
                    $field['code'] = 367;
                }else{
                    $field['code'] = $field[1];
                }

            $builder->add(
                   $field[0], EntityType::class, array(
                        'class'=>'GptCaveBundle:Fieldvaluecode',
                        'attr'=>array('code_id'=>$field[1]),
                        'required' => false,
                        'choice_label' => 'value',
                        'choice_value'=>'id',
                        'query_builder' => function(EntityRepository $e) use ($field){
                                return $e->createQueryBuilder('f')
                                ->select('f')
                                ->where('f.field = :field')
                                ->orderBy('f.value', 'ASC')
                                ->setParameter('field', $field['code']);
                            },
                        )
                    );
        }


        foreach(explode(';', 'code:278;abbreviation:372;scale:373;comment:10374'
        ) as $el){

            $field = explode(':', $el);
            $arr = ['attr'=>['code_id'=> $field[1]]];

            $builder->add($field[0], NULL, $arr);
        }
        $builder->add('name', null, ['required' => true,'attr'=>['code_id'=> 279]]);
        $builder->add('comment', null, ['required' => false,'attr'=>['code_id'=> 10374]]);

}

/**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array('data_class' => 'App\GptCaveBundle\Entity\Mapserie'));
    }
}


