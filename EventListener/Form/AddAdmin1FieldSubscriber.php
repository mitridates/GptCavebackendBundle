<?php

namespace App\GptCavebackendBundle\EventListener\Form;

use App\GptGeonamesBundle\Entity\Admin1;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormInterface;

/**
 * Admin1 Events for mapped or unmapped form fields
 * @package App\GptCavebackendBundle\EventListener\Form
 * @see https://symfony.com/doc/current/reference/forms/types/choice.html
 * @example
 *
 * $admin1 = array(
 *      'name'=>'admin1',//property/field name
 *      'getMethod'=>'getAdmin1',//Get method for property if mapped
 *      'options'=>array() //ChoiceType Field options
 *  );
 *
 * $builder->addEventSubscriber(new AddAdmin1FieldSubscriber($factory, $country, $admin1) )
 */
class AddAdmin1FieldSubscriber implements EventSubscriberInterface
{
    /**
     * @var FormFactoryInterface
     */
    private $factory;
    /**
     * @var array
     */
    private $country;
    /**
     * @var array
     */
    private $admin1;
    
    public function __construct(FormFactoryInterface $factory, array $country=[], array $admin1=[])
    {
        $this->factory = $factory;
        $this->country = $country;
        $this->admin1  = array_merge(
            array(
                'name'=>'admin1',//field name/property if mapped
                'getMethod'=>'getAdmin1',//method if mapped
                'options'=>array(
                    'placeholder'   => 'government.level.admin1',
                    'attr'=>array(
                    )
                )
            ),
            $admin1
        );
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::PRE_SUBMIT   => 'preBind'
        );
    }

    /**
     * @param FormInterface $form
     * @param string|Country|null $country
     * @param string|Admin1|null $admin1
     */
    private function addAdmin1Form($form, $country, $admin1)
    {
        $options = array_merge(
                array(
                'auto_initialize' => false,
                'attr'          => array(),
                'class'         => 'GptGeonamesBundle:Admin1',
                'required'      => false,
                'translation_domain' => 'cavemessages',
                'placeholder'   => 'select.government.level.admin1',
                'label'=>'government.level.admin1',//cavemessages.yml
                'choice_label' => 'name',
                'choice_value'=>'admin1id',
                'query_builder' =>
                        function (EntityRepository $repository) use ($country, $admin1) {
                            $qb = $repository->createQueryBuilder('admin1')
                            ->innerJoin('admin1.country', 'country')
                            ->where('admin1.country = :country')
                            ->setParameter(':country', $country);
                        if($admin1!==NULL){
                            $qb->andWhere('admin1.admin1id = :admin1')
                              ->setParameter(':admin1', $admin1);
                        }
                        return $qb;
                    }
                ), $this->admin1['options']
            );

        $form->add($this->factory->createNamed(
            $this->admin1['name'],
            EntityType::class,
            $admin1,
            $options
        ));
    }

    /**
     * @return array
     */
    public function getCountry(): array
    {
        return $this->country;
    }

    /**
     * @return array
     */
    public function getAdmin1(): array
    {
        return $this->admin1;
    }

    /**
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $data = $event->getData();
        if (null === $data) return;
        $form = $event->getForm();
        
        if(! $this->admin1['getMethod']?? false){
            $this->addAdmin1Form($form, NULL, NULL);
            return;
        }

        $countryorNull = $data->{$this->country['getMethod']}() ?? null;
        $admin1orNull = (null!== $this->admin1) ? $data->{$this->admin1['getMethod']}() : null;
        $this->addAdmin1Form($form, $countryorNull, $admin1orNull);
    }

    /**
     * @param FormEvent $event
     */
    public function preBind(FormEvent $event)
    {
        $data = $event->getData();//array

        if (null === $data) return;

        $form = $event->getForm();
        $countryorNull = $data[$this->country['name']] ?? null;
        $admin1orNull =  $data[$this->admin1['name']] ?? null;

        $this->addAdmin1Form($form, $countryorNull, $admin1orNull);
    }
}