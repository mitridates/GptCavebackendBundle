<?php
namespace App\GptCavebackendBundle\EventListener\Form;

use App\GptCaveBundle\Entity\Mapserie;
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

class AddMapserieFieldSubscriber implements EventSubscriberInterface
{
    /**
     * @var FormFactoryInterface
     */
    private $factory;
    /**
     * @var array
     */
    private $mapserie;

    public function __construct(FormFactoryInterface $factory, array $mapserie=[])
    {
        $this->factory = $factory;
        $this->mapserie= $mapserie;
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
     * @param string|Mapserie|null $mapserie
     */
    private function addMapserieForm($form, $mapserie)
    {
        $options = array_merge(
            array(
                'auto_initialize' => false,
                'attr'          => array(
                ),
                'class'=> 'GptCaveBundle:Mapserie',
                'required' => false,
                'translation_domain' => 'cavemessages',
                'placeholder'   => 'select.mapserie',
                'label'=>'Mapserie',
                'choice_label' => 'name',
                'choice_value'=>'mapserieid',
                'query_builder' => function (EntityRepository $repository) use ($mapserie) {
                    return $repository->createQueryBuilder('m')
                           ->where('m.mapserieid = :mapserie')
                          ->setParameter('mapserie', $mapserie);
                    }
                
            ),$this->mapserie['options'] ?? []
        );

        $form->add($this->factory->createNamed(
            $this->mapserie['name'],
            EntityType::class,
            $mapserie,
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

        if(!$this->mapserie['getMethod']?? false){
            $this->addMapserieForm($event->getForm(), null);
            return;
        }

        $mapserieorNull   = $data->{$this->mapserie['getMethod']}() ?? null;

        $this->addMapserieForm($event->getForm(), $mapserieorNull);
    }

    /**
     * @param FormEvent $event
     */
    public function preSubmit(FormEvent $event)
    {
        $data = $event->getData();
        if (null === $data) return;

        $mapserieorNull = $data[$this->mapserie['name']] ?? null;

        $this->addMapserieForm($event->getForm(), $mapserieorNull);
    }
}