<?php
namespace App\GptCavebackendBundle\EventListener\Form;

use App\GptCaveBundle\Entity\Cave;
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
 * $cave = array(
 *      'name'=>'cave',//property/field name
 *      'getMethod'=>'getCave',//Get method for property if mapped
 *      'options'=>array() //ChoiceType Field options
 *  );
 *
 *
 * $builder->addEventSubscriber(new AddCaveFieldSubscriber( $factory, $cave) );
 */
class AddCaveFieldSubscriber implements EventSubscriberInterface
{
    /**
     * @var FormFactoryInterface
     */
    private $factory;
    /**
     * @var array
     */
    private $cave;

    public function __construct(FormFactoryInterface $factory, array $cave=[])
    {
        $this->factory = $factory;
        $this->cave= $cave;
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
     * @param string|Cave|null $cave
     */
    private function addCaveForm($form, $cave)
    {
                $options = array_merge(
                            array(
                                'auto_initialize' => false,
                                'attr'          => array(
                                ),
                                'class'=> 'GptCaveBundle:Cave',
                                'required' => false,
                                'translation_domain' => 'cavemessages',
                                'placeholder'   => 'select.cave',
                                'label'=>'Cave',
                                'choice_label' => 'name',
                                'choice_value'=>'caveid',
                                'query_builder' => function (EntityRepository $repository) use ($cave) {
                                        return $repository->createQueryBuilder('o')
                                                ->where('o.caveid = :cave')
                                                ->setParameter('cave', $cave);
                                    }
                            ),$this->cave['options']
                        );

       $form->add($this->factory->createNamed(
           $this->cave['name'],
           EntityType::class,
           $cave,
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

        $this->addArticleForm($event->getForm(), $data->{$this->cave['getMethod']}() ?? null);
    }

    /**
     * @param FormEvent $event
     */
    public function preSubmit(FormEvent $event)
    {
        $data = $event->getData();
        if (null === $data) return;

        $this->addCaveForm($event->getForm(), $data[$this->cave['name']] ?? null);
    }
}