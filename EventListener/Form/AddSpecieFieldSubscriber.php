<?php
namespace App\GptCavebackendBundle\EventListener\Form;

use App\GptCaveBundle\Entity\Specie;
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

class AddSpecieFieldSubscriber implements EventSubscriberInterface
{
    /**
     * @var FormFactoryInterface
     */
    private $factory;
    /**
     * @var array
     */
    private $specie;

    public function __construct(FormFactoryInterface $factory, array $specie=[])
    {
        $this->factory = $factory;
        $this->specie= array_merge(
            array(
                'name'=>'specie',//field name/property if mapped
                'getMethod'=>'getSpecie',//method if mapped
                'options'=>array(
                    'placeholder' => 'select.specie',
                    'attr'=>array(
                    )
                )
            ),
            $specie);
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
     * @param string|Specie|null $specie
     */
    private function addSpecieForm($form, $specie)
    {
        $options = array_merge(
            array(
                'auto_initialize' => false,
                'attr'          => array(),
                'translation_domain' => 'cavemessages',
                'placeholder'   => 'select.specie',
                'class'=> 'GptCaveBundle:Specie',
                'required' => false,
                'label'=>'Specie',
                'choice_label' => 'name',
                'choice_value'=>'specieid',
                'query_builder' => function (EntityRepository $repository) use ($specie) {
                    return $repository->createQueryBuilder('m')
                           ->where('m.specieid = :specie')
                          ->setParameter('specie', $specie);
                    }

            ),$this->specie['options'] ?? []
        );

        $form->add($this->factory->createNamed(
            $this->specie['name'],
            EntityType::class,
            $specie,
            $options
        ));
    }

    /**
     * @return array
     */
    public function getSpecie(): array
    {
        return $this->specie;
    }

    /**
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $data = $event->getData();
        if (null === $data) return;

        $this->addSpecieForm($event->getForm(), $data->{$this->specie['getMethod']}() ?? null);
    }

    /**
     * @param FormEvent $event
     */
    public function preSubmit(FormEvent $event)
    {
        $data = $event->getData();
        if (null === $data) return;

        $this->addSpecieForm($event->getForm(), $data[$this->specie['name']]?? null);
    }
}