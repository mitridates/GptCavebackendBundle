<?php
namespace App\GptCavebackendBundle\Form\Type\Map;
use App\GptCaveBundle\Entity\Mapfurtherpc;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\GptCavebackendBundle\EventListener\Form\AddCountryFieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddAdmin1FieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddAdmin2FieldSubscriber;
use App\Cave\LibBundle\Format\Arraypath;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Contracts\Translation\TranslatorInterface;

class EditFurtherpcType extends AbstractType
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
                            'required'=>true,
                            'placeholder'   => 'select.government.level.country',//para la traduccion del texto por defecto
                            'attr'=>array(//attributos del html
                                'code_id'=>368,
                                'class'=>'select2',//css
                                'style'=>'width:100%',//css

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
                            'attr'=>array(
                                'code_id'=>369,
                                'class'=>'select2',//css
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
            'name'=>'admin2',//propiedad
            'getMethod'=>'getAdmin2',//metodo
            'options'=>array(//opciones del campo html
                'attr'=>array(
                    'code_id'=>409,
                    'class'=>'select2',//css
                    'style'=>'width:100%'//css
                )
            )
        );
        //FIXME: La cobertura política adicional permite incluir nuevas áreas, admin2 no existe en este contexto
        //  añadir el campo manytoone area

        $provinceSubscriber = new AddAdmin2FieldSubscriber(
            $factory,array('parameters'=>
            new Arraypath([
                'country'=>$country,
                'state'=>$state,
                'province'=>$province,
                'bundle'=> $bundle->toArray()
            ])
        ));
        $builder->addEventSubscriber($provinceSubscriber);

        $builder->add('position', NULL, ['attr'=>['code_id'=>10001]]);
        
         $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event){
             /**
              * @var Mapfurtherpc $entity
              */
             $entity = $event->getData();
             $form = $event->getForm();
            if(empty($entity->getCountry())
               // || empty($entity->getAdmin1()) //obligatorio?
               // || empty($entity->getAdmin2 ())
            ){
                $form = $event->getForm();
                $form->get('country')->addError(new \Symfony\Component\Form\FormError($this->translator->trans('form.must.complete.this.field',  [], 'caveerrors')));
            }
         });  
         
    }
     
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\GptCaveBundle\Entity\Mapfurtherpc',
            'parameters'=>array(),
            'translator'=>Translator::class
            ));
    }
}


