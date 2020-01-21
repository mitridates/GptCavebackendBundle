<?php
namespace App\GptCavebackendBundle\Form\Type\Cave;
use App\GptCaveBundle\Entity\Cave;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\GptCavebackendBundle\EventListener\Form\AddCountryFieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddAdmin1FieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddAdmin2FieldSubscriber;
use App\GptCavebackendBundle\EventListener\Form\AddAdmin3FieldSubscriber;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Contracts\Translation\TranslatorInterface;

class EditCaveType extends AbstractType
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


        $fields = '70:name;512:updatestatus;9:quantityofentrances;27:nearestlocality;'
            . '54:percentmapped;77:serial;'
            . '10380:hide;13075:geographiclocation;13076:system;10380:hide;229:localgovernmentarea;'
            . '56:length;58:lengthaccuracy;67:extentlength;68:extentwidth;511:verticalextent;62:verticalaccuracy;'
            .'59:extentbelowentrance;60:extentaboveentrance;'
        .'63:lengthlargestchamber;64:widthlargestchamber;65:heighlargestchamber';


        foreach(explode(';', $fields) as $el){
            if(!strpos($el,':')) {var_dump ($el);exit;}
            $field = explode(':', $el);
            $arr = ['attr'=>['code_id'=>$field[0]]];
            if($field[1]=='name'){ $arr['required']=true; }
            $builder->add($field[1], NULL, $arr);
        }

        foreach(explode(';','1:featuretype;10:entrancetype;20:penetrability;29:degreeexplored;78:entrancemarking;512:updatestatus;'
                .'57:lengthmethod;61:verticalmethod;297:lengthcategory;527:depthcategory') as $el){
            $field = explode(':', $el);
            $builder->add(
                   $field[1], EntityType::class, array(
                        'class'=>'GptCaveBundle:Fieldvaluecode',
                        'attr'=>array('code_id'=>$field[0], 'class'=>'select2', 'style'=>'width:100%'),
                        'required' => false,
                        'choice_label' => 'value',
                        'choice_value'=>'id',
                        'query_builder' => function(EntityRepository $e) use ($field){
                            return $e->createQueryBuilder('f')
                            ->select('f')
                            ->where('f.field = :field')
                            ->setParameter('field', $field[0]);
                            },
                        )
                    );
        }


        $countrySubscriber = new AddCountryFieldSubscriber($factory, array(
            'options'=>array(
                'attr'=>array(
                    'code_id'=>220,
                    'class'=>'countryid',
                    'onChange'=>"$('.admin1').html('')"
                )
            )
        ));
        $builder->addEventSubscriber($countrySubscriber);

        $admin1 = array(
            'options'=>array(
                'attr'=>array(
                    'code_id'=>18,
                    'class'=>'select2 admin1',//css
                )
            )
        );
        $admin1Subscriber = new AddAdmin1FieldSubscriber($factory,$countrySubscriber->getCountry(), $admin1);
        $builder->addEventSubscriber($admin1Subscriber);

        $admin2 = array('name'=>'admin2',
            'options'=>array(
                'attr'=>array(
                    'class'=>'select2 admin2',//css
                )
            )
        );
        $admin2Subscriber = new AddAdmin2FieldSubscriber(
            $factory,
            $countrySubscriber->getCountry(),
            $admin1Subscriber->getAdmin1(),
            $admin2
        );
        $builder->addEventSubscriber($admin2Subscriber);

        $admin3Subscriber= new AddAdmin3FieldSubscriber($factory, $admin2Subscriber->getAdmin2(),array(
            'name'=>'admin3',
            'options'=>array(
                'attr'=>array(
                    'class'=>'select2 admin3',//css
                )
            )
        ));
        $builder->addEventSubscriber($admin3Subscriber);


