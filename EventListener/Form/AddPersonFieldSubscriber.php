<?php
namespace App\GptCavebackendBundle\EventListener\Form;
use App\GptCaveBundle\Entity\Person;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Set form field on page load or submit if selected value exists.
 * @package App\GptCavebackendBundle\EventListener\Form
 */
class AddPersonFieldSubscriber implements EventSubscriberInterface
{
    /**
     * @var FormFactoryInterface
     */
    private $factory;
    /**
     * @var array
     */
    private $person;

    public function __construct(FormFactoryInterface $factory, array $person=[])
    {
        $this->factory = $factory;
        $this->person= array_merge(
            array(
                'name'=>'person',//field name/property if mapped
                'getMethod'=>'getPerson',//method if mapped
                'options'=>array(
                    'placeholder'   => 'select.person',
                    'attr'=>array()
                )
            ),
            $person
        );
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::PRE_SUBMIT   => 'preSubmit',
        );
    }

    /**
     * @param FormInterface $form
     * @param string|Person|null $person
     */
    private function addPersonForm($form, $person)
    {
        $options = array_merge(
            array(
                'auto_initialize' => false,
                'attr'          => array(),
                'translation_domain' => 'cavemessages',
                'placeholder'   => 'select.person',
                'class'=> 'GptCaveBundle:Person',
                'required' => false,
                'label'=>'Person',
                'choice_label' => 'name',
                'choice_value'=>'personid',
                'query_builder' => function (EntityRepository $repository) use ($person) {
                    return $repository->createQueryBuilder('m')
                           ->where('m.personid = :person')
                          ->setParameter('person', $person);
                    }

            ),$this->person['options'] ?? []
        );

        $form->add($this->factory->createNamed(
            $this->person['name'],
            EntityType::class,
            $person,
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

        $this->addPersonForm($event->getForm(), $data->{$this->person['getMethod']}() ?? null);
    }

    /**
     * @param FormEvent $event
     */
    public function preSubmit(FormEvent $event)
    {
        $data = $event->getData();
        if (null === $data) return;

        $this->addPersonForm($event->getForm(), $data[$this->person['name']] ?? null);
    }
}