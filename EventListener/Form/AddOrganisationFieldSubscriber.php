<?php
namespace App\GptCavebackendBundle\EventListener\Form;
use App\GptCaveBundle\Entity\Organisation;
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
class AddOrganisationFieldSubscriber implements EventSubscriberInterface
{
    /**
     * @var FormFactoryInterface
     */
    private $factory;
    /**
     * @var array
     */
    private $organisation;

    public function __construct(FormFactoryInterface $factory, array $organisation=[])
    {
        $this->factory = $factory;
        $this->organisation= array_merge(
            array(
                'name'=>'organisation',//field name/property if mapped
                'getMethod'=>'getOrganisation',//method if mapped
                'options'=>array(
                    'placeholder'   => 'select.organisation',
                    'attr'=>array()
                )
            ),
            $organisation
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
     * @param string|Organisation|null $organisation
     */
    private function addOrganisationForm($form, $organisation)
    {
        $options = array_merge(
            array(
                'auto_initialize' => false,
                'attr'          => array(),
                'translation_domain' => 'cavemessages',
                'placeholder'   => 'select.organisation',
                'class'=> 'GptCaveBundle:Organisation',
                'required' => false,
                'label'=>'Organisation',
                'choice_label' => 'name',
                'choice_value'=>'organisationid',
                'query_builder' => function (EntityRepository $repository) use ($organisation) {
                        return $repository->createQueryBuilder('o')
                                ->where('o.organisationid = :organisation')
                                ->setParameter('organisation', $organisation);
                    }
            ),$this->organisation['options'] ?? []
        );

        $form->add($this->factory->createNamed(
            $this->organisation['name'],
            EntityType::class,
            $organisation,
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

        $this->addOrganisationForm($event->getForm(), $data->{$this->organisation['getMethod']}() ?? null);
    }

    /**
     * @param FormEvent $event
     */
    public function preSubmit(FormEvent $event)
    {
        $data = $event->getData();
        if (null === $data) return;

        $this->addOrganisationForm($event->getForm(), $data[$this->organisation['name']] ?? null);
    }

}