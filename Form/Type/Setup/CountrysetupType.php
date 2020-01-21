<?php
namespace App\GptCavebackendBundle\Form\Type\Setup;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CountrysetupType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $defaults
     */
    public function buildForm(FormBuilderInterface $builder, array $defaults)
    {
        $builder->add('country', CountryType::class, [
            'mapped'=>false
            ]
        );
        $builder->add('deep', ChoiceType::class, [
                'mapped'=>false,
                'preferred_choices'=>[4],
                'choices'  => [
                    'Country' => 1,
                    'Admin1' => 2,
                    'Admin2' => 3,
                    'Admin3' => 4
                ],
            ]
        );
        $builder->add('submit', SubmitType::class, [
            'label' => 'add',
            'translation_domain' => 'cavemessages',
            'attr' => ['class' => 'btn btn-primary'],
        ]);


    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array('data_class' => null));
    }
}


