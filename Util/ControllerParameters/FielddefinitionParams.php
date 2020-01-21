<?php
namespace App\GptCavebackendBundle\Util\ControllerParameters;

use Symfony\Contracts\Translation\TranslatorInterface;

class FielddefinitionParams extends CommonParams
{
    public function __construct(array $params, TranslatorInterface $translator)
    {
        parent::__construct('fielddefinition', $params, $translator);
    }

    private $choices= array(
        'singlemultivalued'=>
            [
                'S' => 'Single-valued',
                'M' => 'Multi-valued'
            ],
        'coding'=>
            [
                'I' => 'International coding',
                'U' => 'Uncoded',
                'L' => 'Locally coded'
            ],
        'datatype'=>
            [
                'S' => 'Short integer up to 32768',
                'N' => 'Numeric decimals allowed',
                'A' => 'Alphanumeric up to 255 chars long (A1-A255)',
                'D' => 'Date. Accepts only full valid dates',
                'L' => 'Logical. True or False',
                'M' => 'Memo (variable length free text)',
                'B' => 'BLOB (Binary Large Object e.g. a photo image)'
            ],
        'entity'=>
            [
                'AR' => 'Article in a publication',
                'AT' => 'Field or attribute',
                'AV' => 'Field value',
                'CA' => 'Cave or karst feature',
                'EN' => 'Entity',
                'OR' => 'Organisation',
                'PA' => 'Land parcel',
                'PB' => 'Publication',
                'PE' => 'Person',
                'PH' => 'Photograph',
                'PL' => 'Plan or map',
                'PM' => 'Marker (Permanent mark)',
                'PS' => 'Map series',
                'RE' => 'Region or area',
                'RL' => 'Role',
                'RP' => 'Report',
                'SM' => 'Specimen',
                'SP' => 'Species',
                'ST' => 'Site',
                'SU' => 'Subject',
                'SV' => 'Survey',
                'SY' => 'System field',
                'XK' => 'A key-in batch',
                'XL' => 'An upload batch',
                'XU' => 'An update batch'
            ]
    );

    /**
     * @return array
     */
    public function getChoices(): array
    {
        return $this->choices;
    }

    /**
     * @param string $type
     * @return array
     */
    public function getChoiceType(string $type): array
    {
        return $this->choices[$type];
    }

    /**
     * @inheritDoc
     */
    protected function init(): void
    {
        $this->parametersbag['choices']= $this->choices;
        parent::init();
    }
}