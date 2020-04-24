<?php
namespace App\GptCavebackendBundle\Form\Type\Cave;
use App\GptCavebackendBundle\EventListener\Form\AddArticleFieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddSpecieFieldSubscriber;
use App\GptCaveBundle\Entity\Cavespecie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Contracts\Translation\TranslatorInterface;

class EditSpecieType extends AbstractType
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

        $specieSubscriber = new AddSpecieFieldSubscriber($factory, array(
            'options'=>array(
                'attr'=>array(
                    'code_id'=>364
                )
            )
        ));
        $builder->addEventSubscriber($specieSubscriber);

        $articleSubscriber = new AddArticleFieldSubscriber($factory, array(
            'options'=>array(
                'attr'=>array(
                    'code_id'=>263
                )
            )
        ));
        $builder->addEventSubscriber($articleSubscriber);


        foreach(explode(';','510:specieconfidence;509:genusconfidence;40:speciesignificance') as $el){
            $field = explode(':', $el);
            $builder->add(
                $field[1], EntityType::class, array(
                    'class'=>'GptCaveBundle:Fieldvaluecode',
                    'attr'=>array('code_id'=>$field[0]),
                    'required' => false,
                    'choice_label' => 'value',
                    'choice_value'=>'id',
                    'query_builder' => function(EntityRepository $e) use ($field){
                        return $e->createQueryBuilder('f')
                            ->select('f')
                            ->where('f.field = :field')
                            ->orderBy('f.value', 'ASC')
                            ->setParameter('field', in_array($field[0], [510,509])? 507: $field[0]);
                    },
                )
            );
        }

        $builder->add('position', NULL, ['attr'=>['code_id'=>10001]]);
                    
                                    
        $fields = '38:name;37:genus;39:refsurname;260:refyear;261:refyearsuffix;262:refcomment';
        
        foreach(explode(';', $fields) as $el){
            if(!strpos($el,':')) {var_dump ($el);exit;}
            $field = explode(':', $el);
            $arr = [
                    'attr'=>['code_id'=>$field[0]]
                   ];           
            $builder->add($field[1], NULL, $arr);
        }

         $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event){
            /**
             * @var Cavespecie $entity
             */
             $entity = $event->getData();
             $form = $event->getForm();

             $specieIsNull=     $this->checkSpecie($entity, $form);
             $articleIsNull=    $this->checkArticle($entity, $form);
             $confidenceIsNull= $this->checkConfidence($entity, $form);

             if( $specieIsNull=== NULL
                 && $articleIsNull=== NULL
                 && $confidenceIsNull=== NULL
             ){
                 $form->addError(new FormError($this->translator->trans('form.cannot.be.empty',  [], 'caveerrors')));
             }
         });
         
    }

    /**
     * @param Cavespecie $entity
     * @param FormInterface $form
     * @return bool|null
     */
    private function checkConfidence(Cavespecie $entity, FormInterface &$form){
        $is_valid = true;
        if($entity->getGenusconfidence()=== NULL || $entity->getSpecieconfidence()=== NULL){
            if($entity->getGenusconfidence()=== NULL){
                $form->get('genusconfidence')->addError(new FormError($this->translator->trans('form.must.complete.this.field',  [], 'caveerrors')));
                $is_valid= false;
            }
            if($entity->getSpecieconfidence()=== NULL){
                $form->get('specieconfidence')->addError(new FormError($this->translator->trans('form.must.complete.this.field',  [], 'caveerrors')));
                $is_valid= false;
            }
        }
        return $is_valid;
    }

    /**
     * @param Cavespecie $entity
     * @param FormInterface $form
     * @return bool|null
     */
    private function checkSpecie(Cavespecie $entity, FormInterface &$form){
        $fieldNames = ['name', 'genus', 'refcomment', 'speciesignificance', 'refcomment'];
        $is_empty= 0;

        foreach($fieldNames as $name){
            if( $entity->{'get'.ucfirst($name)}()!== NULL){
                $is_empty++;
            }
        }

        $specieIsNull = $entity->getSpecie() === NULL;

        if($specieIsNull  && $is_empty===0){//vacío

            $form->get('specie')->addError(new FormError($this->translator->trans('form.use.selector.or.text',  [], 'caveerrors')));
            return NULL;

        }elseif(!$specieIsNull  && $is_empty===0){//hay id

            return true;

        }elseif($specieIsNull  && $is_empty!==0){//hay string

            if($entity->getName()==NULL){

                $form->get('name')->addError(new FormError($this->translator->trans('form.empty.master.has.filled.children',  [], 'caveerrors')));

            }else{

                return true;

            }
        }
        return false;
    }

    /**
     * @param Cavespecie $entity
     * @param FormInterface $form
     * @return bool|null
     */
    private function checkArticle(Cavespecie $entity, FormInterface &$form){
        $fieldNames = ['refsurname', 'refyear', 'refyearsuffix'];
        $is_empty= 0;
        
        foreach($fieldNames as $name){
            if( $entity->{'get'.ucfirst($name)}()!== NULL){
                $is_empty++;
            }
        }

        if($entity->getArticle() == NULL && $is_empty==0){//vacío
            return NULL;
        }elseif($entity->getArticle() !== NULL && $is_empty==0){//hay id
            return true;
        }elseif($entity->getArticle() == NULL && $is_empty!==0){//hay string

            if($entity->getRefsurname()==NULL){
                $form->get('refsurname')->addError(new FormError($this->translator->trans('form.empty.master.has.filled.children',  [], 'caveerrors')));
            }else{
                return true;
            }

        }
        return false;
    }  
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\GptCaveBundle\Entity\Cavespecie',
            'translator'=>TranslatorInterface::class
            ));
    }
}


