<?php
namespace App\GptCavebackendBundle\Form\Type\Cave;
use App\GptCaveBundle\Entity\Cavedamage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Contracts\Translation\TranslatorInterface;
class EditDamageType extends AbstractType
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
        $builder->add('position', NULL, ['attr'=>['code_id'=>10001]]);
        $builder->add(
               'damage', EntityType::class, array(
                            'class'=>'GptCaveBundle:Fieldvaluecode',
                            'attr'=>array('code_id'=>43, 'class'=>'select2', 'style'=>'width:100%'),
                            'required' => true,
                            'choice_label' => 'value',
                            'choice_value'=>'id',
                            'query_builder' => function(EntityRepository $e){
                                                return $e->createQueryBuilder('f')
                                                ->select('f')
                                                ->where('f.field = :field')
                                                ->orderBy('f.field', 'DESC')
                                                ->setParameter('field',  43);
                                    },
                            )
                );

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event){
            /**
             * @var Cavedamage $entity
             */
            $entity = $event->getData();
            $form = $event->getForm();
            if( $entity->getDamage() === NULL
            ){
                $form->get('damage')->addError(new FormError($this->translator->trans('form.cannot.be.empty',  [], 'caveerrors')));
            }
        });
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\GptCaveBundle\Entity\Cavedamage',
            'parameters'=>array(),
            'translator'=>TranslatorInterface::class
            ));
    }
}


