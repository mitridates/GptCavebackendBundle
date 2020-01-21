<?php
namespace App\GptCavebackendBundle\Form\Type\Cave;
use App\GptCavebackendBundle\EventListener\Form\AddAreaFieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddCountryFieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddAdmin1FieldSubscriber;
use App\GptCaveBundle\Entity\Cavecrossreference;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class EditCrossreferenceType extends AbstractType
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

        $countrySubscriber = new AddCountryFieldSubscriber($factory, array(
            'options'=>array(
                'attr'=>array(
                    'code_id'=>235
                )
            )
        ));
            $builder->addEventSubscriber($countrySubscriber);

        $admin1Subscriber = new AddAdmin1FieldSubscriber($factory,$countrySubscriber->getCountry(), array(
            'options'=>array(
                'attr'=>array(
                    'code_id'=>236
                )
            )
        ));
        $builder->addEventSubscriber($admin1Subscriber);

        $areaSubscriber = new AddAreaFieldSubscriber($factory, $countrySubscriber->getCountry(),  null, array(
            'options'=>array(
                'attr'=>array(
                    'code_id'=>237
                )
            )
        ));
        $builder->addEventSubscriber($areaSubscriber);
        
        $builder->add('position', NULL, ['attr'=>['code_id'=>10001]]);
        $builder->add('serial', NULL, ['attr'=>['code_id'=>74]]);
        /**
         * 305¦Cross-reference lookup:  concatenates country+state+area
         * No es necesario, el id de state, province o municipality ya tienen esa concatenacion
         */
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event){
            /**
             * @var Cavecrossreference $entity
             */
            $entity = $event->getData();
            $form = $event->getForm();
            if( $entity->getSerial() === NULL){
                $form->get('serial')->addError(new FormError($this->translator->trans('form.must.complete.this.field',  [], 'caveerrors')));
            }
        });

    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\GptCaveBundle\Entity\Cavecrossreference',
            'translator'=>TranslatorInterface::class
            ));
    }
}


