<?php
namespace App\GptCavebackendBundle\Form\Type\Map;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\GptCavebackendBundle\EventListener\Form\AddDynamicArticleFieldSubscriber;
use App\Cave\LibBundle\Format\Arraypath;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Contracts\Translation\TranslatorInterface;

class EditCitationType extends AbstractType
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
        $this->translator = $defaults['translator'];
        $factory = $builder->getFormFactory();
        $citationarticleid = new Arraypath(array(//parametros
                        'name'=>'citationarticleid',//propiedad
                        'getMethod'=>'getCitationarticleid',//metodo
                        'options'=>array(//opciones del campo html
                                'required'=>true,
                                'placeholder'   => 'select.article',//para la traduccion del texto por defecto
                                'attr'=>array(//attributos del html
                                    'code_id'=>335,//article name
                                    'class'=>'select2 article',//css
                                    'style'=>'width:100%',//css
                                    )
                                )
                        )
                );
        $parameters = new Arraypath([
                            'article'=>$citationarticleid->toArray(),
                            'bundle'=> $bundle->toArray()
                        ]);

        $citationarticleidSubscriber = new AddArticleFieldSubscriber(
                                        $factory,array('parameters'=>$parameters));

         $builder->addEventSubscriber($citationarticleidSubscriber);        
        
        
         
        $fields = '599:citationpagenumber,600:citationcomment,10001:position';

        foreach(explode(',', $fields) as $el){

            $field = explode(':', $el);
            $arr = ['attr'=>['code_id'=>$field[0]]];
            $builder->add($field[1], NULL, $arr);
        }

         $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event){
            $data = $event->getData();
            if($data['citationarticleid']==''){
                $form = $event->getForm();
                $form->get('citationarticleid')->addError(new \Symfony\Component\Form\FormError($this->translator->trans('form.cannot.be.empty',  [], 'caveerrors')));
            }
         });

        
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\GptCaveBundle\Entity\Mapcitation',
            'parameters'=>array(),
            'translator'=>Translator::class
            ));
    }
}


