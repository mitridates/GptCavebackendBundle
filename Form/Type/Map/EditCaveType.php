<?php
namespace App\GptCavebackendBundle\Form\Type\Map;
use App\GptCaveBundle\Entity\Mapcave;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\GptCavebackendBundle\EventListener\Form\AddDynamicCaveFieldSubscriber;
use App\Cave\LibBundle\Format\Arraypath;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Contracts\Translation\TranslatorInterface;

class EditCaveType extends AbstractType
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
         * @var $xparams Arraypath
         */
        $xparams = $defaults['parameters'];//Arraypath parametros del bundle
        $this->translator = $defaults['translator'];
        $factory = $builder->getFormFactory();

        $cave = new Arraypath(array(//parametros
                        'name'=>'cave',//propiedad
                        'getMethod'=>'getCave',//metodo
                        'options'=>array(//opciones del campo html
                                'placeholder'   => 'select.cave',//para la traduccion del texto por defecto
                                'required'=>true,
                                'attr'=>array(//attributos del html
                                    'code_id'=>601,
                                    'class'=>'select2 cave',//css
                                    'style'=>'width:100%',//css
                                    )
                                )
                        )
                );
        $parameters = new Arraypath([
                            'cave'=>$cave->toArray(),
                            'bundle'=> $xparams->toArray()
                        ]);
        $caveSubscriber = new AddCaveFieldSubscriber(
                                        $factory,array('parameters'=>$parameters));
        $builder->addEventSubscriber($caveSubscriber);         
         
        $builder->add('position', NULL, ['attr'=>['code_id'=>10001]]);
        
         $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event){
         /**
          * @var Mapcave $entity
          */
            $data = $event->getData();
            $entity = $event->getData();

             if($data['cave']==''){
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
            'data_class' => 'App\GptCaveBundle\Entity\Mapcave',
            'parameters'=>array(),
            'translator'=>Translator::class
            ));
    }
}


