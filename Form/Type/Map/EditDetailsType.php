<?php
namespace App\GptCavebackendBundle\Form\Type\Map;
use App\GptCaveBundle\Entity\Mappublished;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\GptCavebackendBundle\EventListener\Form\AddOrganisationFieldSubscriber;
use App\Cave\LibBundle\Format\Arraypath;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class EditDetailsType extends AbstractType
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
       
        $numbererorgcode = new Arraypath(array(//parametros
                        'name'=>'numbererorgcode',//propiedad
                        'getMethod'=>'getNumbererorgcode',//metodo
                        'options'=>array(//opciones del campo html
                                'placeholder'   => 'select.organisation',//para la traduccion del texto por defecto
                                'attr'=>array(//attributos del html
                                    'code_id'=>517,
                                    'class'=>'select2 organisation',//css
                                    'style'=>'width:100%',//css
                                    )
                                )
                        )
                );
        $parameters = new Arraypath([
                            'organisation'=>$numbererorgcode->toArray(),
                            'bundle'=> $bundle->toArray()
                        ]);
        $numbererorgcodeSubscriber = new AddOrganisationFieldSubscriber(
                                        $factory,array('parameters'=>$parameters));
        $builder->addEventSubscriber($numbererorgcodeSubscriber);

        $fields = '558:specialsheetname,212:restricted,10102:restrictedcomment,' .
            '555:vectorversionavail,556:rasterversionavail,403:digitalmaster,213:microfilmed,'.
            '10103:viewsshownp,10104:viewsshownl,10105:viewsshownx,'.
            '201:sequencenumber,199:caveserialnumber,214:sheetsize,404:sheetquantity,401:issueyear';

        foreach(explode(',', $fields) as $el){
            if(!strpos($el,':')) {var_dump ($el);exit;}
            $field = explode(':', $el);

            $arr = ['attr'=>['code_id'=>$field[0]]];
            $builder->add($field[1], NULL, $arr);
        }
        
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event){
            /**
             * @var Mappublished $entity
             */
            $entity = $event->getData();
            $form = $event->getForm();
/*
            $hasName=            $this->checkManager($entity, $form);
            $ownerIsNull=          $this->checkOwner($entity, $form);
            if( $managerNull=== NULL
                && $ownerIsNull=== NULL
                && $entity->getController()=== NULL
            ){
                $form->addError(new FormError($this->translator->trans('form.cannot.be.empty',  [], 'caveerrors')));
                $form->addError(new FormError($this->translator->trans('form.use.button.to.delete',  [], 'caveerrors')));
            }*/

        });
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\GptCaveBundle\Entity\Mapdetails',
            'parameters'=>array(),
            'translator'=>Translator::class
        ));
    }
}


