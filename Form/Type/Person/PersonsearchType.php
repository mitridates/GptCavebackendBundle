<?php

namespace App\GptCavebackendBundle\Form\Type\Person;

use Symfony\Component\Form\AbstractType;
use App\Cave\LibBundle\Format\Arraypath;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\GptCavebackendBundle\EventListener\Form\AddCountryFieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddAdmin2FieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddAdmin3FieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddOrganisationFieldSubscriber;

class PersonsearchType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $defaults
     */
    public function buildForm(FormBuilderInterface $builder, array $defaults)
    {
        $bundle = $defaults['parameters'];//Arraypath parametros del bundle
        $factory = $builder->getFormFactory();

        $organisation = new Arraypath(array(//parametros del pais
                        'name'=>'organisation1',//propiedad
                        'getMethod'=>'getOrganisation1',//metodo
                        'options'=>array(//opciones del campo html
                                'placeholder'   => 'select.organisation',//para la traduccion del texto por defecto
                                'attr'=>array(//attributos del html
                                    'code_id'=>501,
                                    'class'=>'select2 organisation',//css
                                    'style'=>'width:100%',//css
                                    )
                                )
                        )
                );
        
        $parameters = new Arraypath([
                            'organisation'=>$organisation->toArray(),
                            'bundle'=> $bundle
                        ]);

        $organisationSubscriber = new AddOrganisationFieldSubscriber(
                                        $factory,array('parameters'=>$parameters));

         $builder->addEventSubscriber($organisationSubscriber);        
        
        //PERSON LOCATION
        $country = array(//parametros del pais
                        'name'=>'country',//propiedad
                        'getMethod'=>'getCountry',//metodo
                        'options'=>array(//opciones del campo html
                               // 'placeholder'   => 'select.government.level.country',//para la traduccion del texto por defecto
                                'attr'=>array(//attributos del html
                                    'class'=>'select2 countryid',//css
                                    'style'=>'width:100%',//css
                                    'code_id'=>493,
                                    'onChange'=>"$('.admin2, .admin3').html('')"//js
                                    )
                                )
                        );

        $countrySubscriber = new AddCountryFieldSubscriber(
                                        $factory,array('parameters'=>
                                                new Arraypath([
                                                        'country'=>$country,
                                                        'bundle'=> $bundle->set('country', NULL)->toArray()//Evitar que aparezca seleccionado en el filtro
                                                        ])
                                                ));
        $builder->addEventSubscriber($countrySubscriber);

        $province = array(//parametros de la provincia
                        'name'=>'admin2',//propiedad
                        'getMethod'=>'getAdmin2',//metodo
                        'options'=>array(//opciones del campo html
                                'attr'=>array(
                                    'code_id'=>490,
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
                            'name'=>'admin3',//nombre del campo
                            'getMethod'=>'getAdmin3',//método get
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


        foreach(explode(',', 'surname:479,name:480') as $el){
                            $field = explode(':', $el);
                            $builder->add($field[0], NULL, array(
                                //'label'=>'cod'.$field[1],
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
            'parameters'=>array(),
        ));
    }
}
