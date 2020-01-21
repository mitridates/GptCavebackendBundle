<?php
namespace App\GptCavebackendBundle\Form\Type\Person;

use Doctrine\DBAL\Types\BooleanType;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Component\Form\AbstractType;
use App\Cave\LibBundle\Format\Arraypath;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\GptCavebackendBundle\EventListener\Form\AddAdmin3FieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddCountryFieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddAdmin2FieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddOrganisationFieldSubscriber;
use Symfony\Contracts\Translation\TranslatorInterface;

class PersonType extends AbstractType
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

         $organisation->set('name', 'organisation2')
                 ->set('getMethod', 'getOrganisation2')
                 ->set('options:attr:code_id', 502);

         $parameters->set('organisation', $organisation->toArray());

         $organisationSubscriber = new AddOrganisationFieldSubscriber(
                                        $factory,array('parameters'=>$parameters));

          $builder->addEventSubscriber($organisationSubscriber);

         $organisation->set('name', 'organisation3')
                 ->set('getMethod', 'getOrganisation3')
                 ->set('options:attr:code_id', 503);

         $parameters->set('organisation', $organisation->toArray());

         $organisationSubscriber = new AddOrganisationFieldSubscriber(
                                        $factory,array('parameters'=>$parameters));

        $builder->addEventSubscriber($organisationSubscriber);

        //PERSON LOCATION
        $country = array(//parametros del pais
                        'name'=>'country',//propiedad
                        'getMethod'=>'getCountry',//metodo
                        'options'=>array(//opciones del campo html
                                'placeholder'   => 'select.government.level.country',//para la traduccion del texto por defecto
                                'attr'=>array(//attributos del html
                                    'class'=>'select2 countryid',//css
                                    'style'=>'width:100%',//css
                                    'onChange'=>"$('.admin2id, .admin3id').html('')"//js
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

        $builder->add('email', EmailType::class, [
            'required'=>false,
            'attr'=>[
                'code_id'=>494,
                'class'=>'js-email',//css
                ]
        ])->add('gender', ChoiceType::class, array(
            'required'=>false,
            'choice_translation_domain' => 'cavemessages',
            'attr'=>[
                'code_id'=>'gender',
            ],
            'choices' =>[
               // 'select.gender.undefined'=>0,
                'select.gender.male' => 'M',
                'select.gender.female' => 'F'
            ]
        ));



        foreach(explode(',', 'surname:479,name:480,title:483,middleinitial:481,initialforgivennames:482'
                            . ',addressline1:484,addressline2:485,addressline3:486,addressline4:487,cityorsuburb:488'
                            . ',postcode:491,phoneprefix:495,homephonenumber:496,workphonenumber:497'
                            . ',mobilephonenumber:498,faxphonenumber:499,pagerphonenumber:500'
                            //.',organisation1:501,organisation2:502,organisation3:503'
                            ) as $el){

                                $field = explode(':', $el);

                                $arr = ['attr'=>['code_id'=>$field[1]]];
                                if($field[1]=='478'){
                                    $arr['read_only']=true;
                                }
                                if(in_array($field[1],['501', '502', '503'])){
                                    $arr['attr']['class']='organisation select2';
                                }
                                $builder->add($field[0], NULL, $arr);
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
            'translator'=>Translator::class
        ));
    }
}


