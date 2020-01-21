<?php
namespace App\GptCavebackendBundle\Form\Type\Map;
use App\GptCaveBundle\Entity\Mapfurthergc;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Contracts\Translation\TranslatorInterface;

class EditFurthergcType extends AbstractType
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
        $fields = '397:scopenlatitude,398:scopeslatitude,399:scopeelongitude,400:scopewlongitude,10001:position';//
        
        foreach(explode(',', $fields) as $el){
            if(!strpos($el,':')) {var_dump ($el);exit;}
            $field = explode(':', $el);
            
            $arr = ['attr'=>['code_id'=>$field[0]]];
            if($field[0]!='10001'){
                $arr['required']= true;
            }
            $builder->add($field[1], NULL, $arr);
        }

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event){
            /**
             * @var Mapfurthergc $entity
             */
            $entity = $event->getData();
            $form = $event->getForm();

            if(    empty($entity->getScopenlatitude())
                || empty($entity->getScopeslatitude())
                || empty($entity->getScopeelongitude())
                || empty($entity->getScopewlongitude())
            ){
                $form->addError(new \Symfony\Component\Form\FormError($this->translator->trans('form.must.complete.all.fields',  [], 'caveerrors')));
            }
        });
    }
     
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\GptCaveBundle\Entity\Mapfurthergc',
            'parameters'=>array(),
            'translator'=>Translator::class
            ));
    }
}


