<?php
namespace App\GptCavebackendBundle\Controller;
use App\GptCavebackendBundle\Form\Type\Article\ArticleType;
use App\GptCavebackendBundle\Form\Type\Article\ArticlesearchType;
use App\GptCavebackendBundle\Model\CaveExceptionInteface;
use App\GptCavebackendBundle\Repository\ArticleBackendRepository;
use App\GptCavebackendBundle\Util\ControllerParameters\CommonParams;
use App\GptCaveBundle\Entity\Article;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ArticleController extends AbstractController
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
        $this->controllerParams = new CommonParams('article', $params->get('cave_backend'), $translator);
    }

    /**
     * Index search form
     *
     * @Route("/article",
     *     name="cave_backend_article_index")
     * @return Response
     * @throws \Exception
     */
    public function indexAction()
    {
        $form = $this->createForm(ArticlesearchType::class, new Article() , [
            'attr'=> ['id'=>'article_search_form']
        ]);

        return $this->render('@GptCavebackend/content/article/index.html.twig',
            array(
                'arrayParams'=>$this->controllerParams->indexParams(),
                'form'   => $form->createView()
        ));
    }

    /**
     * Search result
     * @Route("/article/ajaxpager",
     *     name="cave_backend_article_ajaxpager",
     *     methods={"GET","POST"})
     * @param ArticleBackendRepository $repository
     * @param Request $request
     * @return Response
     * @throws NonUniqueResultException
     */
    public function ajaxpagerAction(ArticleBackendRepository $repository, Request $request){

        $arrayParams= $this->controllerParams->getParametersbag();

        $page       = $request->get('page', 1);
        $ipp        = $request->get('ipp', $arrayParams['page']['itemsPerPage'] ?? 20);

        $form = $this->createForm(ArticlesearchType::class, new Article())->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $entity = $form->getData();
        }else{
            return $this->render('@GptCavebackend/partial/form/error/all_errors_message.html.twig',
                ['form'=>$form->createView()]
            );
        }

        list($paginator, $result) = $repository->pageByArticle($entity, $page, $ipp);

        return $this->render(
            '@GptCavebackend/content/article/index_ajax.html.twig',
            array(
                'arrayParams'=>$arrayParams,
                'entities' => $result,
                "entity_token"=>$this->container->get('security.csrf.token_manager')->getToken('article_token'),
                'paginator'=>$paginator
            ));
    }

    /**
     * New registry
     *
     * @Route("/article/new",
     *     name="cave_backend_article_new",
     *     methods={"GET","POST"})
     * @param Request $request
     * @return Response
     */
    public function newAction(Request $request)
    {
        $form = $this->createForm(ArticleType::class, new Article())->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            try {
                $article = $form->getData();
                $em = $this->getDoctrine()->getManager();
                $em->persist($article);
                $em->flush();
                $em->clear();
                return $this->redirectToRoute('cave_backend_article_edit', array('id' => $article->getArticleid()));
            }catch (\Exception $ex){
                $ex instanceof CaveExceptionInteface ?
                    $form->addError(new FormError($this->controllerParams->getTranslator()->trans($ex->getMessageKey(), $ex->getMessageData()))) :
                    $form->addError(new FormError($ex->getMessage()));
            }
        }

        return $this->render(
            '@GptCavebackend/content/article/new.html.twig', [
            'arrayParams'=>$this->controllerParams->newParams(),
            'form'=> $form->createView()
        ]);
    }

    /**
     * Edit registry
     *
     * @Route("/article/edit/{id}",
     *     name="cave_backend_article_edit",
     *     methods={"GET","POST"})
     * @param Request $request
     * @param Article $article
     * @return Response
     */
    public function editAction(Request $request, Article $article)
    {
        $form = $this->createForm(ArticleType::class, $article,
            ['attr'=> ['id'=>'edit-article']]
        )->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            try {
                $em = $this->getDoctrine()->getManager();
                $em->persist($article);
                $em->flush();
                $em->clear();
            }catch (\Exception $ex){
                ($ex instanceof CaveExceptionInteface)?
                    $form->addError(new FormError($this->controllerParams->getTranslator()->trans($ex->getMessageKey(), $ex->getMessageData()))) :
                    $form->addError(new FormError($ex->getMessage()));
            }

        }

        return $this->render('@GptCavebackend/content/article/edit.html.twig', array(
            'arrayParams'=>$this->controllerParams->editParams($article->getArticleid(), $article->getName()),
            'form' => $form->createView(),
            'delete_form' => $this->createDeleteForm($article)->createView(),
            'article'=>$article
        ));
    }


    /**
     * Delete
     *
     * @Route("/article/{id}/delete/",
     *     name="cave_backend_article_delete",
     *     methods={"DELETE"})
     * @param Request $request
     * @param Article $article
     * @return RedirectResponse
     */
    public function deleteAction(Request $request, Article $article)
    {
        $form = $this->createDeleteForm($article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $msg= $this->controllerParams->getTranslator()->trans('id.successfully.deleted', array('%id%'=>$article->getArticleid()), 'cavemessages');
            $em = $this->getDoctrine()->getManager();
            try{
                //TODO no debería eliminarse de la base de datos ya que
                // el registro no puede reutilizarse
                $em->remove($article);
                $em->flush();
                $this->addFlash('success', $msg);
            }catch(\Exception $ex){
                $this->addFlash('danger', $ex->getMessage() );
                return $this->redirectToRoute('cave_backend_article_edit', array('id' => $article->getArticleid()));
            }
        }

        return $this->redirectToRoute('cave_backend_article_index');
    }

    /**
     * Delete form
     *
     * @param Article $article
     * @return FormInterface
     */
    private function createDeleteForm(Article $article)
    {
        return $this->createFormBuilder(null, ['attr'=> ['id'=>'article_delete_form']])
            ->setAction($this->generateUrl('cave_backend_article_delete', array(
                'id' => $article->getArticleid()
            )))
            ->setMethod('DELETE')
            ->getForm()
            ;
    }
}