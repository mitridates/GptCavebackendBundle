<?php
namespace App\GptCavebackendBundle\Form\Type\Cave;
use App\GptCavebackendBundle\EventListener\Form\AddPersonFieldSubscriber;
use App\GptCaveBundle\Entity\Cavediscovery;
use phpDocumentor\Reflection\DocBlock\Tags\Return_;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\GptCavebackendBundle\EventListener\Form\AddDynamicPersonFieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddOrganisationFieldSubscriber;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use App\Cave\LibBundle\Format\Arraypath;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Contracts\Translation\TranslatorInterface;

class EditDiscoveryType extends AbstractType
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
        $this->translator = $defaults['translator'];
        $factory = $builder->getFormFactory();

        $personSubscriber = new AddPersonFieldSubscriber($factory, array(
            'options'=>array(
                'attr'=>array(
                    'code_id'=>419
                )
            )
        ));
        $builder->addEventSubscriber($personSubscriber);

        $organisationSubscriber = new AddOrganisationFieldSubscriber($factory, array(
            'options'=>array(
                'attr'=>array(
                    'code_id'=>420
                )
            )
        ));
        $builder->addEventSubscriber($organisationSubscriber);

        $builder->add(
               'type', EntityType::class, array(
                            'class'=>'GptCaveBundle:Fieldvaluecode',
                            'attr'=>array('code_id'=>30, 'class'=>'select2', 'style'=>'width:100%'),
                            'required' => true,
                            'choice_label' => 'value',
                            'choice_value'=>'id',
                            'query_builder' => function(EntityRepository $e){
                                return $e->createQueryBuilder('f')
                                ->select('f')
                                ->where('f.field = :field')
                                ->orderBy('f.value', 'ASC')
                                ->setParameter('field',  30);
                                },
                            )
                );

        $builder->add(
               'datequalifier', EntityType::class, array(
                            'class'=>'GptCaveBundle:Fieldvaluecode',
                            'attr'=>array('code_id'=> 477),
                            'required' => false,
                            'choice_label' => 'value',
                            'choice_value'=>'id',
                            'query_builder' => function(EntityRepository $e){
                                return $e->createQueryBuilder('f')
                                ->select('f')
                                ->where('f.field = :field')
                                ->orderBy('f.value', 'ASC')
                                ->setParameter('field',  507);
                                },
                            )
                );        
        //31:name; no es necesario
        $fields = '523:firstname;522:surname;524:organisationinitials;32:day;33:month;34:year;10001:position';

        foreach(explode(';', $fields) as $el){
            if(!strpos($el,':')) {var_dump ($el);exit;}
            $field = explode(':', $el);
            $arr = ['attr'=>['code_id'=>$field[0]]];
            $builder->add($field[1], NULL, $arr);
        }

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event){
            /**
             * @var Cavediscovery $entity
             */
            $entity = $event->getData();
            $form = $event->getForm();
            $dateIsNull=            $this->checkDate($entity, $form);
            $personIsNull=          $this->checkPerson($entity, $form);
            $organisationIsNull=    $this->checkOrganisation($entity, $form);
    
            if( $dateIsNull=== NULL &&
                $personIsNull=== NULL &&
                $organisationIsNull=== NULL
            ){
                    $form->addError(new FormError($this->translator->trans('form.cannot.be.empty',  [], 'caveerrors')));
            }
        });

    }

    private function checkDate(Cavediscovery $entity, FormInterface &$form){

        if(empty($entity->getDatequalifier())){
            return NULL;
        }elseif(!checkdate($entity->getMonth(), $entity->getDay(), $entity->getYear())){
            $form->addError(new FormError($this->translator->trans('form.invalid.date',  [], 'caveerrors')));
            return false;
        }else{
            return true;
        }
    }


    private function checkPerson(Cavediscovery $entity, FormInterface &$form){

        if(empty($entity->getFirstname()) && empty($entity->getSurname()) && empty($entity->getPerson())){
            return NULL;
        }elseif(!(empty($entity->getFirstname()) && empty($entity->getSurname())) && !empty($entity->getPerson())){
            $form->get('person')->addError(new FormError($this->translator->trans('form.use.selector.or.text',  [], 'caveerrors')));
            return false;
        }
        return true;
    }

    private function checkOrganisation(Cavediscovery $entity, FormInterface &$form){

        if(empty($entity->getOrganisationinitials()) && empty($entity->getOrganisation())){
            return NULL;
        }elseif(!empty($entity->getOrganisationinitials()) && !empty($entity->getOrganisation())){
            $form->get('organisation')->addError(new FormError($this->translator->trans('form.use.selector.or.text',  [], 'caveerrors')));
            return false;
        }
        return true;
    }    

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\GptCaveBundle\Entity\Cavediscovery',
            'parameters'=>array(),
            'translator'=>TranslatorInterface::class
            ));
    }
}


