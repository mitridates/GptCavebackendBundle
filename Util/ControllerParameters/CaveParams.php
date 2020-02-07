<?php
namespace App\GptCavebackendBundle\Util\ControllerParameters;
use App\GptCaveBundle\Entity\Cave;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * findmemo(CA0680), descriptionmemo(CA0530)
 * @internal CA0070:70 name pasa a Cave
 * @@internal publishablelandunit(CA0439) (fieldvaluecodes [441, 253, 254, 255, 256]) y CA0245 Cave exact position pasan a CA0000
 * @@internal tablas en http://www.uisic.uis-speleo.org  entrancedescriptionmemo(CA0534)
 */
class CaveParams extends CommonParams
{
    public function __construct(array $params, TranslatorInterface $translator)
    {
        parent::__construct('cave', $params, $translator);
    }

    /**
     * @TODO onetomany:
     *      CA0536 Entrance close-up photos
     *      CA0537 Entrance long-shot photos
     *      CA0538 Entrance number-tag photos
     *      CA0540 Cave photos
     * @TODO onetoone
     *      'survey',
     *      'updater',
     *      'available'
     *      Entrance description (memo)
     *      CA0447: Cave latest update
     */
    private $relationship = array(
        'onetomany'=>[ 'access', 'cavetype','comment','content', 'crossreference','descriptionline','damage', 'decoration',
            'development', 'difficulty','direction','discovery','equipment','excluded', 'entranceft','entrancedev','entranceline',
            'grid','hazard', 'importance', 'name', 'otherdbid', 'previousnumber', 'pitch', 'prospect', 'protection', 'reference',
            'rocktype', 'specie', 'surfaceuse', 'todo', 'use', 'widestmap'
        ],
        'onetoone'=>[
            'environment', 'management', 'howtofind', 'description', 'history'
        ],
        'partial'=>[
            'identity', 'coordinates', 'coarse'
        ]
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
     * Get createForm() parameters
     * @param Cave $cave
     * @param string $name Partial form type name
     * @return array
     */
    public function createPartialform(Cave $cave, $name): array
    {
        $class =   sprintf('%s\%s', 'App\GptCavebackendBundle\Form\Type\Cave', 'EditCavepartial'.  ucfirst($name)."Type");
        return [
            $class, $cave,
                ['attr'=> ['id'=>'edit-partial-'.$name.'-'.$cave->getCaveid()],
                    //'translator'=>$this->controllerParams->getTranslator()
                ]
            ];
    }

    /**
     * Get createForm() parameters
     * @param Cave $cave
     * @param string $name Partial form type name
     * @return array
     */
    public function createOnetooneform(Cave $cave, $name): array
    {
        $type =   sprintf('%s\%s', 'App\GptCavebackendBundle\Form\Type\Cave', 'Edit'.  ucfirst($name)."Type");
        $class = sprintf('%s%s', 'App\GptCaveBundle\Entity\Cave',$name);
        return [
            $type, $cave->{'getCave'.$name}() ?? new $class($cave),
            ['attr'=> ['id'=>'edit-onetoone-'.$name.'-'.$cave->getCaveid()],
                'translator'=>$this->getTranslator()
            ]
        ];
    }

    /**
     * Get createForm() parameters
     * @param Cave $cave
     * @param string $name Partial form type name
     * @param int|null $sequence
     * @param ObjectManager|null $em
     * @return array
     */
    public function createManytooneform(Cave $cave, $name, $sequence, $em): array
    {
        $type =   sprintf('%s\%s', 'App\GptCavebackendBundle\Form\Type\Cave', 'Edit'.  ucfirst($name)."Type");
        $class = sprintf('%s%s', 'App\GptCaveBundle\Entity\Cave',$name);
        $entity = ($sequence && $em)? $em->getRepository($class)->findOneBy(['cave'=>$cave->getCaveid(), 'sequence'=>$sequence]) : new $class($cave);
        return [
            $type, $entity,
            ['attr'=> ['id'=>'edit-onetomany-'.$name.'-'.$cave->getCaveid()],
                'translator'=>$this->getTranslator()
            ]
        ];
    }

}