<?php
namespace App\GptCavebackendBundle\Form\Type\Cave;
use App\GptCaveBundle\Entity\Cavereference;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\GptCavebackendBundle\EventListener\Form\AddDynamicArticleFieldSubscriber;
use App\Cave\LibBundle\Format\Arraypath;
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
        $bundle = $defaults['parameters'];//Arraypath parametros del bundle
        $factory = $builder->getFormFactory();
        $this->translator = $defaults['translator'];

        $article = new Arraypath(array(//parametros
                        'name'=>'article',//propiedad
                        'getMethod'=>'getArticle',//metodo
                        'options'=>array(//opciones del campo html
                                'placeholder'   => 'select.article',//para la traduccion del texto por defecto
                                'attr'=>array(//attributos del html
                                    'code_id'=>270,
                                    'class'=>'select2 article',//css
                                    'style'=>'width:100%',//css
                                    )
                                )
                        )
                );
        $parameters = new Arraypath([
                            'article'=>$article->toArray(),
                            'bundle'=> $bundle->toArray()
                        ]);
        $articleSubscriber = new AddArticleFieldSubscriber(
                                        $factory,array('parameters'=>$parameters));
        $builder->addEventSubscriber($articleSubscriber); 
        
        $fields = '268:subjects;269:range;71:surnames;266:year;267:yearsuffix;355:articlename;356:publication;357:volume;358:issue;10001:position';

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

             $articleIsNull=     $this->checkArticle($entity, $form);

             if( $articleIsNull=== NULL){
                 $form->addError(new FormError($this->translator->trans('form.cannot.be.empty',  [], 'caveerrors')));
             }
         });

    }

    /**
     * @param Cavereference $entity
     * @param FormInterface $form
     * @return bool|null
     */
    private function checkArticle(Cavereference $entity, FormInterface &$form){
        $fieldNames = ['articlename', 'publication', 'volume', 'issue', 'surnames', 'year', 'yearsuffix'];
        $is_empty= 0;

        foreach($fieldNames as $name){
            if( $entity->{'get'.ucfirst($name)}()!== NULL){
                $is_empty++;
            }
        }
        $articleIsNull = $entity->getArticle() === NULL;

        if($articleIsNull && $is_empty===0) {//vacío

            return NULL;

        }elseif(!$articleIsNull && $is_empty===0) {//valido

            return true;


        }elseif(!$articleIsNull && $is_empty!==0){

            $form->addError(new FormError($this->translator->trans('form.use.selector.or.text',  [], 'caveerrors')));

        }elseif($articleIsNull && $is_empty!==0){//hay string

            if($entity->getArticlename()===NULL){
                $form->get('articlename')->addError(new FormError($this->translator->trans('form.empty.master.has.filled.children',  [], 'caveerrors')));
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
            'data_class' => 'App\GptCaveBundle\Entity\Cavereference',
            'parameters'=>array(),
            'translator'=>TranslatorInterface::class
            ));
    }
}