//
//        foreach(explode(';','1:featuretype;10:entrancetype;20:penetrability;78:entrancemarking;512:updatestatus') as $el){
//            $field = explode(':', $el);
//            $builder->add(
//                   $field[1], EntityType::class, array(
//                        'class'=>'GptCaveBundle:Fieldvaluecode',
//                        'attr'=>array('code_id'=>$field[0], 'class'=>'select2', 'style'=>'width:100%'),
//                        'required' => false,
//                        'choice_label' => 'value',
//                        'choice_value'=>'id',
//                        'query_builder' => function(EntityRepository $e) use ($field){
//                            return $e->createQueryBuilder('f')
//                            ->select('f')
//                            ->where('f.field = :field')
//                            ->setParameter('field', $field[0]);
//                            },
//                        )
//                    );
//        }
//
//
//        $fields = '70:name;9:quantityofentrances;229:localgovernmentarea;'
//                . '77:serial';
//
//        foreach(explode(';', $fields) as $el){
//            if(!strpos($el,':')) {var_dump ($el);exit;}
//            $field = explode(':', $el);
//
//            $arr = ['attr'=>['code_id'=>$field[0]]];
//
//            if($field[1]=='name'){ $arr['required']=true; }
//
//            $builder->add($field[1], NULL, $arr);
//        }
//
//    /**
//     * CAVE MEASURE
//     */
//        $fields = '57:lengthmethod;61:verticalmethod;297:lengthcategory;527:depthcategory;29:degreeexplored';
//        foreach(explode(';',$fields) as $el){
//            $field = explode(':', $el);
//            $builder->add(
//                $field[1], EntityType::class, array(
//                    'class'=>'GptCaveBundle:Fieldvaluecode',
//                    'attr'=>array('code_id'=>$field[0], 'class'=>'select2', 'style'=>'width:100%'),
//                    'required' => false,
//                    'choice_label' => 'value',
//                    'choice_value'=>'id',
//                    'query_builder' => function(EntityRepository $e) use ($field){
//                        return $e->createQueryBuilder('f')
//                            ->select('f')
//                            ->where('f.field = :field')
//                            ->setParameter('field', $field[0]);
//                    },
//                )
//            );
//        }
//
//
//
//        $fields = '56:length;58:lengthaccuracy;511:verticalextent;59:extentbelowentrance;60:extentaboveentrance;'
//            . '62:verticalaccuracy;63:lengthlargestchamber;64:widthlargestchamber;65:heighlargestchamber;'
//            . '67:extentlength;68:extentwidth;54:percentmapped';
//
//        foreach(explode(';', $fields) as $el){
//            if(!strpos($el,':')) {var_dump ($el);exit;}
//            $field = explode(':', $el);
//
//            $arr = ['attr'=>['code_id'=>$field[0]]];
//            $builder->add($field[1], NULL, $arr);
//        }
//
//        /**
//         * Caveposition
//         */
//        //propiedades con distinto nombre pero mismos choices posibles
//        //ejem 251:gridrefunits(metros, pies...), 443:altitudeunits(metros, pies...)
//        foreach(explode(';','251:gridrefunits;628:geographicmethod;651:grefmethod;661:altitudemethod;443:altitudeunits') as $el){
//            $field = explode(':', $el);
//            if(in_array($field[0], ['628', '661', '651'])){//unidades de medida
//                $field['code'] = 641;
//            }elseif(in_array($field[0], ['251', '443'])){
//                $field['code'] = 298;
//            }else{
//                $field['code'] = $field[0];
//            }
//            $builder->add(
//                $field[1], EntityType::class, array(
//                    'class'=>'GptCaveBundle:Fieldvaluecode',
//                    'attr'=>array('code_id'=>$field[0], 'class'=>'select2', 'style'=>'width:100%'),
//                    'required' => false,
//                    'choice_label' => 'value',
//                    'choice_value'=>'id',
//                    'query_builder' => function(EntityRepository $e) use ($field){
//                        return $e->createQueryBuilder('f')
//                            ->select('f')
//                            ->where('f.field = :field')
//                            ->setParameter('field', $field['code']);
//                    },
//                )
//            );
//        }
//
////        247:gridzone === 673:utmzonenumber;674:utmzoneletter
//        $poss = '631:geographicdate;652:grefdate;'
//            .'249:easting;250:northing;252:grefprecision;649:geographiccomment;660:grefcomment;'
//            .'300:grefaccuracy;625:geogeodetdatum;626:mapgrid;'
//            .'442:altitude;444:altitudeprecision;445:altitudeaccuracy;670:altitudecomment;'
//            .'650:grefgeodetdatum;627:altitudeheightdatum;676:sheet100;677:sheet25;'
//            .'247:gridzone;531:orientation;'//landunit
//            .'441:landunit5;253:landunit4;254:landunit3;255:landunit2;256:landunit1;365:landunit0;'
//            .'21:latitudecoarse;22:longitudecoarse;'//Coarse Anteriormente en tabla CA00000
//            . '23:mapsheetnamecoarse;26:gridreferencecoarse;25:grrefqualifiercoarse;28:altitudecoarse';
//
//        foreach(explode(';', $poss) as $el){
//            $field = explode(':', $el);
//            $arr = ['attr'=>['code_id'=>$field[0], 'class'=>'input-sm']];
//            $builder->add($field[1], NULL, $arr);
//        }
//
//        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event){
//            /**
//             * @var Cave $entity
//             */
//            $entity = $event->getData();
//            $form = $event->getForm();
//
//            if(empty($entity->getName())){
//                $form->get('name')->addError(new FormError($this->translator->trans('form.must.complete.this.field',  [], 'caveerrors')));
//            }
//
//            $latlngIsNull=            $this->checkLatlng($entity, $form);
//            $gridrefIsNull=          $this->checkGridref($entity, $form);
//            $altitudeIsNull=    $this->checkAltitude($entity, $form);
//            $MappossitionIsNull=    $this->checkMappossition($entity, $form);
//            $landunitIsNull=    $this->checkLandunit($entity, $form);
//
//            if( $latlngIsNull=== NULL
//                && $gridrefIsNull=== NULL
//                && $altitudeIsNull=== NULL
//                && $MappossitionIsNull=== NULL
//                && $landunitIsNull=== NULL
//            ){
//               /* $form->addError(new FormError($this->translator->trans('form.cannot.be.empty',  [], 'caveerrors')));
//                $form->addError(new FormError($this->translator->trans('form.use.button.to.delete',  [], 'caveerrors')))*/;
//            }
//        });
    }

    private function checkLatlng(Cave $entity, FormInterface &$form){
        $fieldNames = ['latitude','longitude', 'geographicmethod','geographicdate', 'geogeodetdatum', 'geogprecision', 'geogaccuracy','geographiccomment'];
        $is_empty= true;
        foreach($fieldNames as $name){
            if( $entity->{'get'.ucfirst($name)}()!== NULL){
                $is_empty= false;
                break;
            }
        }

        if($is_empty) return NULL;

        $is_valid=true;

        if($entity->getLatitude()== NULL){
            $is_valid=false;
            $form->get('latitude')->addError(new FormError($this->translator->trans('form.empty.master.has.filled.children',  [], 'caveerrors')));
        }
        if($entity->getLongitude()== NULL){
            $is_valid=false;
            $form->get('longitude')->addError(new FormError($this->translator->trans('form.empty.master.has.filled.children',  [], 'caveerrors')));
        }
        return $is_valid;
    }

    private function checkGridref(Cave $entity, FormInterface &$form){
        $fieldNames = ['easting','northing', 'gridzone', 'grefmethod',
            'grefdate', 'grefgeodetdatum', 'grefprecision', 'grefaccuracy',
            'mapgrid', 'gridrefunits',
            'grefcomment'];
        $is_empty= true;
        foreach($fieldNames as $name){
            if( $entity->{'get'.ucfirst($name)}()!== NULL){
                $is_empty= false;
                break;
            }
        }

        if($is_empty) return NULL;

        $is_valid=true;

        if($entity->getEasting()== NULL){
            $is_valid=false;
            $form->get('easting')->addError(new FormError($this->translator->trans('form.empty.master.has.filled.children',  [], 'caveerrors')));
        }
        if($entity->getNorthing()== NULL){
            $is_valid=false;
            $form->get('northing')->addError(new FormError($this->translator->trans('form.empty.master.has.filled.children',  [], 'caveerrors')));
        }
        return $is_valid;
    }

    private function checkAltitude(Cave $entity, FormInterface &$form){
        $fieldNames = ['altitude','altitudeunits', 'altitudemethod', 'altitudeheightdatum','altitudeprecision', 'altitudeaccuracy', 'altitudecomment'];
        $is_empty= true;
        $is_valid=true;

        foreach($fieldNames as $name){
            if( $entity->{'get'.ucfirst($name)}()!== NULL){
                $is_empty= false;
                break;
            }
        }

        if($is_empty) return NULL;


        if($entity->getAltitude()== NULL){
            $is_valid=false;
            $form->get('altitude')->addError(new FormError($this->translator->trans('form.empty.master.has.filled.children',  [], 'caveerrors')));
        }
        return $is_valid;
    }

    private function checkMappossition(Cave $entity, FormInterface &$form){
        $fieldNames = ['sheet100','sheet25', 'orientation'];
        foreach($fieldNames as $name){
            if( $entity->{'get'.ucfirst($name)}()!== NULL){
                return false;
            }
        }
        return NULL;
    }

    private function checkLandunit(Cave $entity, FormInterface &$form){
        $fieldNames = ['landunit5','landunit4','landunit3','landunit2','landunit1','landunit0'];
        foreach($fieldNames as $name){
            if( $entity->{'get'.ucfirst($name)}()!== NULL){
                return false;
            }
        }
        return NULL;
    }

    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\GptCaveBundle\Entity\Cave',
            'translator'=>TranslatorInterface::class
        ));
    }
}


