<?php
namespace App\GptCavebackendBundle\Form\Type\Cave;
use App\GptCaveBundle\Entity\Cavewidestmap;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\GptCavebackendBundle\EventListener\Form\AddMapFieldSubscriber;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Contracts\Translation\TranslatorInterface;

class EditWidestmapType extends AbstractType
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

        $mapSubscriber = new AddMapFieldSubscriber($factory, array(
            'options'=>array(
                'attr'=>array(
                    'code_id'=>411
                )
            )
        ));
        $builder->addEventSubscriber($mapSubscriber);

        $fields = '359:mapname;55:orgcode;264:sequencenumber;360:surveygradeorg;'
                . '361:surveygradevalue;362:scale;363:survissueamend;10001:position';
        
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
             * @var Cavewidestmap $entity
             */
            $entity = $event->getData();
            $form = $event->getForm();
            $mapIsNull=       $entity->getMap()== NULL ? NULL : false;
            $mapStrIsNull=    $this->checkMapStr($entity, $form);

            if( $mapIsNull=== NULL && $mapStrIsNull=== NULL){
                $form->addError(new FormError($this->translator->trans('form.cannot.be.empty',  [], 'caveerrors')));
                $form->addError(new FormError($this->translator->trans('form.use.selector.or.text',  [], 'caveerrors')));
            }
        });

    }


    /**
     *
     * @param Cavewidestmap $entity
     * @param FormInterface $form
     * @return bool|null
     */
    private function checkMapStr(Cavewidestmap $entity, FormInterface &$form){
        $fieldNames = ['mapname','orgcode', 'sequencenumber', 'surveygradeorg', 'surveygradevalue', 'scale', 'survissueamend'];
        $is_empty= 0;

        foreach($fieldNames as $name){
            if( $entity->{'get'.ucfirst($name)}()!== NULL){
                $is_empty++;
            }
        }

        if($is_empty === 0) return NULL;

        if($is_empty >= 1 && $entity->getMapname()== NULL){
            $form->get('mapname')->addError(new FormError($this->translator->trans('form.empty.master.has.filled.children',  [], 'caveerrors')));
            return false;
        }
        return true;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\GptCaveBundle\Entity\Cavewidestmap',
            'translator'=>TranslatorInterface::class
            ));
    }
}


