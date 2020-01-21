<?php
namespace App\GptCavebackendBundle\Twig\Extension;
use App\GptCavebackendBundle\Repository\SysparamBackendRepository;
use App\GptCaveBundle\Entity\Organisation;
use App\GptCaveBundle\Entity\Sysparam;
use Doctrine\ORM\NonUniqueResultException;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * System parameters in twig
 *
 * @author mitridates
 */
class CavesystemparametersExtension extends AbstractExtension
{
    /**
     * @var Sysparam|null Current system parameters
     */
    private $sysparam;

    /**
     * @param SysparamBackendRepository $sysparamBackendRepository
     * @throws NonUniqueResultException
     */
    public function __construct(SysparamBackendRepository $sysparamBackendRepository)
    {
        $this->sysparam = $sysparamBackendRepository->getSystemParameters();
    }

    /**
     * @return Sysparam|null
     */
    public function getSystemparameters(): ?Sysparam
    {
        return $this->sysparam;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions() {
        return array(
            new TwigFunction('get_system_parameters',array($this, 'getSystemparameters')),
        );
    }
}