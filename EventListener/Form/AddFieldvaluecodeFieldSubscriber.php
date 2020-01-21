<?php
namespace App\GptCavebackendBundle\EventListener\Form;

use App\GptCaveBundle\Entity\Fieldvaluecode;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Set form field on page load or submit if selected value exists.
 * @package App\GptCavebackendBundle\EventListener\Form
 *
 * @example
 * $fieldvaluecode = array(
 *      'name'=>'fieldvaluecode',//property/field name
 *      'getMethod'=>'getFieldvaluecode',//Get method for property if mapped
 *      'options'=>array() //ChoiceType Field options
 *  );
 *
 *
 * $builder->addEventSubscriber(new AddFieldvaluecodeFieldSubscriber( $factory, $fieldvaluecode) );
 */
class AddFieldvaluecodeFieldSubscriber implements EventSubscriberInterface
{
    /**
     * @var FormFactoryInterface
     */
    private $factory;
    /**
     * @var array
     */
    private $fieldvaluecode;

    public function __construct(FormFactoryInterface $factory, array $fieldvaluecode=[])
    {
        $this->factory = $factory;
        $this->fieldvaluecode= $fieldvaluecode;
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
     * @param string|Fieldvaluecode|null $fieldvaluecode
     */
    private function addFieldvaluecodeForm($form, $fieldvaluecode)
    {
        $options = array_merge(
            array(
                'auto_initialize' => false,
                'attr'          => array(
                ),
                'class'=> 'GptCaveBundle:Fieldvaluecode',
                'required' => false,
                'translation_domain' => 'cavemessages',
                'placeholder'   => 'select.fieldvaluecode',
                'label'=>'Fieldvaluecode',
                'choice_label' => 'value',
                'choice_value'=>'id',
                'query_builder' => function (EntityRepository $repository) use ($fieldvaluecode) {
                    return $repository->createQueryBuilder('o')
                        ->where('o.id = :fieldvaluecode')
                        ->setParameter('fieldvaluecode', $fieldvaluecode);
                }
            ),$this->fieldvaluecode['options']
        );

        $form->add($this->factory->createNamed(
            $this->fieldvaluecode['name'],
            EntityType::class,
            $fieldvaluecode,
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

        $this->addFieldvaluecodeForm($event->getForm(), $data->{$this->fieldvaluecode['getMethod']}() ?? null);
    }

    /**
     * @param FormEvent $event
     */
    public function preSubmit(FormEvent $event)
    {
        $data = $event->getData();
        if (null === $data) return;

        $this->addFieldvaluecodeForm($event->getForm(), $data[$this->fieldvaluecode['name']] ?? null);
    }
}