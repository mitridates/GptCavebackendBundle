<?php
namespace App\GptCavebackendBundle\EventListener\Form;

use App\GptGeonamesBundle\Entity\Admin2;
use App\GptGeonamesBundle\Entity\Admin3;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormInterface;

/**
 * Admin3  Events
 * @package App\GptCavebackendBundle\EventListener\Form
 *
 * @example
 *
 * $admin3 = array(
 *      'name'=>'admin3',//property/field name
 *      'getMethod'=>'getAdmin3',//Get method for property if mapped
 *      'options'=>array() //ChoiceType Field options
 *  );
 *
 * $builder->addEventSubscriber(new AddAdmin3FieldSubscriber($factory, $admin2, $admin3) )
 */
class AddAdmin3FieldSubscriber implements EventSubscriberInterface
{
    /**
     * @var FormFactoryInterface
     */
    private $factory;
    /**
     * @var array
     */
    private $admin2;
    /**
     * @var array
     */
    private $admin3;
    
    public function __construct(FormFactoryInterface $factory, array $admin2=[], array $admin3=[])
    {
        $this->factory = $factory;
        $this->admin2  = $admin2;
        $this->admin3  = array_merge(
            array(
                'name'=>'admin3',//field name/property if mapped
                'getMethod'=>'getAdmin3',//method if mapped
                'options'=>array(
                    'placeholder'   => 'government.level.admin3',
                    'attr'=>array(
                    )
                )
            ), $admin3);
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
     * @param string|Admin2|null  $admin2
     * @param string|Admin3|null  $admin3
     */
    private function addAdmin3Form($form, $admin2, $admin3)
    {
        $options = array_merge(
                    array(
                        'class'         => 'GptGeonamesBundle:Admin3',
                        'translation_domain' => 'cavemessages',
                        'placeholder'   => 'select.government.level.admin3',//cavemessages.yml
                        'label'=> 'government.level.admin3',//cavemessages.yml
                        'attr'          => array(),
                        'required'=>false,
                        'auto_initialize' => false,
                        'choice_label' => 'name',
                        'choice_value'=>'admin3id',
                        'query_builder' => function (EntityRepository $repository) use ($admin2, $admin3)
                        {
                            //if(null == $admin2 && null == $admin3) return null;
                            $qb = $repository->createQueryBuilder('a3')
                                //->select(['a3.admin3id', 'a3.name'])
                                ->innerJoin('a3.admin2', 'a2')
                                ->where('a3.admin2 = :id')
                                ->setParameter(':id', $admin2);
                            if($admin3!==NULL){
                                $qb->andWhere('a3.admin3id = :admin3')
                                    ->setParameter(':admin3', $admin3);
                            }
                            return $qb;
                        }
                    ), $this->admin3['options']
        );
        
        $form->add($this->factory->createNamed(
            $this->admin3['name'],
            EntityType::class,
            $admin3,
            $options));
    }

    /**
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $data = $event->getData();
        if (null === $data) return;

        if(! $this->admin3['getMethod']?? false){
            $this->addAdmin3Form($event->getForm(), null, null);
            return;
        }

        $admin2orNull   = $data->{$this->admin2['getMethod']}() ?? null;
        $admin3orNull   = $data->{$this->admin3['getMethod']}() ?? null;

        $this->addAdmin3Form($event->getForm(), $admin2orNull, $admin3orNull);
    }

    /**
     * @param FormEvent $event
     */
    public function preBind(FormEvent $event)
    {
        $data = $event->getData();//array
        if (null === $data) return;

        $admin2orNull = $data[$this->admin2['name']] ?? null;
        $admin3orNull = $data[$this->admin3['name']] ?? null;

        $this->addAdmin3Form($event->getForm(), $admin2orNull,  $admin3orNull);
    }
}