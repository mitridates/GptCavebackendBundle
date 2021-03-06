<?php
namespace App\GptCavebackendBundle\Form\Type\Cave;
use App\GptCavebackendBundle\EventListener\Form\AddArticleFieldSubscriber;
use App\GptCaveBundle\Entity\Cavereference;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormError;
use Symfony\Contracts\Translation\TranslatorInterface;

class EditReferenceType extends AbstractType
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

        $this->translator = $defaults['translator'];

        $articleSubscriber = new AddArticleFieldSubscriber($factory, array(
            'options'=>array(
                'attr'=>array(
                    'code_id'=>270
                )
            )
        ));
        $builder->addEventSubscriber($articleSubscriber);

        $fields = '268:subjects;269:range;10001:position';

        foreach(explode(';', $fields) as $el){
            if(!strpos($el,':')) {var_dump ($el);exit;}
            $field = explode(':', $el);

            $arr = ['attr'=>['code_id'=>$field[0]]];
            $builder->add($field[1], NULL, $arr);
        }

         $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event){
             /**
              * @var Cavereference $entity
              */
             $entity = $event->getData();
             $form = $event->getForm();

             if($entity->getArticle() === NULL) {
                 $form->get('article')->addError(new FormError($this->translator->trans('form.must.complete.this.field', [], 'caveerrors')));
             }
         });

    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\GptCaveBundle\Entity\Cavereference',
            'translator'=>TranslatorInterface::class
            ));
    }
}


