<?php
namespace App\GptCavebackendBundle\Util;
use App\GptCaveBundle\Entity\Cave;

/**
 * Helper to generate forms. Get mapped Cave entities or Cave related formTypes by name
 *
 * @package App\GptBackendBundle\Service
 */
class CaveControllerUtil
{

    /**
     * @var array Mapping ManyToOne Cave
     * @todo CA0536 Entrance close-up photos, CA0537 Entrance long-shot photos, CA0538 Entrance number-tag photos, CA0540 Cave photos
     */
    public static $MAPPEDMANYTOONE= [
        //CA0018 included in Cave
        'access',//CA0258
        'cavetype',
        'comment',//CA0053
        'content',//CA0072
        'damage',//CA0043
        'decoration',//CA0012
        'development',//CA0011
        'difficulty',//CA0050
        'discovery',//CA0030
        'grid',// Position on maps (CA0241) (uisic.uis-speleo.org) === Cave grid references (kid.caves.org.au)
        'hazard',//CA0052
        'importance',
        'name',
        'pitch',
        'prospect',
        'protection',
        'reference',
        'rocktype',
        'surfaceuse',
        'use',
        'widestmap',
        'previousnumber',
        'crossreference',
        'direction',//CA0680
        'entranceline',//CA0535
        'descriptionline',//CA0525
        'specie',//CA0037 Species found
        'excluded',//CA0075
        'entranceft',
        'entrancedev',
        'otherdbid',//CA0259
        'todo',//uncoded
        'equipment'
    ];

    /**
     * @var array Mapping OneToOne Cave. Entity: Cave{name}
     * @todo survey, updater, available, Entrance description (memo), CA0447: Cave latest update
     */
    public static $MAPPEDONETOONE= [
        'environment',
        'management',
        'howtofind',//CA0257
        'description', //CA0530
        'history'
    ];

    /**
     * Create Cave{$name} instances by suffix
     *
     * @param Cave $cave
     * @param string $name
     * @return object Cave|Cave{name}
     * @throws \UnexpectedValueException
     */
    public static function getInheritClass(Cave $cave, $name)
    {
        if($name == "cave"){
            return $cave;
        }else{
            $class = ENTITYNAMESPACE.'\\'.$name;
            if(!class_exists($class)){
                throw new UnexpectedValueException(sprintf('Unknow class suffix: %s'), $name);
            }
            return new $class($cave);
        }
    }

}

