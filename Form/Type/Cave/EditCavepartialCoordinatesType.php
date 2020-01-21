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

class EditCavepartialCoordinatesType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $defaults)
    {

//        247:gridzone === 673:utmzonenumber;674:utmzoneletter
        $fields = '631:geographicdate;652:grefdate;245:latitude;246:longitude;514:geogprecision;515:geogaccuracy;'
            .'249:easting;250:northing;252:grefprecision;649:geographiccomment;660:grefcomment;'
            .'300:grefaccuracy;625:geogeodetdatum;626:mapgrid;'
            //altitude
            .'442:altitude;444:altitudeprecision;445:altitudeaccuracy;670:altitudecomment;'
            .'650:grefgeodetdatum;627:altitudeheightdatum;247:gridzone';


        foreach(explode(';', $fields) as $el){
            if(!strpos($el,':')) {var_dump ($el);exit;}
            $field = explode(':', $el);
            $arr = ['attr'=>['code_id'=>$field[0]]];
            if($field[1]=='name'){ $arr['required']=true; }
            $builder->add($field[1], NULL, $arr);
        }

        $fields = '251:gridrefunits;628:geographicmethod;651:grefmethod;661:altitudemethod;443:altitudeunits';

        foreach(explode(';',$fields) as $el){

            $field = explode(':', $el);//set Fieldvaluecodes
            if(in_array($field[0], ['628', '661', '651'])){//unidades de medida con mismo Fieldvaluecode
                $field['code'] = 641;
            }elseif(in_array($field[0], ['251', '443'])){
                $field['code'] = 298;
            }else{
                $field['code'] = $field[0];
            }

            $builder->add(
                   $field[1], EntityType::class, array(
                        'class'=>'GptCaveBundle:Fieldvaluecode',
                        'attr'=>array('code_id'=>$field[0]),
                        'required' => false,
                        'choice_label' => 'value',
                        'choice_value'=>'id',
                        'query_builder' => function(EntityRepository $e) use ($field){
                            return $e->createQueryBuilder('f')
                            ->select('f')
                            ->where('f.field = :field')
                            ->setParameter('field', $field['code']);
                            },
                        )
                    );
        }
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array('data_class' => 'App\GptCaveBundle\Entity\Cave'));
    }
}


