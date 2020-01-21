<?php
namespace App\GptCavebackendBundle\EventListener;
use App\GptCavebackendBundle\Controller\AreaController;
use App\GptCavebackendBundle\Controller\ArticleController;
use App\GptCavebackendBundle\Controller\CaveController;
use App\GptCavebackendBundle\Controller\MapController;
use App\GptCavebackendBundle\Controller\MapserieController;
use App\GptCavebackendBundle\Controller\OrganisationController;
use App\GptCavebackendBundle\Controller\PersonController;
use App\GptCavebackendBundle\Controller\SpecieController;
use App\GptCavebackendBundle\Repository\SetupBackendRepository;
use App\GptCavebackendBundle\Repository\SysparamBackendRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\RouterInterface;

class SysparamSetupSubscriber implements EventSubscriberInterface
{
    /**
     * @var SysparamBackendRepository
     */
    private $sysparamBackendRepository;
    /**
     * @var SetupBackendRepository $setupBackendRepository
     */
    private $setupBackendRepository;
    /**
     * @var RouterInterface
     */
    private $routerInterface;

    /**
     * @param SysparamBackendRepository $sysparamBackendRepository
     * @param SetupBackendRepository $setupBackendRepository
     * @param RouterInterface $routerInterface
     * @throws NonUniqueResultException
     */
    public function __construct(SysparamBackendRepository $sysparamBackendRepository,  SetupBackendRepository $setupBackendRepository, RouterInterface $routerInterface)
    {
        $this->setupBackendRepository= $setupBackendRepository;
        $this->sysparamBackendRepository = $sysparamBackendRepository;
        $this->routerInterface = $routerInterface;
    }


    /**
     * @inheritDoc
     */
    public function onKernelController(ControllerEvent $event)
    {
        if (!$event->isMasterRequest()) return;

        $controller = $event->getController();
        $className = get_class($controller[0]);

        $dashboardController = [
            AreaController::class,
            ArticleController::class,
            CaveController::class,
            MapController::class,
            MapserieController::class,
            OrganisationController::class,
            PersonController::class,
            SpecieController::class
        ];

        if(!in_array($className, $dashboardController)) return;

        //minimun requirements
        if($this->setupBackendRepository->countOrganisation()==0 || $this->setupBackendRepository->countCountry()==0)
        {
            $event->setController(function (){
                return new RedirectResponse($this->routerInterface->generate('cave_backend_setup_index'));
            });
        }

        //sysparam requirements
        $sysparams = $this->sysparamBackendRepository->getSystemParametersValues(['organisationdbm', 'country']);

        if(empty($sysparams) || $sysparams['organisationdbm']==null || $sysparams['country']==null)
        {
            $event->setController(function (){
                return new RedirectResponse($this->routerInterface->generate('cave_backend_sysparam_edit'));
            });
        }
        return;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::CONTROLLER => array(array('onKernelController', 15)),
        );
    }
}