<?php
namespace App\GptCavebackendBundle\Form\Type\Setup;
use App\GptCavebackendBundle\Exception\CustomCaveException;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\GptCavebackendBundle\EventListener\Form\AddCountryFieldSubscriber;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use App\GptCaveBundle\Entity\Organisation;


/**
 * Generate first organisation
 */
class OrganisationsetupType extends AbstractType
{
    /**
     * @var Organisation
     */
    private $organisationDbm;
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $factory = $builder->getFormFactory();

        $countrySubscriber = new AddCountryFieldSubscriber($factory, array(
            'name'=>'countryorg',
            'getMethod'=>'getCountryorg',
            'options'=>array(
                'required' => true,
                'attr'=>array(
                    'code_id'=>376
                )
            )
        ));
        $builder->addEventSubscriber($countrySubscriber);

        $builder->add('name', NULL, array('attr'=>array('code_id'=>391),'required' => true) )
            ->add('code', NULL, array(
                'attr'=>array(
                    'maxlength'=> 3,
                    'pattern'=>'[A-Z]{3}',
                    'title'=>'ABC',
                    'code_id'=>178
                ),'required' => true) )
            ->addEventListener(FormEvents::POST_SUBMIT, array($this, 'onPostSubmit'));

    }

    /**
     * Custom ID example ES-GPT-00001
     *  - Only validate numeric suffix 00001
     *  - throw error in controller if exists on flush
     * @param FormEvent $event
     * @throws CustomCaveException
     * @throws \ReflectionException
     */
    public function onPostSubmit(FormEvent $event)
    {
        $entity = $event->getData();
        $form = $event->getForm();
        $country= $form["countryorg"]->getData();
        $code = $form["code"]->getData();
        $organisationid =   $country->getCountryid().strtoupper($code).'00001';
        
        $reflection = new \ReflectionClass($entity);
        $property = $reflection->getProperty('organisationid');
        $property->setAccessible(true);
        $property->setValue($entity, $organisationid);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\GptCaveBundle\Entity\Organisation',
            'parameters'=>array()
        ));
    }
}


