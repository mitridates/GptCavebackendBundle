<?php
namespace App\GptCavebackendBundle\Controller;
use App\GptCavebackendBundle\Repository\FielddefinitionBackendRepository;
use App\GptCavebackendBundle\Util\ControllerParameters\FielddefinitionParams;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;


class AjaxController extends AbstractController
{
    /**
     * @var FielddefinitionParams
     */
    private $fielddefinitionParams;

    /**
     * @param TranslatorInterface $translator
     * @param ParameterBagInterface $params
     */
    public function __construct(TranslatorInterface $translator, ParameterBagInterface $params)
    {
        $this->fielddefinitionParams = new FielddefinitionParams($params->get('cave_backend'), $translator);
    }


    /**
     * Retorna los objetos necesarios para el popover sobre Fieldefinition
     * @Route("/hxr/fielddefinition/{code}",
     *      name="cave_backend_xhr_fielddefinition",
     *     methods={"GET","POST"}
     *     )
     * @param FielddefinitionBackendRepository $repository
     * @param Request $request
     * @param null $code
     * @return Response
     * @throws NonUniqueResultException
     */
    public function fielddefinitionAction(FielddefinitionBackendRepository $repository, Request $request, $code= null)
    {
        if(null==$code){
            return new Response('C&oacute;digo "'.$code.'" inv&aacute;lido');
        }

        $fielddefinition = $repository->getTranslationORFielddefinition($code, $request->getLocale());

        if(null==$fielddefinition){
            return new Response('C&oacute;digo "'.$code.'" no encontrado');
        }

        return $this->render('@GptCavebackend/partial/page/field_value_code_popover.html.twig', array(
        'fielddefinition'   => $fielddefinition,
        'arrayParams'=>$this->fielddefinitionParams->getParametersbag()
        ));

    }
}
