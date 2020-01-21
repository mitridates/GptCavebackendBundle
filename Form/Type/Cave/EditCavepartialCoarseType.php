<?php
namespace App\GptCavebackendBundle\Form\Type\Cave;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditCavepartialCoarseType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $defaults)
    {
        $fields = '676:sheet100;677:sheet25;23:mapsheetnamecoarse;26:gridreferencecoarse;25:grrefqualifiercoarse;'//map
            .'21:latitudecoarse;22:longitudecoarse;28:altitudecoarse;531:orientation;'//Anteriormente en tabla CA00000
            .'441:landunit5;253:landunit4;254:landunit3;255:landunit2;256:landunit1;365:landunit0';//landunit


        foreach(explode(';', $fields) as $el){
            if(!strpos($el,':')) {var_dump ($el);exit;}
            $field = explode(':', $el);
            $arr = ['attr'=>['code_id'=>$field[0]]];
            if($field[1]=='name'){ $arr['required']=true; }
            $builder->add($field[1], NULL, $arr);
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


