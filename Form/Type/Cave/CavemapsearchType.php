<?php
namespace App\GptCavebackendBundle\Form\Type\Cave;
use Symfony\Component\Form\AbstractType;
use App\Cave\LibBundle\Format\Arraypath;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\GptCavebackendBundle\EventListener\Form\AddCountryFieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddAdmin1FieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddAdmin2FieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddAdmin3FieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddAreaFieldSubscriber;
use Symfony\Contracts\Translation\TranslatorInterface;

class CavemapsearchType extends AbstractType
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
        $bundle = $defaults['parameters'];//Arraypath parametros del bundle
        $this->translator = $defaults['translator'];

        $factory = $builder->getFormFactory();
       //LOCATION
        $country = array(//parametros del pais
                        'name'=>'country',//propiedad
                        'getMethod'=>'getCountry',//metodo
                        'options'=>array(//opciones del campo html
                                //'mapped'=>false,
                                'placeholder'   => 'government.level.country',//para la traduccion del texto por defecto
                                'attr'=>array(//attributos del html
                                    'code_id'=>220,
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
                        'name'=>'admin1',//propiedad
                        'getMethod'=>'getAdmin1',//metodo
                        'options'=>array(//opciones del campo html
                                //'mapped'=>false,
                                'attr'=>array(
                                    'code_id'=>18,
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
        
        
        $province = array(//parametros de la provincia
                        'name'=>'admin2',//propiedad
                        'getMethod'=>'getAdmin2',//metodo
                        'options'=>array(//opciones del campo html
                                //'mapped'=>false,
                                'attr'=>array(
                                    'code_id'=>10377,
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
                                    //'mapped'=>false,
                                    'attr'=>array(//attributos del html
                                        'code_id'=>10378,
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
        
        
/*        $area = array(//parametros de area
                        'name'=>'area',//propiedad
                        'getMethod'=>'getArea',//metodo
                        'options'=>array(//opciones del campo html
                                //'mapped'=>false,
                                'attr'=>array(
                                    'code_id'=>223,
                                    'class'=>'select2 areaid',//css
                                    'style'=>'width:100%'//css
                                    )
                                )
                        );

        $areaSubscriber = new AddAreaFieldSubscriber(
                                        $factory,array('parameters'=>
                                                new Arraypath([
                                                        'country'=>$country,
                                                        'state'=>$state,
                                                        'area'=>$area,
                                                        'bundle'=> $bundle->toArray()
                                                        ])
                                                ));
        $builder->addEventSubscriber($areaSubscriber);       */
        

        
        foreach(explode(',', 'name:70') as $el){
                            $field = explode(':', $el);
                            $builder->add($field[0], NULL, array(
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
            'data_class' => 'App\GptCaveBundle\Entity\Cave',
            'parameters'=>array(),
            'translator'=>TranslatorInterface::class
        ));
    }
}
