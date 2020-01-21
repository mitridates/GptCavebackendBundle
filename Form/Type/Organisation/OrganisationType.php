<?php
namespace App\GptCavebackendBundle\Form\Type\Organisation;
use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\GptCaveBundle\Entity\Organisation;
use App\GptCavebackendBundle\EventListener\Form\AddAdmin3FieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddCountryFieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddAdmin1FieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddAdmin2FieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddOrganisationFieldSubscriber;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Contracts\Translation\TranslatorInterface;

class OrganisationType extends AbstractType
{

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $defaults)
    {
        $factory = $builder->getFormFactory();
        $this->translator = $defaults['translator'];

        /**
         * @var Organisation $organisation
         */
        $organisation = $builder->getData();

        $countrySubscriber = new AddCountryFieldSubscriber($factory, array(
            'options'=>array(
                'attr'=>array(
                    'code_id'=>376,
                    'class'=>'country',//css
                    'onChange'=>"$('.admin1, .admin2, .admin3').html('')"//js
                )
            )
        ));
        $builder->addEventSubscriber($countrySubscriber);

        $admin1Subscriber = new AddAdmin1FieldSubscriber($factory, $countrySubscriber->getCountry(), [
                'options'=>array(
                    'attr'=>array('code_id'=>377,'class'=>'select2 admin1')
                )]
        );
        $builder->addEventSubscriber($admin1Subscriber);

        $admin2Subscriber = new AddAdmin2FieldSubscriber($factory, $admin1Subscriber->getCountry(), $admin1Subscriber->getAdmin1(), [
                'options'=>array(
                    'attr'=>array('class'=>'select2 admin2')
                )]
        );
        $builder->addEventSubscriber($admin2Subscriber);

        $admin3Subscriber = new AddAdmin3FieldSubscriber($factory, $admin2Subscriber->getAdmin2(), [
                'options'=>array(
                    'attr'=>array('class'=>'select2 admin3')
                    )]
        );
        $builder->addEventSubscriber($admin3Subscriber);


        //Org address
        $countrySubscriber = new AddCountryFieldSubscriber($factory, [
            'name'=>'countryaddress',
            'getMethod'=>'getCountryaddress',
            'options'=>[
                'attr'=>[
                    'code_id'=>395,
                    'class'=>'countryaddress',//css
                    'onChange'=>"$('.admin2address, .admin3address').html('')"//js
                ]]
            ]
        );
        $builder->addEventSubscriber($countrySubscriber);

        $admin1Subscriber = new AddAdmin1FieldSubscriber($factory, $countrySubscriber->getCountry(), [
                'name'=>'admin1address',
                'getMethod'=>'getAdmin1address',
                'options'=>[
                    'attr'=>[
                        'code_id'=>377,
                        'class'=>'select2 admin1address'
                    ]
                ]]
        );
        $builder->addEventSubscriber($admin1Subscriber);

        $admin2Subscriber = new AddAdmin2FieldSubscriber($factory, $admin1Subscriber->getCountry(), $admin1Subscriber->getAdmin1(), [
                'name'=>'admin2address',
                'getMethod'=>'getAdmin2address',
                'options'=>array(
                    'attr'=>array('class'=>'select2 admin2address', 'code_id'=>392)
                )]
        );
        $builder->addEventSubscriber($admin2Subscriber);

        $admin3Subscriber = new AddAdmin3FieldSubscriber($factory, $admin2Subscriber->getAdmin2(), [
                'name'=>'admin3address',
                'getMethod'=>'getAdmin3address',
                'options'=>array(
                    'attr'=>array('class'=>'select2 admin3address', 'code_id'=>392)
                )]
        );
        $builder->addEventSubscriber($admin3Subscriber);

        $organisationSubscriber= new AddOrganisationFieldSubscriber($factory,[
            'name'=>'currentidifdefunct',
            'getMethod'=>'getCurrentidifdefunct',
            'options'=>[
                'attr'=>[
                    'code_id'=>384,
                    'class'=>'select2 organisation',//css
                    ]
                ],
            ]
        );
        $builder->addEventSubscriber($organisationSubscriber);

        foreach(explode(',', 'code:178,defunctyear:383,postcode:378,postcodefist:379,'
                            . 'addressline0:385,addressline1:386,'
                            . 'addressline2:387,addressline3:388,addressline4:389,initials:390,'
                            . 'name:391,email:614,webpage:615,defunct:382') as $el)
        {//admin3address:392,
                            $field = explode(':', $el);

                            $arr = ['attr'=>['code_id'=>$field[1]]];
                            $builder->add($field[0], NULL, $arr);
        }



        foreach(explode(',','type:381,coverage:393,grouping:394') as $el){
            $field = explode(':', $el);
            $builder->add(
                   $field[0], EntityType::class, array(
                        'class'=>'GptCaveBundle:Fieldvaluecode',
                        'attr'=>array('code_id'=>$field[1]),
                        'required' => false,//show empty option
                        'choice_label' => 'value',
                        'choice_value'=>'id',
                        'query_builder' => function(EntityRepository $e) use ($field){
                            return $e->createQueryBuilder('f')
                            ->select('f')
                            ->where('f.field = :field')
                            ->orderBy('f.value', 'ASC')
                            ->setParameter('field', $field[1]);
                        })
                    );
        }

        $builder->add('idgenerator', CheckboxType::class, array(
            'attr'=>['code_id'=>10002],
            'required' => false)

        );

        $builder->add('code', null, array(
                'attr'=>['code_id'=>178],
                'disabled' => $organisation->getIdgenerator()
            )
        );

        //set defunct if child is not null
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event){
            /**
             * @var array $data
             */
            $data= $event->getData();
            if($data['currentidifdefunct']!=null || $data['defunctyear']!=null){
                $data['defunct']= true;
                $event->setData($data);
            }
        });

        //avoid circular reference
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($organisation){

            /**
             * @var Organisation $data
             */
            $data= $event->getData();
            if($data->getCurrentidifdefunct()!=null)
            {
                if($organisation->getOrganisationid()== $data->getCurrentidifdefunct()->getOrganisationid())
                {
                    $event->getForm()->get('currentidifdefunct')->addError(
                        new FormError($this->translator->trans('cave.circularreference.field', [], 'validators'))
                    );
                }
            }
        });
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\GptCaveBundle\Entity\Organisation',
            'translator'=>TranslatorInterface::class
        ));
    }
}


