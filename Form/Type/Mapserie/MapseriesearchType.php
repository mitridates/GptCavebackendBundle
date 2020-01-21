<?php
namespace App\GptCavebackendBundle\Form\Type\Mapserie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\GptCavebackendBundle\EventListener\Form\AddOrganisationFieldSubscriber;
use App\Cave\LibBundle\Format\Arraypath;

class MapseriesearchType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $factory = $builder->getFormFactory();

        $organisationSubscriber = new AddOrganisationFieldSubscriber($factory, array(
            'name'=>'publisher',
            'getMethod'=>'getPublisher',
            'options'=>array(
                'attr'=>array(
                    'code_id'=>374,
                    'class'=>'select2 organisation',
                )
            )
        ));
        $builder->addEventSubscriber($organisationSubscriber);

        foreach(explode(',', 'name:279,code:278,scale:373,abbreviation:372') as $el){
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
        $resolver->setDefaults(array('data_class' => 'App\GptCaveBundle\Entity\Mapserie'));
    }
}
