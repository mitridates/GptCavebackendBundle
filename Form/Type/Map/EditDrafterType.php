<?php
namespace App\GptCavebackendBundle\Form\Type\Map;
use App\GptCaveBundle\Entity\Mapdrafter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\GptCavebackendBundle\EventListener\Form\AddDynamicPersonFieldSubscriber;
use App\Cave\LibBundle\Format\Arraypath;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Contracts\Translation\TranslatorInterface;

class EditDrafterType extends AbstractType
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
        $person = new Arraypath(array(//parametros
                        'name'=>'drafterid',//propiedad
                        'getMethod'=>'getDrafterid',//metodo
                        'options'=>array(//opciones del campo html
                                'placeholder'   => 'select.person',//para la traduccion del texto por defecto
                                'attr'=>array(//attributos del html
                                    'code_id'=>587,
                                    'class'=>'select2 person',//css
                                    'style'=>'width:100%',//css
                                    )
                                )
                        )
                );
        $parameters = new Arraypath([
                            'person'=>$person->toArray(),
                            'bundle'=> $bundle->toArray()
                        ]);
        $personSubscriber = new AddPersonFieldSubscriber(
                                        $factory,array('parameters'=>$parameters));
        $builder->addEventSubscriber($personSubscriber);

        $fields = '585:drafter,10001:position';//

        foreach(explode(',', $fields) as $el){
            if(!strpos($el,':')) {var_dump ($el);exit;}
            $field = explode(':', $el);
            $arr = ['attr'=>['code_id'=>$field[0]]];
            $builder->add($field[1], NULL, $arr);
        }

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event){
            /**
             * @var Mapdrafter $entity
             */
            $entity = $event->getData();
            $form = $event->getForm();
            if(empty($entity->getDrafterid()) && empty($entity->getDrafter())){
                $form = $event->getForm();
                $form->addError(new \Symfony\Component\Form\FormError($this->translator->trans('form.use.selector.or.text',  [], 'caveerrors')));
            }
        });

    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\GptCaveBundle\Entity\Mapdrafter',
            'parameters'=>array(),
            'translator'=>Translator::class
            ));
    }
}


