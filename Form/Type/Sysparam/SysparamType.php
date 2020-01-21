<?php
namespace App\GptCavebackendBundle\Form\Type\Sysparam;
use App\GptCavebackendBundle\EventListener\Form\AddMapserieFieldSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\GptCavebackendBundle\EventListener\Form\AddCountryFieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddOrganisationFieldSubscriber;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;

class SysparamType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $defaults
     */
    public function buildForm(FormBuilderInterface $builder, array $defaults)
    {
        /**
         * @var array
         */
        $arrayParams = $defaults['parameters'];
        $factory = $builder->getFormFactory();

        $countrySubscriber = new AddCountryFieldSubscriber($factory, array(
            'getMethod'=>'getCountry',
            'options'=>array(
                'attr'=>array(
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

        $languages = ['en'=>\Locale::getDisplayLanguage('en')];
        foreach ($arrayParams['bundle']['languages'] as $lang){
            $languages[$lang] = ucfirst(\Locale::getDisplayLanguage($lang, $lang));
        }

        $builder->add('language', choiceType::class, [
            'choices'=> array_flip($languages),
            'attr'=>array('code_id'=>158),
        ]);

                                        
        foreach(explode(',', 'geogeodetdatum:288,mapgrid:289,heightdatum:516,'.
                            'grrefqualifier:413,'.
                            'geogprecision:294,grefprecision:295,altitudeprecision:526,'.
                            'landunit:440,'.
                            'transcodes:291,'.
                            'softwarelevel:529,version:539,mapdir:596,topodir:13079'
        ) as $el){

            $field = explode(':', $el);
            $arr = ['attr'=>['code_id'=>$field[1]]];
            $builder->add($field[0], NULL, $arr);
        }
        $builder->add('name');

        $builder->add('submit', SubmitType::class, [
            'label' => 'save',
            'translation_domain' => 'cavemessages',
            'attr' => ['class' => 'btn btn-primary'],
        ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\GptCaveBundle\Entity\Sysparam',
            'parameters'=>array(),
        ));
    }
}


