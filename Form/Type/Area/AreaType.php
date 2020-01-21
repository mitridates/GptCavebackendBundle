<?php
namespace App\GptCavebackendBundle\Form\Type\Area;
use App\GptCavebackendBundle\EventListener\Form\AddCountryFieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddAdmin1FieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddMapFieldSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AreaType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $defaults)
    {
        $factory = $builder->getFormFactory();

        $countrySubscriber = new AddCountryFieldSubscriber($factory, array(
            'options'=>array(
                'attr'=>array(
                    'code_id'=>224
                )
            )
        ));
        $builder->addEventSubscriber($countrySubscriber);

        $admin1Subscriber = new AddAdmin1FieldSubscriber(
            $factory, $countrySubscriber->getCountry(), [
                'options'=>array(
                    'attr'=>array('code_id'=>225)
                )]
        );
        $builder->addEventSubscriber($admin1Subscriber);

//        $builder->addEventSubscriber(new AddMapFieldSubscriber($factory));

        foreach(explode(',', 'code:81,name:80'//,designation:223'
                . ',comment:621,latlng:10019,mapsheet:618'
        ) as $el){

            $field = explode(':', $el);
            $arr = ['attr'=>['code_id'=>$field[1]]];

            if($field[1]=='307'){
                $arr['attr']['disabled']='disabled';
            }
            $builder->add($field[0], NULL, $arr);
        }

    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array('data_class' => 'App\GptCaveBundle\Entity\Area'));
    }
}


