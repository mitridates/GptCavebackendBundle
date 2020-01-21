<?php
namespace App\GptCavebackendBundle\Form\Type\Article;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\GptCavebackendBundle\EventListener\Form\AddCountryFieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddAdmin1FieldSubscriber;

class ArticlesearchType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $factory = $builder->getFormFactory();

        $countrySubscriber = new AddCountryFieldSubscriber($factory, array(
            'options'=>array(
                'attr'=>array(
                    'code_id'=>319,
                    'class'=>'countryid',
                    'onChange'=>"$('.admin1id').html('')"
                )
            )
        ));
        $builder->addEventSubscriber($countrySubscriber);

        $admin1 = array(
            'options'=>array(
                'attr'=>array(
                    'code_id'=>321,
                    'class'=>'select2 admin1id',//css
                )
            )
        );
        $admin1Subscriber = new AddAdmin1FieldSubscriber($factory,$countrySubscriber->getCountry(), $admin1);
        $builder->addEventSubscriber($admin1Subscriber);

        foreach(explode(',', 'name:335,author:10331,publicationyear:308') as $el){
                $field = explode(':', $el);
                $builder->add($field[0], NULL, array(
                    'required'=>false,
                     'attr'=>['code_id'=>$field[1]]
                        ));
        }

    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\GptCaveBundle\Entity\Article',
        ));
    }
}
