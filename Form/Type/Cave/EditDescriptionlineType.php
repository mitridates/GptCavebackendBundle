<?php
namespace App\GptCavebackendBundle\Form\Type\Cave;
use App\GptCaveBundle\Entity\Cavedescriptionline;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Contracts\Translation\TranslatorInterface;

class EditDescriptionlineType extends AbstractType
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
        $builder->add('descriptionline', null,['attr'=>['code_id'=>525]]);

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event){
            /**
             * @var Cavedescriptionline $entity
             */
            $entity = $event->getData();
            $form = $event->getForm();
            if( $entity->getDescriptionline() === NULL
            ){
                $form->get('descriptionline')->addError(new FormError($this->translator->trans('form.cannot.be.empty',  [], 'caveerrors')));
            }
        });

    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\GptCaveBundle\Entity\Cavedescriptionline',
            'parameters'=>array(),
            'translator'=>TranslatorInterface::class
            ));
    }
}


