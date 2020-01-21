<?php
namespace App\GptCavebackendBundle\EventListener\Form;
use App\GptCaveBundle\Entity\Area;
use App\GptGeonamesBundle\Entity\Admin1;
use App\GptGeonamesBundle\Entity\Country;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormInterface;

/**
 * Set form field on page load or submit if selected value exists.
 * @package App\GptCavebackendBundle\EventListener\Form
 *
 * @example
 *      $country= array(...);
 *      $admin1= array(...);
 *      $area = array(
 *          'name'=>'area',//property/field name
 *          'getMethod'=>'getArea',//Get method for property if mapped
 *          'options'=>array() //ChoiceType Field options
 *      );
 *
 * $builder->addEventSubscriber(new AddAreaFieldSubscriber( $factory, $country, $admin1, $area) );
 */
class AddAreaFieldSubscriber implements EventSubscriberInterface
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
     * @var array|null
     */
    private $admin1;
    /**
     * @var array
     */
    private $area;
    
    public function __construct(FormFactoryInterface $factory, array $country=[], array $admin1=null, $area=[])
    {
        $this->factory = $factory;
        $this->country = $country;
        $this->admin1  = $admin1;
        $this->area  = array_merge(
            array(
                'name'=>'area',//field name/property if mapped
                'getMethod'=>'getArea',//method if mapped
                'options'=>array(
                    'placeholder' => 'select.area',
                    'attr'=>array(
                    )
                )
            ),
            $area);
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::PRE_SUBMIT     => 'preBind'
        );
    }

    /**
     * @param FormInterface $form
     * @param string|Country|null $country
     * @param string|Admin1|null $admin1
     * @param string|Area|null $area
     */
    private function addAreaForm($form, $country, $admin1, $area)
    {
        $options = array_merge(
                    array(
                    'auto_initialize' => false,
                    'attr'          => array(),
                    'translation_domain' => 'cavemessages',
                    'placeholder'   => 'select.area',
                    'class'         => 'GptCaveBundle:Area',
                    'required'      => false,
                    'label'=> 'area',//cavemessages.yml
                    'choice_label' => 'name',
                    'choice_value'=>'areaid',
                    'query_builder' => function (EntityRepository $repository) use ($country, $admin1, $area) {
                         $qb = $repository->createQueryBuilder('area')
                            ->innerJoin('area.country', 'country')
                            ->where('area.country = :country')
                            ->setParameter(':country', $country);
                         if($admin1!==NULL){
                             $qb->andWhere('area.admin1 = :admin1')
                                ->setParameter(':admin1', $admin1);
                         }
                        if($area!==NULL){
                            $qb->andWhere('area.areaid = :areaid')
                                ->setParameter(':areaid', $area);
                        }
                        return $qb;
                    }
                ), $this->area['options'] ?? []
                );

        $form->add($this->factory->createNamed(
            $this->area['name'],
            EntityType::class,
            $area,
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
     * @return array
     */
    public function getArea(): array
    {
        return $this->area;
    }

    /**
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $data = $event->getData();
        if (null === $data) return;

        $this->addAreaForm($event->getForm(),
            $data->{$this->country['getMethod']}() ?? null,
            $this->admin1==null ? null : $data->{$this->admin1['getMethod']}() ?? null,
        $data->{$this->area['getMethod']}() ?? null);
    }

    /**
     * @param FormEvent $event
     */
    public function preBind(FormEvent $event)
    {
        $data = $event->getData();
        if (null === $data) return;

        $this->addAreaForm($event->getForm(),
            $data[$this->country['name']] ?? null,
            $data[$this->admin1['name']] ?? null,
            $data[$this->area['name']]?? null);
    }
}