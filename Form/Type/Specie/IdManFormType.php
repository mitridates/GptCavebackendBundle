<?php
namespace App\GptCavebackendBundle\Form\Type\Specie;
use App\Cave\LibBundle\Exception\TranslatableException;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\GptCaveBundle\Entity\Organisation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
/**
 * Carga el formulario manual. Permite generar un ID con una organización distinta a la administradora de la base de datos.<br>
 * ID: ISO 2-letter country code + 3-letter organisation code + 5-right-justified integer serial number, zero-filled.<br>
 *
 * Su objetivo es el de "importar registros manualmente" y evitar duplicidad  en posteriores actualizaciónes.
 *  - La organizaci&oacute;n padre tiene que est&aacute;r habilitada como generadora de registros.
 *  - La organizaci&oacute;n se selecciona. Los n&uacute;meros de serie son introducidos manualmente.
 *  - Si el número de serie si existe tirará un error. No es autoincremental.
 * @package App\GptCavebackendBundle\Form\Type\Specie
 */
class IdManFormType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $defaults
     */
    public function buildForm(FormBuilderInterface $builder, array $defaults)
    {
        list($xparams, $adminorg) = array_values($defaults['parameters']);

        $builder
            ->add('name', NULL, array('attr'=>array('code_id'=>282),'required' => true) )
            ->add('commonname', NULL, array('attr'=>array('code_id'=>606),'required' => true) )
            ->add('organisation',  EntityType::class, array(
            'class'=>'GptCaveBundle:Organisation',
            'attr'=>array('class'=>'select2', 'style'=>'width:100%'),
            'required' => true,
            'mapped'=>false,
            'choice_label' => 'name',
            'choice_value'=>'organisationid',
            'query_builder' => function(\Doctrine\ORM\EntityRepository $e)  use ($adminorg){
                    $qb =  $e->createQueryBuilder('p')
                        ->select('p')
                        ->where('p.idgenerator = :type')
                        ->setParameter(':type', 1);
                    /**
                     * la organización administradora distinta de la principal
                     */
                    if($adminorg instanceof Organisation){
                        $qb->andWhere('p.organisationid != :id')
                            ->setParameter('id', $adminorg->getOrganisationid());
                    }
                    return $qb;
                },
            ))
            ->add('serial', NULL, array(
                'required' => true,
                'mapped'=>false
                ))
            ->addEventListener(
                FormEvents::POST_SUBMIT,
                array($this, 'onPostSubmit')
            );
    }

    /**
     * Estamos importando un ID generado por OTRA organización generadora de registros.
     *  - La Organización EXISTE en la base de datos
     *  - El serial DEBE ser validado
     *  - El ID ya existe, sólo VALIDAMOS la parte numérica del sufijo
     *  - El controlador le dice a Doctrine que utilice AssignedGenerator
     * @param FormEvent $event
     * @throws TranslatableException
     * @throws \ReflectionException
     */
    public function onPostSubmit(FormEvent $event)
    {
        $entity = $event->getData();
        $form = $event->getForm();
        $suffix= $form["serial"]->getData();
        $generator = $form["organisation"]->getData();

        if (!preg_match("/^[0-9]{5}$/", $suffix)) {
            throw new TranslatableException('invalid.serial',
                TranslatableException::FORMATO_ERRONEO,
                NULL,
                ['%entity%' => get_class($entity), '%string%' => $suffix],
                'caveerrors'
            );
        }
        $id =   substr($generator->getOrganisationid(), 0,5).$suffix;
        $reflection = new \ReflectionClass($entity);
        $property = $reflection->getProperty('specieid');
        $property->setAccessible(true);
        $property->setValue($entity, $id);

    }
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\GptCaveBundle\Entity\Specie',
             'parameters'=>[]
        ));
    }
}


