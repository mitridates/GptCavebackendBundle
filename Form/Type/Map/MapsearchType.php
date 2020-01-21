<?php
namespace App\GptCavebackendBundle\Form\Type\Map;
use Symfony\Component\Form\AbstractType;
use App\Cave\LibBundle\Format\Arraypath;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\GptCavebackendBundle\EventListener\Form\AddCountryFieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddAdmin1FieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddAdmin2FieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddAdmin3FieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddOrganisationFieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddDynamicMapserieFieldSubscriber;

class MapsearchType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $defaults
     */
    public function buildForm(FormBuilderInterface $builder, array $defaults)
    {
        $bundle = $defaults['parameters'];//Arraypath parametros del bundle
        $factory = $builder->getFormFactory();

        $mapserie = array(//parametros del pais
                        'name'=>'mapserie',//propiedad
                        'getMethod'=>'getMapserie',//metodo
                        'options'=>array(//opciones del campo html
                                'placeholder'   => 'select.mapserie',//para la traduccion del texto por defecto
                                'attr'=>array(//attributos del html
                                    'code_id'=>366,
                                    'class'=>'select2 mapserie',//css
                                    'style'=>'width:100%',//css
                                    )
                                )
                        );

        $mapserieSubscriber = new AddMapserieFieldSubscriber(
                                                $factory ,
                                                array('parameters'=>
                                                new Arraypath([
                                                        'mapserie'=>$mapserie,
                                                        'bundle'=> $bundle->toArray()
                                                        ])
                                                ));
        $builder->addEventSubscriber($mapserieSubscriber);
        

       //LOCATION map scope
        $scopecountry = array(//parametros del pais
                        'name'=>'scopecountry',//propiedad
                        'getMethod'=>'getScopecountry',//metodo
                        'options'=>array(//opciones del campo html
                            'placeholder'   => 'select.government.level.country',//para la traduccion del texto por defecto
                            'attr'=>array(//attributos del html
                                'class'=>'select2 countryid',//css
                                'style'=>'width:100%',//css
                                )
                            )
                        );

        $countrySubscriber = new AddCountryFieldSubscriber(
                                                $factory ,
                                                array('parameters'=>
                                                new Arraypath([
                                                        'country'=>$scopecountry,
                                                        'bundle'=> $bundle->toArray()
                                                        ])
                                                ));
        $builder->addEventSubscriber($countrySubscriber);

        $scopestate = array(//parametros de la provincia
                        'name'=>'scopeadmin1',//propiedad
                        'getMethod'=>'getScopeadmin1',//metodo
                        'options'=>array(//opciones del campo html
                                'attr'=>array(
                                    'class'=>'select2 admin1id',//css
                                    'style'=>'width:100%'//css
                                    )
                                )
                        );

        $stateSubscriber = new AddAdmin1FieldSubscriber(
                                        $factory,array('parameters'=>
                                                new Arraypath([
                                                        'country'=>$scopecountry,
                                                        'state'=>$scopestate,
                                                        'bundle'=> $bundle->toArray()
                                                        ])
                                                ));
        $builder->addEventSubscriber($stateSubscriber);        
        
        
        $scopeprovince = array(//parametros de la provincia
                        'name'=>'scopeadmin2',//propiedad
                        'getMethod'=>'getScopeadmin2',//metodo
                        'options'=>array(//opciones del campo html
                                'attr'=>array(
                                    'class'=>'select2 admin2id',//css
                                    'style'=>'width:100%'//css
                                    )
                                )
                        );

        $provinceSubscriber = new AddAdmin2FieldSubscriber(
                                        $factory,array('parameters'=>
                                                new Arraypath([
                                                        'country'=>$scopecountry,
                                                        'state'=>$scopestate,
                                                        'province'=>$scopeprovince,
                                                        'bundle'=> $bundle->toArray()
                                                        ])
                                                ));
        $builder->addEventSubscriber($provinceSubscriber);

        $scopemunicipality = array(//parametros del municipio
                            'name'=>'scopeadmin3',//nombre del campo
                            'getMethod'=>'getScopeadmin3',//método get
                            'options'=>array(//opciones del campo html
                                    'attr'=>array(//attributos del html
                                          'class'=>'select2 admin3id',//css
                                          'style'=>'width:100%'//css
                                        )
                                    )
                            );

        $municipalitySubscriber = new AddAdmin3FieldSubscriber(
                                        $factory,array('parameters'=>
                                                new Arraypath([
                                                        'country'=>$scopecountry,
                                                        'state'=>$scopestate,
                                                        'province'=>$scopeprovince,
                                                        'municipality'=>$scopemunicipality,
                                                        'bundle'=> $bundle->toArray()
                                                        ])
                                                ));
        $builder->addEventSubscriber($municipalitySubscriber);

        //LOCATION source
        $sourcecountry = array(//parametros del pais
            'name'=>'sourcecountry',//propiedad
            'getMethod'=>'getSourcecountry',//metodo
            'options'=>array(//opciones del campo html
                'placeholder'   => 'select.government.level.country',//para la traduccion del texto por defecto
                'attr'=>array(//attributos del html
                    'code_id'=>370,
                    'class'=>'select2 countrysource',//css
                    'style'=>'width:100%',//css
                )
            )
        );

        $sourcecountrySubscriber = new AddCountryFieldSubscriber(
            $factory ,
            array('parameters'=>
                new Arraypath([
                    'country'=>$sourcecountry,
                    'bundle'=> $bundle->toArray()
                ])
            ));
        $builder->addEventSubscriber($sourcecountrySubscriber);

        $sourceorg = new Arraypath(array(//parametros
                        'name'=>'sourceorg',//nombre del campo
                        'getMethod'=>'getSourceorg',//metodo
                        'options'=>array(//opciones del campo html
                                'placeholder'   => 'select.organisation',//para la traduccion del texto por defecto
                                'attr'=>array(//attributos del html
                                    'code_id'=>200,
                                    'class'=>'select2 organisation',//css
                                    'style'=>'width:100%',//css
                                    )
                                )
                        )
                );
        $parameters = new Arraypath([
                            'organisation'=>$sourceorg->toArray(),
                            'bundle'=> $bundle->toArray()
                        ]);

        $sourceorgcodeSubscriber = new AddOrganisationFieldSubscriber(
                                        $factory,array('parameters'=>$parameters));

         $builder->addEventSubscriber($sourceorgcodeSubscriber);


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
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\GptCaveBundle\Entity\Map',
            'parameters'=>array(),
            ));
    }
}
