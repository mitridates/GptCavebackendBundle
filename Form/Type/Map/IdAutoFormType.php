<?php
namespace App\GptCavebackendBundle\Form\Type\Map;
use App\GptCaveBundle\Entity\Map;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
/**
 * Carga el formulario automático. Permite generar un ID autoincremental utilizando la "organización administradora del sistema".<br>
 * ID: ISO 2-letter country code + 3-letter organisation code + 5-right-justified integer serial number, zero-filled.<br>
 *
 * Su objetivo es el de "crear nuevos registros"  localmente sobre recursos que no han sido documentados para
 * posteriormente ser exportados a una base de datos nacional u otras bases de datos sin crear duplicidades.
 *  - La organizaci&oacute;n ha de ser creada por el administrador del sistema.
 *  - Country code y organisation code se toman de la organización administradora del sistema
 *  - El número de serie es autoincremental 00000-99999.
 * @package App\GptCavebackendBundle\Form\Type\Map
 */
class IdAutoFormType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        list($xparams, $adminorg) = array_values($options['parameters']);

        $builder->add('name', NULL, array('attr'=>array('code_id'=>202),'required' => true) );

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($adminorg) {
            /**
             * @var Map $entity
             */
            $entity = $event->getData();
            $entity->setGeneratedby($adminorg);
        });        
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\GptCaveBundle\Entity\Map',
             'parameters'=>[]
        ));
    }
}


