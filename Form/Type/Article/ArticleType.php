<?php
namespace App\GptCavebackendBundle\Form\Type\Article;
use App\GptCavebackendBundle\EventListener\Form\AddCountryFieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddAdmin1FieldSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $defaults
     */
    public function buildForm(FormBuilderInterface $builder, array $defaults)
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

        /**
         * Realmente no sé que hacer con estos
         * surnameoverflow:329,publicationtype:322
         */
        foreach(explode(',', 'name:335,publicationyear:308,publicationyearsuffix:309,volumenumber:312'
                . ',issuenumber:313,bookpublisherandcity:314,pagerange:315,bibliographypresent:316'
                . ',quantityofmaps:317,quantityofplates:318,isbn:608,issn:320'
                . ',abstractyear:323,abstractcategory:324,abstractreference:325,bbsyear:326,bbssequencenumber:327'
                . ',ssccode:328'
        ) as $el){

            $field = explode(':', $el);
            $arr = ['attr'=>['code_id'=>$field[1]]];

            if($field[1]=='307'){
                $arr['attr']['disabled']='disabled';
            }
            $builder->add($field[0], NULL, $arr);
        }

         $builder->add('author',null, array('required'=>false,'attr'=>array('code_id'=>10331)));
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\GptCaveBundle\Entity\Article'
        ));
    }
}


