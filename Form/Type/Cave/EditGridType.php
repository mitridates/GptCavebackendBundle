<?php
namespace App\GptCavebackendBundle\Form\Type\Cave;
use App\GptCavebackendBundle\EventListener\Form\AddMapFieldSubscriber;
use App\GptCaveBundle\Entity\Cavediscovery;
use App\GptCaveBundle\Entity\Cavegrid;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Contracts\Translation\TranslatorInterface;

class EditGridType extends AbstractType
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

        $personSubscriber = new AddMapFieldSubscriber($factory, array(
            'options'=>array(
                'attr'=>array(
                    'code_id'=>411
                )
            )
        ));
        $builder->addEventSubscriber($personSubscriber);

        $builder->add('refunits', EntityType::class, array(
                            'class'=>'GptCaveBundle:Fieldvaluecode',
                            'attr'=>array('code_id'=> 298, 'class'=>'select2', 'style'=>'width:100%'),
                            'required' => false,
                            'choice_label' => 'value',
                            'choice_value'=>'id',
                            'query_builder' => function(EntityRepository $e){
                                    return $e->createQueryBuilder('f')
                                    ->select('f')
                                    ->where('f.field = :field')
                                    ->orderBy('f.value', 'ASC')
                                    ->setParameter('field',   298);
                                    },
                            )
                );
        //TODO: Hacer un datepicker
        $builder->add('date', DateType::class,
                    [
                    'required' => false,
                    'attr'=>[
                        'code_id'=>632,
                        'class' => '',
                        'data-provide' => 'datepicker',
                        'data-date-format' => 'dd-mm-yyyy'
                        ],
                   'widget' => 'single_text',
                    'format' => 'dd-MM-yyyy'
                    ]
                );
        $fields =   '241:mapeasting;242:mapnorthing;244:grefprecision;302:grefaccuracy;240:mapedition;' .
                    '629:geodeticdatum;630:mapgrid;238:mapscale;239:mapnumber;414:mapname;640:comment;10001:position';

        foreach(explode(';', $fields) as $el){
            if(!strpos($el,':')) {var_dump ($el);exit;}
            $field = explode(':', $el);

            $arr = [
                'attr'=>[
                'code_id'=>$field[0]],
                'required'=> in_array($field[0], [241, 242] ) ? true : false
                ];
            $builder->add($field[1], NULL, $arr);
        }

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event){
            /**
             * @var Cavediscovery $entity
             */
            $entity = $event->getData();
            $form = $event->getForm();
            $mapIsNull=          $this->checkMap($entity, $form);
            $gridIsNull=    $this->checkGrid($entity, $form);

            if( $mapIsNull=== NULL && $gridIsNull=== NULL){

                $form->addError(new FormError($this->translator->trans('form.cannot.be.empty',  [], 'caveerrors')));
                $form->get('map')->addError(new FormError($this->translator->trans('form.use.selector.or.text',  [], 'caveerrors')));

            }elseif(($mapIsNull !== NULL && $gridIsNull === NULL) ||//si una parte no está vacia
                    ($mapIsNull === NULL && $gridIsNull!== NULL)){// la otra tampoco puede estarlo

                $form->addError(new FormError($this->translator->trans('form.cannot.be.empty',  [], 'caveerrors')));

            }
        });
    }

    /**
     *
     * @param Cavegrid $entity
     * @param FormInterface $form
     * @return bool|null
     */
    private function checkMap(Cavegrid $entity, FormInterface &$form){
        $fieldNames = ['map','geodeticdatum', 'mapgrid', 'mapscale', 'mapnumber', 'mapname'];
        $is_empty= 0;
        $is_valid=true;

        foreach($fieldNames as $name){
            if( $entity->{'get'.ucfirst($name)}()!== NULL){
                $is_empty++;
            }
        }

        if($is_empty === 0) return NULL;

        if($entity->getMap()!== NULL){
            if($is_empty == 1) return true;

            if($is_empty > 1){
                $form->get('map')->addError(new FormError($this->translator->trans('form.use.selector.or.text',  [], 'caveerrors')));
                $is_valid = false;
            }
        }else{
            if($entity->getMapname()=== NULL){
                $form->get('mapname')->addError(new FormError($this->translator->trans('form.field.has.dependencies',  [], 'caveerrors')));
                $is_valid = false;
            }
        }

        return $is_valid;
    }

    /**
     * El formulario debe contener al menos: mapeasting mapnorthing
     *
     * @param Cavegrid $entity
     * @param FormInterface $form
     * @return bool|null
     */
    private function checkGrid(Cavegrid $entity, FormInterface &$form){
        $fieldNames = ['mapeasting','mapnorthing', 'grefprecision', 'grefaccuracy', 'mapedition', 'refunits', 'date'];
        $is_empty= 0;
        $is_valid=true;

        foreach($fieldNames as $name){
            if( $entity->{'get'.ucfirst($name)}()!== NULL){
                $is_empty++;
            }
        }

        /*son campos obligatorios*/
        if($is_empty === 0){
            $form->get('mapeasting')->addError(new FormError($this->translator->trans('form.must.complete.this.field',  [], 'caveerrors')));
            $form->get('mapnorthing')->addError(new FormError($this->translator->trans('form.must.complete.this.field',  [], 'caveerrors')));
            return false;
        };

        if($entity->getMapeasting()=== NULL){
            $form->get('mapeasting')->addError(new FormError($this->translator->trans('form.empty.master.has.filled.children',  [], 'caveerrors')));
            $is_valid = false;
        }elseif($entity->getMapnorthing()=== NULL){
            $form->get('mapnorthing')->addError(new FormError($this->translator->trans('form.empty.master.has.filled.children',  [], 'caveerrors')));
            $is_valid = false;
        }

        return $is_valid;
    }
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\GptCaveBundle\Entity\Cavegrid',
            'translator'=>TranslatorInterface::class
            ));
    }
}


