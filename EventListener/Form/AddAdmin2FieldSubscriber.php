<?php

namespace App\GptCavebackendBundle\EventListener\Form;

use App\GptGeonamesBundle\Entity\Admin1;
use App\GptGeonamesBundle\Entity\Admin2;
use App\GptGeonamesBundle\Entity\Country;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormInterface;

/**
 * Admin2  Events
 * @package App\GptCavebackendBundle\EventListener\Form
 *
 * @example
 *
 * $admin2 = array(
 *      'name'=>'admin1',//property/field name
 *      'getMethod'=>'getAdmin2',//Get method for property if mapped
 *      'options'=>array() //ChoiceType Field options
 *  );
 *
 * $builder->addEventSubscriber(new AddAdmin2FieldSubscriber($factory, $country, $admin1, $admin2) )
 */
class AddAdmin2FieldSubscriber implements EventSubscriberInterface
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
    /**
     * @var array
     */
    private $admin2;    

    public function __construct(FormFactoryInterface $factory, array $country=[], array $admin1=[], array $admin2=[])
    {
        $this->factory = $factory;
        $this->country = $country;
        $this->admin1  = $admin1;
        $this->admin2  = array_merge(
            array(
                'name'=>'admin2',//field name/property if mapped
                'getMethod'=>'getAdmin2',//method if mapped
                'options'=>array(
                    'placeholder'   => 'government.level.admin2',
                    'attr'=>array(
                    )
                )
            ), $admin2);
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return array(
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::PRE_SUBMIT   => 'preBind'
        );
    }

    /**
     * @param FormInterface $form
     * @param string|Country|null $country
     * @param string|Admin1|null  $admin1
     * @param string|Admin2|null  $admin2
     */
    private function addAdmin2Form($form, $country, $admin1, $admin2): void
    {
        $options = array_merge(
                    array(
                    'class'         => 'GptGeonamesBundle:Admin2',
                    'translation_domain' => 'cavemessages',
                    'placeholder'   => 'select.government.level.admin2',//cavemessages.yml
                    'label'=> 'government.level.admin2',//cavemessages.yml
                    'attr'          => array(),
                    'required'      => false,
                    'auto_initialize' => false,
                    'choice_label' => 'name',
                    'choice_value'=>'admin2id',
                    'query_builder' => function (EntityRepository $repository) use ($country, $admin1, $admin2) {
                        //if(null == $country && null == $admin1 && null == $admin2) return null;
                        $qb = $repository->createQueryBuilder('admin2')
                                //->select(['admin2.admin2id', 'admin2.name'])
                                ->innerJoin('admin2.country', 'country')
                                ->where('admin2.country = :country')
                                ->setParameter(':country', $country);
                             if($admin1!==NULL){
                                 $qb->andWhere('admin2.admin1 = :admin1')
                                    ->setParameter(':admin1', $admin1);
                             }
                            if($admin2!==NULL){
                                $qb->andWhere('admin2.admin2id = :admin2')
                                    ->setParameter(':admin2', $admin2);
                            }
                            return $qb;
                        }
                    ), $this->admin2['options']
        );

        $form->add($this->factory->createNamed(
            $this->admin2['name'],
            EntityType::class,
            $admin2,
            $options));
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
     * @return array
     */
    public function getAdmin2(): array
    {
        return $this->admin2;
    }

    /**
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event): void
    {
        $data = $event->getData();
        if (null === $data) return;

        if(! $this->admin2['getMethod']?? false){
            $this->addAdmin2Form($event->getForm(), null, null, null);
            return;
        }

        $countryorNull  = $data->{$this->country['getMethod']}() ?? null;
        $admin1orNull   = $data->{$this->admin1['getMethod']}() ?? null;
        $admin2orNull   = $data->{$this->admin2['getMethod']}() ?? null;

        $this->addAdmin2Form($event->getForm(), $countryorNull, $admin1orNull,  $admin2orNull);
    }

    /**
     * @param FormEvent $event
     */
    public function preBind(FormEvent $event): void
    {
        $data = $event->getData();//array
        if (null === $data) return;


        $countryorNull = $data[$this->country['name']] ?? null;
        $admin1orNull = $data[$this->admin1['name']] ?? null;
        $admin2orNull = $data[$this->admin2['name']] ?? null;

        $this->addAdmin2Form($event->getForm(), $countryorNull, $admin1orNull,  $admin2orNull);
    }

}