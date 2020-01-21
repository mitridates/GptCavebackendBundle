<?php
namespace App\GptCavebackendBundle\Form\Type\Cave;
use App\GptCaveBundle\Entity\Caveenvironment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Contracts\Translation\TranslatorInterface;

class EditEnvironmentType extends AbstractType
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

        foreach(explode(';','2:submersion;3:flowpresence;4:flowdirection;5:internalflow;6:internalwater;17:moisturelevel') as $el){
            $field = explode(':', $el);
            $builder->add($field[1], EntityType::class, array(
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

        $fields = '13:minimumtemperature;14:maximumtemperature;15:minimumhumidity;16:maximumhumidity';

        foreach(explode(';', $fields) as $el){
            if(!strpos($el,':')) {var_dump ($el);exit;}
            $field = explode(':', $el);

            $arr = ['attr'=>['code_id'=>$field[0]]];
            $builder->add($field[1], NULL, $arr);
        }

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event){
            /**
             * @var Caveenvironment $entity
             */
            $entity = $event->getData();
            $form = $event->getForm();
            $fieldNames = ['submersion','flowpresence', 'flowdirection','internalflow', 'internalwater', 'moisturelevel',
                'minimumtemperature','maximumtemperature', 'minimumhumidity','maximumhumidity'];
            $is_empty= true;
            foreach($fieldNames as $name){
                if( $entity->{'get'.ucfirst($name)}()!== NULL){
                    $is_empty= false;
                    break;
                }
            }

            if( $is_empty){
                $form->addError(new FormError($this->translator->trans('form.cannot.be.empty',  [], 'caveerrors')));
            }
        });
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\GptCaveBundle\Entity\Caveenvironment',
            'parameters'=>array(),
            'translator'=>TranslatorInterface::class
           )
        );
    }
}


