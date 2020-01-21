<?php
namespace App\GptCavebackendBundle\Controller;
use App\GptCavebackendBundle\Form\Type\Organisation\EditOrganisationType;
use App\GptCavebackendBundle\Form\Type\Setup\CountrysetupType;
use App\GptCavebackendBundle\Form\Type\Setup\OrganisationsetupType;
use App\GptCavebackendBundle\Form\Type\Sysparam\EditSysparamType;
use App\GptCavebackendBundle\Repository\SetupBackendRepository;
use App\GptCavebackendBundle\Service\BackendParams\SysparamParams;
use App\GptCavebackendBundle\Util\ControllerParameters\SetupParams;
use App\GptCaveBundle\Entity\Organisation;
use App\Cave\LibBundle\Format\Select2;
use App\GptGeonamesBundle\Entity\Country;
use App\GptGeonamesdumpBundle\Model\LoaderInteface;
use App\GptGeonamesdumpBundle\Util\FileHelper;
use App\GptGeonamesdumpBundle\Util\RepositoryHelper;
use Doctrine\ORM\Id\AssignedGenerator;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class SetupController extends AbstractController
{
    /**
     * @var SetupParams
     */
    private $controllerParams;

    /**
     * @param TranslatorInterface $translator
     * @param ParameterBagInterface $params
     */
    public function __construct(TranslatorInterface $translator, ParameterBagInterface $params)
    {
        $this->controllerParams = new SetupParams('setup', $params->get('cave_backend'), $translator);
    }

    /**
     * Setup index
     *
     * @Route("/setup",
     *     name="cave_backend_setup_index")
     * @return Response
     * @throws NonUniqueResultException
     */
    public function indexAction()
    {
        return $this->render('@GptCavebackend/content/setup/index.html.twig',
            array(
                'arrayParams'=>$this->controllerParams->indexParams($this->getDoctrine()->getManager())
            ));
    }

    /**
     * Country loader
     *
     * @Route("/setup/country",
     *     name="cave_backend_setup_country")
     * @param Request $request
     * @param ParameterBagInterface $parameterBag
     * @return Response
     * @throws NonUniqueResultException
     * @throws \Exception
     */
    public function countryAction(Request $request, ParameterBagInterface $parameterBag)
    {
        $form = $this->createForm(CountrysetupType::class, null)->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $country = $form->get('country')->getData();
            $deep = $form->get('deep')->getData();
            $parameters = $parameterBag->get('gpt_geonames_dump');
            $parameters['dump']=[
                'country' => [$country],
                'admin1' => [$country],
                'admin2' => [$country],
                'admin3' => [$country]
                ];
            $loaders = array_keys($parameters['dump']);
            $repositoryHelper = new RepositoryHelper($this->getDoctrine()->getManager());
            $fileHelper = new FileHelper($parameters['config']['webdir'], $parameters['config']['localdir'], $parameters['config']['tmpdir']);
            $loaderNamespace = 'App\GptGeonamesdumpBundle\Loader\\';

            foreach($loaders as $key => $loader) {

                if($key==0) $fileHelper->createTemporaryDir();
                $class = $loaderNamespace.ucfirst($loader).'Loader';

                /**
                 * @var LoaderInteface $loader
                 */
                $loader = new $class($fileHelper, $repositoryHelper, $parameters, false);
                $loader->load();

                if($key === count($loaders)-1 || $deep-1 === $key ){
                    $parameters['config']['rmdir'] ?: $fileHelper->deleteTemporaryDir();
                    break;
                };
            }

        }

        return $this->render('@GptCavebackend/content/setup/countrySetup.html.twig',
            array(
                'arrayParams'=>$this->controllerParams->countryParams($this->getDoctrine()->getManager()),
                'form'=> $form->createView(),
            ));
    }

    /**
     * Delete Country if not used
     *
     * @Route("/setup/country/delete/{countryCode}",
     *     name="cave_backend_setup_delete_country")
     * @param Request $request
     * @param string $countryCode
     * @return Response
     */
    public function deletecountryAction(Request $request, string $countryCode)
    {
        $em = $this->getDoctrine()->getManager();
        $country = $em->getReference(Country::class, $countryCode);
        if($country!=null){
            try{
                (new SetupBackendRepository($this->getDoctrine()->getManager()))->deleteCountry($country);
            }catch (\Exception $e){
                $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
            }
        }
        return $this->redirectToRoute('cave_backend_setup_country');
    }


    /**
     * Setup Country
     *
     * @Route("/setup/organisation",
     *     name="cave_backend_setup_organisation")
     * @param Request $request
     * @return Response
     * @throws NonUniqueResultException
     */
    public function organisationAction(Request $request)
    {
        $arrayParams= $this->controllerParams->organisationParams($this->getDoctrine()->getManager());
        $num = $arrayParams['page']['organisations'];

        if($num == 0){
            return $this->redirectToRoute('cave_backend_setup_index');
        }elseif ($num == 1){
            $organisation = (new SetupBackendRepository($this->getDoctrine()->getManager()))->getOrganisation();
        }else{
            $organisation = new Organisation();
        }

        $form = $this->createForm(OrganisationsetupType::class, $organisation)->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $organisation = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $metadata = $em->getClassMetaData(get_class($organisation));
            $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);
            $metadata->setIdGenerator(new AssignedGenerator());
            $em->persist($organisation);
            $em->flush();
            return $this->redirectToRoute('cave_backend_setup_index');
        }

        return $this->render('@GptCavebackend/content/setup/organisationSetup.html.twig',
            array(
                'arrayParams'=>$arrayParams,
                'form'=> $form->createView(),
            ));
    }

    /**
     * Setup Fielddefinition
     *
     * @Route("/setup/fielddefinition",
     *     name="cave_backend_setup_fielddefinition")
     * @return Response
     * @throws \Doctrine\DBAL\DBALException
     */
    public function loadfielddefinitionAction()
    {
        // Bundle to manage file and directories
        $finder = (new Finder())->files()
            ->name('*Fielddefinition.sql')
            ->in(__DIR__.'/../Resources/Sql')
            ->sortByModifiedTime();
        if($finder->hasResults())
        {
            $this->get('doctrine')->getConnection();
            $this->get('doctrine')->getConnection()->prepare(
               $finder->getIterator()->current()->getContents()
           )->execute();
        }
        return $this->redirectToRoute('cave_backend_setup_index');
    }
}