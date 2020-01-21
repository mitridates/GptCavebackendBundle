<?php
namespace App\GptCavebackendBundle\EventListener;
use App\GptCavebackendBundle\Repository\SysparamBackendRepository;
use App\GptCaveBundle\Entity\Sysparam;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LocaleSubscriber implements EventSubscriberInterface
{
    /**
     * @var string Symfony global %kernel.default_locale%
     */
    private $defaultLocale;

    /**
     * @var SysparamBackendRepository
     */
    private $sysparamBackendRepository;
    /**
     * @var array
     */
    private $appLocale;

    /**
     * @param ParameterBagInterface $parameterBag
     */
        public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->defaultLocale = $parameterBag->get('cave_backend')['languages'][0] ?? $parameterBag->get('kernel.default_locale');
    }

    /**
     * @inheritDoc
     */
    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();

        if (!$request->hasPreviousSession()) {
            return;
        }

        // try to see if the locale has been set as a _locale routing parameter
        if ($locale = $request->attributes->get('_locale'))
        {
            $request->getSession()->set('_locale', $locale);
        }

        // if no explicit locale has been set on this request, use one from the session
        $request->setLocale($request->getSession()->get('_locale', $this->defaultLocale));
        return;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return array(
            // must be registered before the default Locale listener
            KernelEvents::REQUEST => array(array('onKernelRequest', 15)),
        );
    }
}