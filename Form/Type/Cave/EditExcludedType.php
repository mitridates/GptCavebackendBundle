<?php
namespace App\GptCavebackendBundle\Form\Type\Cave;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use Symfony\Contracts\Translation\TranslatorInterface;

class EditExcludedType extends AbstractType
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
               'excluded', EntityType::class, array(
                            'class'=>'GptCaveBundle:Fielddefinition',
                            'attr'=>array('code_id'=>75),
                            'required' => true,
                            'choice_label' => function($fd, $key, $index) {
                                                    /** @var Fielddefinition $fd */
                                                    return $fd->getCode() . ' ' . $fd->getName();
                                                },
                            'choice_value'=>'code',
                            'query_builder' => function(EntityRepository $e){
                                                return $e->createQueryBuilder('f')
                                                ->select('f')
                                                ->where('f.entity = :entity')        
                                                ->setParameter('entity',  'CA');
                                    },
                            )
                );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\GptCaveBundle\Entity\Caveexcluded',
            'translator'=>TranslatorInterface::class
            ));
    }
}


