<?php
namespace App\GptCavebackendBundle\Util\ControllerParameters;
use App\GptCaveBundle\Entity\Map;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Contracts\Translation\TranslatorInterface;

class MapParams extends  CommonParams
{
    public function __construct(array $params, TranslatorInterface $translator)
{
    parent::__construct('map', $params, $translator);
}

    private $relationship = array(
        'onetomany'=>[
            'comment',
            'citation',/*'citation==publication*/
            'furtherpc',
            'furthergc',
            'surveyor',
            'drafter',
            'cave'
        ],
        'onetoone'=>[
            'details',
            'controller',
            'updater'
        ],
        'partial'=>[]
    );

    /**
     * @inheritDoc
     */
    protected function init(): void
    {
        $this->parametersbag['relationship']= $this->relationship;
        parent::init();
    }

    /**
     * Get createForm() parameters. No hay partial de Map, pero mantenemos la función
     * @param Map $map
     * @param string $name form type name suffix
     * @return array
     */
    public function createPartialform(Map $map, $name): array
    {
        $prefix = $name=='map' ? 'EditMappartial': 'Edit';
        $class =   sprintf('%s\%s', 'App\GptCavebackendBundle\Form\Type\Map', $prefix.  ucfirst($name)."Type");
        return [
            $class, $map,
            ['attr'=> ['id'=>'edit-partial-'.$name.'-'.$map->getMapid()],
                //'translator'=>$this->controllerParams->getTranslator()
            ]
        ];
    }

    /**
     * Get createForm() parameters
     * @param Map $map
     * @param string $name form type name suffix
     * @return array
     */
    public function createOnetooneform(Map $map, $name): array
    {
        $type =   sprintf('%s\%s', 'App\GptCavebackendBundle\Form\Type\Map', 'Edit'.  ucfirst($name)."Type");
        $class = sprintf('%s%s', 'App\GptCaveBundle\Entity\Map',$name);
        return [
            $type, $map->{'getMap'.$name}() ?? new $class($map),
            ['attr'=> ['id'=>'edit-onetoone-'.$name.'-'.$map->getMapid()],
                'translator'=>$this->getTranslator()
            ]
        ];
    }

    /**
     * Get createForm() parameters
     * @param Map $map
     * @param string $name form type name suffix
     * @param int|null $sequence
     * @param ObjectManager|null $em
     * @return array
     */
    public function createManytooneform(Map $map, $name, $sequence, $em): array
    {
        $type =   sprintf('%s\%s', 'App\GptCavebackendBundle\Form\Type\Map', 'Edit'.  ucfirst($name)."Type");
        $class = sprintf('%s%s', 'App\GptCaveBundle\Entity\Map',$name);
        $entity = ($sequence && $em)? $em->getRepository($class)->findOneBy(['cave'=>$map->getMapid(), 'sequence'=>$sequence]) : new $class($map);
        return [
            $type, $entity,
            ['attr'=> ['id'=>'edit-onetomany-'.$name.'-'.$map->getMapid()],
                'translator'=>$this->getTranslator()
            ]
        ];
    }

}