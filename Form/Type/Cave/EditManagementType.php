<?php
namespace App\GptCavebackendBundle\Form\Type\Cave;
use App\GptCavebackendBundle\EventListener\Form\AddCountryFieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddPersonFieldSubscriber;
use App\GptCaveBundle\Entity\Cavemanagement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\GptCavebackendBundle\EventListener\Form\AddDynamicPersonFieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddOrganisationFieldSubscriber;
use App\Cave\LibBundle\Format\Arraypath;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Contracts\Translation\TranslatorInterface;

class EditManagementType extends AbstractType
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
            'name'=>'ownerperson',
            'getMethod'=>'getOwnerperson',
            'options'=>array(
                'attr'=>array(
                    'code_id'=>228,
                    'class'=>'select2 person',
                )
            )
        ));

        $builder->addEventSubscriber($personSubscriber);

        $organisationSubscriber = new AddOrganisationFieldSubscriber($factory, array(
            'name'=>'ownerorganisation',
            'getMethod'=>'getOwnerorganisation',
            'options'=>array(
                'attr'=>array(
                    'code_id'=>417,
                    'class'=>'select2 organisation',
                )
            )
        ));

        $builder->addEventSubscriber($organisationSubscriber);

        foreach(explode(';','42:ownertype;45:managementcategory') as $el){
            $field = explode(':', $el);
            $builder->add( $field[1], EntityType::class, array(
                        'class'=>'GptCaveBundle:Fieldvaluecode',
                        'attr'=>array('code_id'=>$field[0], 'class'=>'select2', 'style'=>'width:100%'),
                        'required' => false,
                        'choice_label' => 'value',
                        'choice_value'=>'id',
                        'query_builder' => function(EntityRepository $e) use ($field){
                                            return $e->createQueryBuilder('f')
                                            ->select('f')
                                            ->where('f.field = :field')
                                            ->orderBy('f.value', 'ASC')
                                            ->setParameter('field', $field[0]);
                                },
                        )
                    );
        }



        foreach(explode(';', '44:managementclassifier;47:controller;418:ownername') as $el){
            $field = explode(':', $el);
            $arr = ['attr'=>['code_id'=>$field[0]]];
            $builder->add($field[1], NULL, $arr);
        }

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event){
            /**
             * @var Cavemanagement $entity
             */

            $entity = $event->getData();
            $form = $event->getForm();

            $managerNull=            $this->checkManager($entity, $form);
            $ownerIsNull=          $this->checkOwner($entity, $form);
            if( $managerNull=== NULL
                && $ownerIsNull=== NULL
                && $entity->getController()=== NULL
            ){
                $form->addError(new FormError($this->translator->trans('form.cannot.be.empty',  [], 'caveerrors')));
                $form->addError(new FormError($this->translator->trans('form.use.button.to.delete',  [], 'caveerrors')));
            }
        });

    }


    /**
     * @param Cavemanagement $entity
     * @param FormInterface $form
     * @return bool|null
     */
    private function checkManager(Cavemanagement $entity, FormInterface &$form)
    {
        if ($entity->getManagementcategory() === NULL && $entity->getManagementclassifier() === NULL){
            return NULL;
        }
        if($entity->getManagementcategory() === NULL && $entity->getManagementclassifier()!== NULL){
            $form->get('managementcategory')->addError(new FormError($this->translator->trans('form.empty.master.has.filled.children',  [], 'caveerrors')));
            return false;
        }
        if($entity->getManagementclassifier() === NULL && $entity->getManagementcategory()!== NULL){
            $form->get('managementclassifier')->addError(new FormError($this->translator->trans('form.empty.master.has.filled.children',  [], 'caveerrors')));
            return false;
        }
        return true;
    }


    private function checkOwner(Cavemanagement $entity, FormInterface &$form){
        $fieldNames = ['ownername','ownerorganisation','ownerperson'];
        $is_empty= 0;

        foreach($fieldNames as $name){
            if( $entity->{'get'.ucfirst($name)}()!== NULL){
                $is_empty++;
            }
        }
        
        if($is_empty== 0 ){
            if($entity->getOwnertype()!==NULL){
                $form->get('ownertype')->addError(new FormError($this->translator->trans('form.field.has.dependencies',  [], 'caveerrors')));
            }
            return NULL;
        }elseif ($is_empty== 1){
            return true;
        }elseif ($is_empty>= 2 ){
            $form->addError(new FormError($this->translator->trans('form.use.onefield',  [], 'caveerrors')));
            return false;
        }
        return false;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\GptCaveBundle\Entity\Cavemanagement',
            'translator'=>TranslatorInterface::class
        ));
    }
}


