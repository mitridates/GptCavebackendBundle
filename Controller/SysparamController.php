<?php
namespace App\GptCavebackendBundle\Controller;
use App\GptCavebackendBundle\{Form\Type\Sysparam\SysparamType,
    Model\CaveExceptionInteface,
    Repository\SysparamBackendRepository,
    Util\ControllerParameters\SysparamParams};
use App\GptCaveBundle\Entity\Sysparam;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\{DependencyInjection\ParameterBag\ParameterBagInterface,
    HttpFoundation\JsonResponse,
    HttpFoundation\Request,
    HttpFoundation\Response,
    Routing\Annotation\Route};
use Symfony\Contracts\Translation\TranslatorInterface;

class SysparamController extends AbstractController
{

    /**
     * @var SysparamParams
     */
    private $controllerParams;

    /**
     * @param TranslatorInterface $translator
     * @param ParameterBagInterface $params
     */
    public function __construct(TranslatorInterface $translator, ParameterBagInterface $params)
    {
        $this->controllerParams = new SysparamParams($params->get('cave_backend'), $translator);

    }

    /**
     * Crea/Edita el registro único
     *
     * @Route("/sysparam",
     *     name="cave_backend_sysparam_edit",
     *     methods={"GET","POST"})
     * @param Request $request
     * @param SysparamBackendRepository $sysparamBackendRepository
     * @return Response|JsonResponse
     * @throws NonUniqueResultException
     */
    public function editAction(Request $request, SysparamBackendRepository $sysparamBackendRepository)
    {
        $sysparam=  $sysparamBackendRepository->getSystemParameters() ?? null;
        $arrayParams= $this->controllerParams->editParams($sysparam);
        $form = $this->createForm(SysparamType::class, $sysparam ?? new Sysparam(), array('attr'=> ['id'=>'cave_sysparam_form'],'parameters'=>$arrayParams)
        )->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $em = $this->getDoctrine()->getManager();
            $entity = $form->getData();
            try{
                $em->persist($entity);
                $em->flush();
                $em->clear();
            }catch (\Exception $ex){
                if($ex instanceof CaveExceptionInteface){
                    $this->addFlash('danger', $this->get('translator')->trans($ex->getMessageKey(), $ex->getMessageData()));
                }else{
                    $this->addFlash('danger', $ex->getMessage());
                }
            }
        }

        return $this->render('@GptCavebackend/content/sysparam/edit.html.twig', array(
            'arrayParams'=>$arrayParams,
            'form' => $form->createView(),
            'sysparam'=>$sysparam
        ));
    }

}