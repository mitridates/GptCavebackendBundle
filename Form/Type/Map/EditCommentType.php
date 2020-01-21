<?php
namespace App\GptCavebackendBundle\Form\Type\Map;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Contracts\Translation\TranslatorInterface;

class EditCommentType extends AbstractType
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
        $builder->add('comment', TextareaType::Class, ['attr'=>['code_id'=>579], 'required'=>true]);
        $builder->add('position', NULL , ['attr'=>['code_id'=>10001], 'required'=>false]);

        return;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\GptCaveBundle\Entity\Mapcomment',
            'parameters'=>array(),
            'translator'=>Translator::class
            ));
    }
}


