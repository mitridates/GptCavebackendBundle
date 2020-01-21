<?php
namespace App\GptCavebackendBundle\Form\Type\Map;
use App\GptCaveBundle\Entity\Mapsurveyor;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\GptCavebackendBundle\EventListener\Form\AddDynamicPersonFieldSubscriber;
use App\Cave\LibBundle\Format\Arraypath;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use App\Cave\LibBundle\Exception\TranslatableException;
use Symfony\Contracts\Translation\TranslatorInterface;

class EditSurveyorType extends AbstractType
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
                        'name'=>'surveyorid',//propiedad
                        'getMethod'=>'getSurveyorid',//metodo
                        'options'=>array(//opciones del campo html
                                'placeholder'   => 'select.person',//para la traduccion del texto por defecto
                                'attr'=>array(//attributos del html
                                    'code_id'=>586,
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

        $fields = '584:surveyor,10001:position';

        foreach(explode(',', $fields) as $el){
            if(!strpos($el,':')) {var_dump ($el);exit;}
            $field = explode(':', $el);
            $arr = ['attr'=>['code_id'=>$field[0]]];
            $builder->add($field[1], NULL, $arr);
        }

         $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event){
             /**
              * @var Mapsurveyor $entity
              */
            $entity = $event->getData();
            if(empty($entity->getSurveyorid()) && empty($entity->getSurveyor())){
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
            'data_class' => 'App\GptCaveBundle\Entity\Mapsurveyor',
            'parameters'=>array(),
            'translator'=>Translator::class
            ));
   }
}


