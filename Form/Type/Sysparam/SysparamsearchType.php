<?php
namespace App\GptCavebackendBundle\Form\Type\Sysparam;

use App\GptCavebackendBundle\EventListener\Form\AddMapserieFieldSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\GptCavebackendBundle\EventListener\Form\AddCountryFieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddOrganisationFieldSubscriber;


class SysparamsearchType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $defaults
     */
    public function buildForm(FormBuilderInterface $builder, array $defaults)
    {
        $factory = $builder->getFormFactory();

        //LOCATION
        $countrySubscriber = new AddCountryFieldSubscriber($factory, array(
            'options'=>array(
                'attr'=>array(
                    'class'=>'.countryid',
                    'code_id'=>156,

                )
            )
        ));
        $builder->addEventSubscriber($countrySubscriber);

        //DBManager organisation
        $organisationDbSubscriber = new AddOrganisationFieldSubscriber($factory, array(
            'name'=>'organisationdbm',
            'getMethod'=>'getOrganisationdbm',
            'options'=>array(
                'attr'=>array(
                    'code_id'=>182,
                    'class'=>'select2 organisation',
                )
            )
        ));
        $builder->addEventSubscriber($organisationDbSubscriber);

        $organisationSiteSubscriber = new AddOrganisationFieldSubscriber($factory, array(
            'name'=>'organisationsite',
            'getMethod'=>'getOrganisationsite',
            'options'=>array(
                'attr'=>array(
                    'code_id'=>157,
                    'class'=>'select2 organisation',
                )
            )
        ));
        $builder->addEventSubscriber($organisationSiteSubscriber);

        //Map series lookup
        $mapserieSubscriber = new AddMapserieFieldSubscriber($factory, array(
            'name'=>'mapserie',
            'getMethod'=>'getMapserie',
            'options'=>array(
                'attr'=>array(
                    'code_id'=>412,
                    'class'=>'select2 mapserieid',
                )
            )
        ));
        $builder->addEventSubscriber($mapserieSubscriber);

        //valuecodes
            $builder->add(
                   'refunits', EntityType::class, array(
                                'class'=>'GptCaveBundle:Fieldvaluecode',
                                'attr'=>array('code_id'=>296, 'class'=>'select2', 'style'=>'width:100%'),
                                'required' => false,//show empty option
                                'choice_label' => 'value',
                                'choice_value'=>'id',
                                'query_builder' => function(EntityRepository $e){
                                        return $e->createQueryBuilder('f')
                                        ->select('f')
                                        ->where('f.field = :field')
                                        ->orderBy('f.value', 'ASC')
                                        ->setParameter('field', 298);
                                    },
                                )
                    );         
            $builder->add(
                   'altitudeunit', EntityType::class, array(
                        'class'=>'GptCaveBundle:Fieldvaluecode',
                        'attr'=>array('code_id'=>446, 'class'=>'select2', 'style'=>'width:100%'),
                        'required' => false,//show empty option
                        'choice_label' => 'value',
                        'choice_value'=>'id',
                        'query_builder' => function(EntityRepository $e){
                                return $e->createQueryBuilder('f')
                                ->select('f')
                                ->where('f.field = :field')
                                ->orderBy('f.value', 'ASC')
                                ->setParameter('field', 298);
                            },
                        )
                    );    

        $builder->add('language', ChoiceType::class, [
           'choices'=>[]
        ]);
         
        $builder->add('name', null, ['required'=>false]);
       
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
