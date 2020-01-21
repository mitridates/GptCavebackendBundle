<?php
namespace App\GptCavebackendBundle\Form\Type\Cave;
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

        $admin2 = array('name'=>'admin2',
            'attr'=>array(
                'code_id'=>233,
                'style'=>'width:100%'//css
            )
        );
        $admin2Subscriber = new AddAdmin2FieldSubscriber(
            $factory,
            $countrySubscriber->getCountry(),
            $admin1Subscriber->getAdmin1(),
            $admin2
        );
        $builder->addEventSubscriber($admin2Subscriber);

        $admin3Subscriber= new AddAdmin3FieldSubscriber($factory, $admin2Subscriber->getAdmin2(),array(
            'name'=>'admin3',
            'options'=>array(
                'attr'=>array(//attributos del html
                    'style'=>'width:100%'//css
                )
            )
        ));
        $builder->addEventSubscriber($admin3Subscriber);


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


