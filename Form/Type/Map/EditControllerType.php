<?php
namespace App\GptCavebackendBundle\Form\Type\Map;
use App\GptCaveBundle\Entity\Mapcontroller;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\GptCavebackendBundle\EventListener\Form\AddOrganisationFieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddDynamicPersonFieldSubscriber;
use App\Cave\LibBundle\Format\Arraypath;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Contracts\Translation\TranslatorInterface;

class EditControllerType extends AbstractType
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
        $xparams = $defaults['parameters'];//Arraypath parametros del bundle
        $this->translator = $defaults['translator'];
        $factory = $builder->getFormFactory();



        $organisation = new Arraypath(array(//parametros
                        'name'=>'organisation',//propiedad
                        'getMethod'=>'getOrganisation',//metodo
                        'options'=>array(//opciones del campo html
                                'placeholder'   => 'select.organisation',//para la traduccion del texto por defecto
                                'attr'=>array(//attributos del html
                                    'code_id'=>406,
                                    'class'=>'select2 organisation',//css
                                    'style'=>'width:100%',//css
                                    )
                                )
                        )
                );
        $parameters = new Arraypath([
                            'organisation'=>$organisation->toArray(),
                            'bundle'=> $xparams->toArray()
                        ]);

        $organisationSubscriber = new AddOrganisationFieldSubscriber(
                                        $factory,array('parameters'=>$parameters));

        $builder->addEventSubscriber($organisationSubscriber);

         
        
        $person = new Arraypath(array(//parametros
                        'name'=>'person',//propiedad
                        'getMethod'=>'getPerson',//metodo
                        'options'=>array(//opciones del campo html
                                'placeholder'   => 'select.person',//para la traduccion del texto por defecto
                                'attr'=>array(//attributos del html
                                    'code_id'=>407,
                                    'class'=>'select2 person',//css
                                    'style'=>'width:100%',//css
                                    )
                                )
                        )
                );
        $parameters = new Arraypath([
                            'person'=>$person->toArray(),
                            'bundle'=> $xparams->toArray()
                        ]);
        $personSubscriber = new AddPersonFieldSubscriber(
                                        $factory,array('parameters'=>$parameters));
        $builder->addEventSubscriber($personSubscriber);          

        $fields = '210:controllerifnoid,408:comment';
        foreach(explode(',', $fields) as $el){
            
            if(!strpos($el,':')) {var_dump ($el);exit;}
            $field = explode(':', $el);

            $arr = ['attr'=>['code_id'=>$field[0]]];

            $builder->add($field[1], NULL, $arr);
        }

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event){
            /**
             * @var Mapcontroller $entity
             */
            $entity = $event->getData();
            $form = $event->getForm();
            if(  empty($entity->getPerson())
                 && empty($entity->getOrganisation()) //obligatorio?
                 && empty($entity->getControllerifnoid())
            ){
                $form = $event->getForm();
                $form->addError(new \Symfony\Component\Form\FormError($this->translator->trans('form.cannot.be.empty',  [], 'caveerrors')));
            }

        });        
        
    }

    
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\GptCaveBundle\Entity\Mapcontroller',
            'parameters'=>array(),
            'translator'=>Translator::class
            ));
    }
}


