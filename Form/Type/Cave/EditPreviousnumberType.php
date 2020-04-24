<?php
namespace App\GptCavebackendBundle\Form\Type\Cave;
use App\GptCavebackendBundle\EventListener\Form\AddAreaFieldSubscriber;
use App\GptCaveBundle\Entity\Cavepreviousnumber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\GptCavebackendBundle\EventListener\Form\AddCountryFieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddAdmin1FieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddAdmin2FieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddAdmin3FieldSubscriber;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Contracts\Translation\TranslatorInterface;

class EditPreviousnumberType extends AbstractType
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
                    'code_id'=>231
                )
            )
        ));
        $builder->addEventSubscriber($countrySubscriber);

        $admin1 = array(
            'options'=>array(
                'attr'=>array(
                    'code_id'=>232,
                )
            )
        );
        $admin1Subscriber = new AddAdmin1FieldSubscriber($factory,$countrySubscriber->getCountry(), $admin1);
        $builder->addEventSubscriber($admin1Subscriber);


        $area = array(
            'options'=>array(
                'attr'=>array(
                    'code_id'=>233,
                )
            )
        );
        $areaSubscriber = new AddAreaFieldSubscriber($factory,$countrySubscriber->getCountry(), null, $area);
        $builder->addEventSubscriber($areaSubscriber);

        $builder->add('position', NULL, ['attr'=>['code_id'=>10001]]);
        $builder->add('serial', NULL, ['attr'=>['code_id'=>234]]);

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event){
            /**
             * @var Cavepreviousnumber $entity
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
            'data_class' => 'App\GptCaveBundle\Entity\Cavepreviousnumber',
            'translator'=>TranslatorInterface::class
            ));
    }
}


