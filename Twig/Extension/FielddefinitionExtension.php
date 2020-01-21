<?php
namespace App\GptCavebackendBundle\Twig\Extension;
use App\GptCavebackendBundle\Repository\FielddefinitionBackendRepository;
use App\GptCaveBundle\Entity\Fielddefinition;
use App\GptCaveBundle\Repository\FielddefinitionRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
/**
 * Fielddefinition in twig
 *
 * @author mitridates
 */
class FielddefinitionExtension extends AbstractExtension
{
    /**
     * @var FielddefinitionRepository
     */
    protected $entityRepository;

    /**
     * @param FielddefinitionBackendRepository $entityRepository
     */
    public function __construct(FielddefinitionBackendRepository $entityRepository)
    {
        $this->entityRepository = $entityRepository;
    }
    /**
     * @TODO: language
     * @param string $field
     * @param string|null $locale
     * @return array
     */
    public function getValuecodes(string $field, string $locale=null): array
    {
        return $this->entityRepository->getFieldvaluecodeRepository()->findBy(['field'=>$field], ['code'=>'ASC']);
    }

    /**
     * Get Fielddefinition by code
     * @param int $code
     * @param null|string $locale
     * @param bool $abbr Get Abbreviated
     * @return string|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getFielddefinitionname(int $code, string $locale=null, bool $abbr=false): ?string
    {

        $result = $this->entityRepository->getFielddefinitionName($code, $locale);

        if(empty($result)) {
          return sprintf('Code %s Not found!', $code);
        }
        if($abbr){
              if(!empty($result['abbreviation'])){
                  if(!empty($result['trans_abbreviation'])){
                      return $result['trans_abbreviation'];
                  }
                  return $result['abbreviation'];
              }
        }
        if(!empty($result['trans_name'])){
            return $result['trans_name'];
        }
        return $result['name'];
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions() : array
    {
        return array(
            new TwigFunction('get_fielddefinition_name',array($this, 'getFielddefinitionname')),
            new TwigFunction('get_valuecodes',array($this, 'getValuecodes'))
        );
    }
}