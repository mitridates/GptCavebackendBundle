<?php
namespace App\GptCavebackendBundle\Form\Type\Map;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\GptCavebackendBundle\EventListener\Form\AddCountryFieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddAdmin1FieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddAdmin2FieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddAdmin3FieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddOrganisationFieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddDynamicMapserieFieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddDynamicPersonFieldSubscriber;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Cave\LibBundle\Format\Arraypath;
use Doctrine\ORM\EntityRepository;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class EditMapType extends AbstractType
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @param FormBuilderInterface $builder
     * @param array $defaults
     */
    public function buildForm(FormBuilderInterface $builder, array $defaults)
    {
        /**
         * @var Arraypath
         */
        $bundle = $defaults['parameters'];//Arraypath parametros del bundle
        $this->translator = $defaults['translator'];
        $factory = $builder->getFormFactory();

        /**
         * FIXME: La cobertura política adicional permite incluir nuevas áreas añadir manytoone area 'code_id'=>196.
         */


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

        /*FUENTE DEL MAPA*/
        $sourcecountry = array(//parametros del pais
                        'name'=>'sourcecountry',//propiedad
                        'getMethod'=>'getSourcecountry',//metodo
                        'options'=>array(//opciones del campo html
                                'placeholder'   => 'government.level.country',//para la traduccion del texto por defecto
                                'attr'=>array(//attributos del html
                                    'code_id'=>370,
                                    'class'=>'select2 countryid',//css
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

        $sourceorgcode = new Arraypath(array(//parametros
                        'name'=>'sourceorg',//propiedad
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
                            'organisation'=>$sourceorgcode->toArray(),
                            'bundle'=> $bundle->toArray()
                        ]);

        $sourceorgcodeSubscriber = new AddOrganisationFieldSubscriber(
                                        $factory,array('parameters'=>$parameters));

         $builder->addEventSubscriber($sourceorgcodeSubscriber);

        /*END FUENTE DEL MAPA*/

       /*AMBITO POLITICO*/
        $country = array(//parametros del pais
                        'name'=>'scopecountry',//propiedad
                        'getMethod'=>'getScopecountry',//metodo
                        'options'=>array(//opciones del campo html
                                'placeholder'   => 'government.level.country',//para la traduccion del texto por defecto
                                'attr'=>array(//attributos del html
                                    'code_id'=>196,
                                    'class'=>'select2 countryid',//css
                                    'style'=>'width:100%',//css
                                    'onChange'=>"$('.admin1id').html('')"//js
                                    )
                                )
                        );

        $countrySubscriber = new AddCountryFieldSubscriber(
                                                $factory ,
                                                array('parameters'=>
                                                new Arraypath([
                                                        'country'=>$country,
                                                        'bundle'=> $bundle->set('country', NULL)->toArray()//Evitar que aparezca seleccionado en el filtro
                                                        ])
                                                ));
        $builder->addEventSubscriber($countrySubscriber);

        $state = array(//parametros de la provincia
                        'name'=>'scopeadmin1',//propiedad
                        'getMethod'=>'getScopeadmin1',//metodo
                        'options'=>array(//opciones del campo html
                                'attr'=>array(
                                    'code_id'=>197,
                                    'class'=>'select2 admin1id',//css
                                    'style'=>'width:100%'//css
                                    )
                                )
                        );

        $stateSubscriber = new AddAdmin1FieldSubscriber(
                                        $factory,array('parameters'=>
                                                new Arraypath([
                                                        'country'=>$country,
                                                        'state'=>$state,
                                                        'bundle'=> $bundle->toArray()
                                                        ])
                                                ));
        $builder->addEventSubscriber($stateSubscriber);        
        
        
        $province = array(//parametros de la provincia/area
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
                                                        'country'=>$country,
                                                        'province'=>$province,
                                                        'bundle'=> $bundle->toArray()
                                                        ])
                                                ));
        $builder->addEventSubscriber($provinceSubscriber);

        $municipality = array(//parametros del municipio
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
                                                        'country'=>$country,
                                                        'province'=>$province,
                                                        'municipality'=>$municipality,
                                                        'bundle'=> $bundle->toArray()
                                                        ])
                                                ));
        $builder->addEventSubscriber($municipalitySubscriber);

        /*END AMBITO POLITICO*/


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

    /*SURVEYOR*/
        $surveygradeorg = new Arraypath(array(//parametros
                'name'=>'surveygradeorg',//propiedad
                'getMethod'=>'getSurveygradeorg',//metodo
                'options'=>array(//opciones del campo html
                    'placeholder'   => 'select.organisation',//para la traduccion del texto por defecto
                    'attr'=>array(//attributos del html
                        'code_id'=>203,
                        'class'=>'select2 organisation',//css
                        'style'=>'width:100%',//css
                    )
                )
            )
        );
        $parameters = new Arraypath([
            'organisation'=>$surveygradeorg->toArray(),
            'bundle'=> $bundle->toArray()
        ]);
        $surveygradeorgSubscriber = new AddOrganisationFieldSubscriber(
            $factory,array('parameters'=>$parameters));
        $builder->addEventSubscriber($surveygradeorgSubscriber);


        $principalsurveyorid = new Arraypath(array(//parametros
                'name'=>'principalsurveyorid',//propiedad
                'getMethod'=>'getPrincipalsurveyorid',//metodo
                'options'=>array(//opciones del campo html
                    'placeholder'   => 'select.person',//para la traduccion del texto por defecto
                    'attr'=>array(//attributos del html
                        'code_id'=>208,
                        'class'=>'select2 person',//css
                        'style'=>'width:100%',//css
                    )
                )
            )
        );
        $parameters = new Arraypath([
            'person'=>$principalsurveyorid->toArray(),
            'bundle'=> $bundle->toArray()
        ]);
        $principalsurveyoridSubscriber = new AddPersonFieldSubscriber(
            $factory,array('parameters'=>$parameters));
        $builder->addEventSubscriber($principalsurveyoridSubscriber);

        $principaldrafterid = new Arraypath(array(//parametros
                'name'=>'principaldrafterid',//propiedad
                'getMethod'=>'getPrincipaldrafterid',//metodo
                'options'=>array(//opciones del campo html
                    'placeholder'   => 'select.person',//para la traduccion del texto por defecto
                    'attr'=>array(//attributos del html
                        'code_id'=>402,
                        'class'=>'select2 person',//css
                        'style'=>'width:100%',//css
                    )
                )
            )
        );
        $parameters = new Arraypath([
            'person'=>$principaldrafterid->toArray(),
            'bundle'=> $bundle->toArray()
        ]);
        $principaldrafteridSubscriber = new AddPersonFieldSubscriber(
            $factory,array('parameters'=>$parameters));
        $builder->addEventSubscriber($principaldrafteridSubscriber);

        /*END SURVEYOR*/

        $fields =   '202:name,271:number,272:subsheetname,204:surveygradevalue,209:sourceifnoid,607:surveystartyear,207:surveyfinishyear,'.
                    '273:latestupdateyear,557:edition,554:geogcoordsshown,'.
                    '205:scale,551:geodeticdatum,552:heightdatum,553:grid,274:scopenlatitude,'.
                    '275:scopeslatitude,276:scopeelongitude,277:scopewlongitude';

        foreach(explode(',', $fields) as $el){
            if(!strpos($el,':')) {var_dump ($el);exit;}
            $field = explode(':', $el);

            $arr = ['attr'=>['code_id'=>$field[0]]];
            $builder->add($field[1], NULL, $arr);
        }

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event){
            /**
             * @var Map $entity
             */
            /*@todo validación mapa*/
            $data = $event->getData();
            $entity = $event->getData();
            if($entity->getScopeadmin2areamap()!= NULL && ($entity->getScopeadmin2areamap()->getMapid() == $entity->getMapid())){
                $form = $event->getForm();
                $form->get('scopeadmin2areamap')->addError(new \Symfony\Component\Form\FormError($this->translator->trans('form.field.circular.reference.error',  [], 'caveerrors')));
            }
        });
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\GptCaveBundle\Entity\Map',
            'parameters'=>array(),
            'translator'=>Translator::class
            ));
    }
}


