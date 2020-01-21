<?php
namespace App\GptCavebackendBundle\Controller;
use App\GptCavebackendBundle\Service\BackendParams\FielddefinitionParams;
use App\GptCavebackendBundle\Util\ControllerParameters\CommonParams;
use App\GptCaveBundle\Entity\Fielddefinitionlang;
use App\Cave\LibBundle\Doctrine\Filter\Filter;
use Doctrine\ORM\NonUniqueResultException;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * test
 */
class TestController extends AbstractController
{
    /**
     * @var CommonParams
     */
    private $controllerParams;

    /**
     * @param TranslatorInterface $translator
     * @param ParameterBagInterface $params
     */
    public function __construct(TranslatorInterface $translator, ParameterBagInterface $params)
    {
        $this->controllerParams = new CommonParams('test', $params->get('cave_backend'), $translator);
    }

    /**
     * test
     * @Route("/test/populateselectjson",
     *      name="cave_backend_test_populateselectjson",
     *     methods={"GET","POST"}
     *     )
     * @return Response
     */
    public function testAction()
    {
        return $this->render('@GptCavebackend/playground/populateselectjson.html.twig', [
            'arrayParams'=>$this->controllerParams->getParametersbag(),
        ]);
    }
}
