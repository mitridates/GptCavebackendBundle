<?php
namespace App\GptCavebackendBundle\Form\Type\Cave;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\GptCavebackendBundle\EventListener\Form\AddCountryFieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddAdmin1FieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddAdmin2FieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddAdmin3FieldSubscriber;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use Symfony\Contracts\Translation\TranslatorInterface;

class EditCavepartialMeasureType extends AbstractType
{

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $defaults)
    {
        $factory = $builder->getFormFactory();
        $this->translator = $defaults['translator'];


        $fields = '56:length;58:lengthaccuracy;67:extentlength;68:extentwidth;511:verticalextent;62:verticalaccuracy;'
            .'59:extentbelowentrance;60:extentaboveentrance;'
            .'63:lengthlargestchamber;64:widthlargestchamber;65:heighlargestchamber';


        foreach(explode(';', $fields) as $el){
            if(!strpos($el,':')) {var_dump ($el);exit;}
            $field = explode(':', $el);
            $arr = ['attr'=>['code_id'=>$field[0]]];
            if($field[1]=='name'){ $arr['required']=true; }
            $builder->add($field[1], NULL, $arr);
        }

        foreach(explode(';','57:lengthmethod;61:verticalmethod;297:lengthcategory;527:depthcategory') as $el){
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
                            ->setParameter('field', $field[0]);
                    },
                )
            );
        }
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\GptCaveBundle\Entity\Cave',
            'translator'=>TranslatorInterface::class
        ));
    }
}