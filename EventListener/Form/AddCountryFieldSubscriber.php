<?php
namespace App\GptCavebackendBundle\EventListener\Form;
use App\GptGeonamesBundle\Entity\Country;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormInterface;

/**
 * Country Events
 * @package App\GptCavebackendBundle\EventListener\Form
 *
 * @example
 *
 * $country = array(
 *      'name'=>'countryorg',           //property/field name
 *      'getMethod'=>'getCountryorg',   //Get method for property if mapped
 *      'preferred'=>['ES'],             //prefered choice. ISO 2 or null
 *      'options'=>array()              //ChoiceType Field options
 *  );
 *
 * $builder->addEventSubscriber(new AddAdmin1FieldSubscriber($factory, $country) )
 *
 */
class AddCountryFieldSubscriber implements EventSubscriberInterface
{
    /**
     * @var FormFactoryInterface
     */
    private $factory;
    /**
     * @var array
     */
    private $country;


    public function __construct(FormFactoryInterface $factory, array $country=[])
    {
        $this->factory =        $factory;
        $this->country =        array_merge(
            [
                'name'=>'country',//field name/property if mapped
                'getMethod'=>'getCountry',//method if mapped
                'options'=>array(
                    'placeholder'   => 'government.level.country',
                    'attr'=>array(
                    )
                )
            ],
            $country
        );
    }

    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::PRE_SUBMIT     => 'preBind'
        );
    }

    /**
     * @return array
     */
    public function getCountry(): array
    {
        return $this->country;
    }

    /**
     * @param FormInterface $form
     * @param Country|null $country
     */
    private function addCountryForm($form, $country)
    {
        $preferred = $this->country['preferred'] ?? null;
        $options = array_merge(
            array(
                'auto_initialize' => false,
                'attr' => array(),
                'class' => 'GptGeonamesBundle:Country',
                'required' => false,
                'translation_domain' => 'cavemessages',
                'placeholder'   => 'select.government.level.country',//cavemessages.yml
                'label'=>'government.level.country',//cavemessages.yml
                'choice_label' => 'name',
                'choice_value'=>'countryid',
                'preferred_choices'=>function ($choice, $key) use ($preferred) {
                           return in_array($choice->getCountryid(), is_array($preferred)? $preferred : (array)$preferred );
                         }
                ), $this->country['options']
        );

       $form->add($this->factory->createNamed(
           $this->country['name'] ,
           EntityType::class ,
           $country,
           $options
        ));
    }

    /**
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $data = $event->getData();
        if (null === $data) return;

        $form = $event->getForm();

        if($this->country['getMethod'] ?? false){
            $this->addCountryForm($form, NULL);
            return;
        }

        $countryorNull = $data->{$this->country['getMethod']}() ?? null;
        $this->addCountryForm($form, $countryorNull);
    }

    /**
     * @param FormEvent $event
     */
    public function preBind(FormEvent $event)
    {
        $data = $event->getData();
        if (null === $data) return;
        $form = $event->getForm();

        $this->addCountryForm($form, $data[$this->country['name']] ?? null);
    }
}