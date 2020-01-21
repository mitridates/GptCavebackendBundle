<?php
namespace App\GptCavebackendBundle\Form\Type\Map;
use App\GptCaveBundle\Entity\Mapupdater;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\GptCavebackendBundle\EventListener\Form\AddDynamicPersonFieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddOrganisationFieldSubscriber;
use App\Cave\LibBundle\Format\Arraypath;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Contracts\Translation\TranslatorInterface;

class EditUpdaterType extends AbstractType
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
        
        $biblioupdaterorgid = new Arraypath(array(//parametros
                        'name'=>'organisation',//propiedad
                        'getMethod'=>'getOrganisation',//metodo
                        'options'=>array(//opciones del campo html
                                'placeholder'   => 'select.organisation',//para la traduccion del texto por defecto
                                'attr'=>array(//attributos del html
                                    'code_id'=>581,
                                    'class'=>'select2 organisation',//css
                                    'style'=>'width:100%',//css
                                    )
                                )
                        )
                );
        $parameters = new Arraypath([
                            'organisation'=>$biblioupdaterorgid->toArray(),
                            'bundle'=> $bundle->toArray()
                        ]);
        $biblioupdaterorgidSubscriber = new AddOrganisationFieldSubscriber(
                                        $factory,array('parameters'=>$parameters));
        $builder->addEventSubscriber($biblioupdaterorgidSubscriber);
        
        $biblioupdaterid = new Arraypath(array(//parametros
                        'name'=>'person',//propiedad
                        'getMethod'=>'getPerson',//metodo
                        'options'=>array(//opciones del campo html
                                'placeholder'   => 'select.person',//para la traduccion del texto por defecto
                                'attr'=>array(//attributos del html
                                    'code_id'=>580,
                                    'class'=>'select2 person',//css
                                    'style'=>'width:100%',//css
                                    )
                                )
                        )
                );
        $parameters = new Arraypath([
                            'person'=>$biblioupdaterid->toArray(),
                            'bundle'=> $bundle->toArray()
                        ]);
        $biblioupdateridSubscriber = new AddPersonFieldSubscriber(
                                        $factory,array('parameters'=>$parameters));
        $builder->addEventSubscriber($biblioupdateridSubscriber);

        //$fields = 'name:215,organisationinitials:216,biblioupdateryear:217';
        $fields = 'year:217,name:215,organisationinitials:216';
        
        
        foreach(explode(',', $fields) as $el){
            if(!strpos($el,':')) {var_dump ($el);exit;}
            $field = explode(':', $el);

            $arr = ['attr'=>['code_id'=>$field[1]]];
            $builder->add($field[0], NULL, $arr);
        }

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event){
            /**
             * @var Mapupdater $entity
             */
            $entity = $event->getData();
            $form = $event->getForm();
            $personIsNull= $this->checkPerson($entity, $form);
            $organisationIsNull=   $this->checkOrganisation($entity, $form);
            $yearIsNull= empty($entity->getYear())? NULL : false ;

            if( $personIsNull== NULL &&
                $organisationIsNull== NULL
            ){
                $form->addError(new FormError($this->translator->trans('form.use.selector.or.text',  [], 'caveerrors')));
            }

            if( $personIsNull=== NULL &&
                $organisationIsNull=== NULL &&
                $yearIsNull=== NULL
            ){
                $form->addError(new FormError($this->translator->trans('form.cannot.be.empty',  [], 'caveerrors')));
            }
        });
    }

    private function checkPerson(Mapupdater $entity, FormInterface &$form){
        if(empty($entity->getPerson()) && empty($entity->getName())){
            return NULL;
        }elseif(!empty($entity->getPerson()) && !empty($entity->getName())){
            $form->get('person')->addError(new FormError($this->translator->trans('form.use.selector.or.text',  [], 'caveerrors')));
            return false;
        }else{
            return true;
        }
    }

    private function checkOrganisation(Mapupdater $entity, FormInterface &$form){
        if(empty($entity->getOrganisation()) && empty($entity->getOrganisationinitials())){
            return NULL;
        }elseif(!empty($entity->getOrganisation()) && !empty($entity->getOrganisationinitials())){
            $form->get('organisation')->addError(new FormError($this->translator->trans('form.use.selector.or.text',  [], 'caveerrors')));
            return false;
        }else{
            return true;
        }
    }
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\GptCaveBundle\Entity\Mapupdater',
            'parameters'=>array(),
            'translator'=>Translator::class
            ));
    }
}


