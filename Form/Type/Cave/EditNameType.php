<?php
namespace App\GptCavebackendBundle\Form\Type\Cave;
use App\GptCaveBundle\Entity\Cavename;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Contracts\Translation\TranslatorInterface;

class EditNameType extends AbstractType
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
        $builder->add('name', NULL,  ['attr'=>['code_id'=>69]]);
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event){
            /**
             * @var Cavename $entity
             */
            $entity = $event->getData();
            $form = $event->getForm();
            if( $entity->getName() === NULL
            ){
                $form->get('name')->addError(new FormError($this->translator->trans('form.cannot.be.empty',  [], 'caveerrors')));
            }
        });
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\GptCaveBundle\Entity\Cavename',
            'parameters'=>array(),
            'translator'=>TranslatorInterface::class
            ));
    }
}


