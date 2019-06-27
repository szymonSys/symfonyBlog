<?php
/**
 * Article controller.
 */

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Form\ArticleType;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use App\Repository\ArticleRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ArticleController.
 *
 * @Route("/article")
 */

class ArticleController extends AbstractController
{

    /**
     * Index action.
     *
     * @param \App\Repository\ArticleRepository         $repository Repository
     * @param \Symfony\Component\HttpFoundation\Request $request    HTTP request
     * @param \Knp\Component\Pager\PaginatorInterface   $paginator  Paginator
     *
     * @return \Symfony\Component\HttpFoundation\Response HTTP response
     *
     * @Route(
     *     "/",
     *     name="article_index",
     * )
     */
    public function index(Request $request, ArticleRepository $repository, PaginatorInterface $paginator): Response
    {
        $pagination = $paginator->paginate(
            $repository->queryAll(),
            $request->query->getInt('page', 1),
            Article::NUMBER_OF_ITEMS
            );

        return $this->render(
            'article/index.html.twig',
            ['pagination' => $pagination]
        );
    }

    /**
     * View action.
     *

     * @param \App\Repository\ArticleRepository $repository Article repository
     * @param int                            $id         Element Id
     *
     * @return \Symfony\Component\HttpFoundation\Response HTTP response
     *
     * @Route(
     *     "/{id}",
     *     methods={"GET", "POST"},
     *     name="article_view",
     *     requirements={"id": "[1-9]\d*"},
     * )
     */
    public function view(Request $request, ArticleRepository $articleRepository, CommentRepository $commentRepository, int $id): Response
    {
        $article = $articleRepository->find($id);
        $form = null;

        if($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $comment = new Comment();

            $form = $this->createForm(CommentType::class, $comment);
            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()) {

                $comment->setArticle($article);
                $comment->setAuthor($this->getUser());
                $commentRepository->save($comment);

                $this->addFlash('success', 'message.created_successfully');

                return $this->redirectToRoute('article_view', ['id' => $id]);
            }
        }

        return $this->render(
            'article/view.html.twig',
            [
                'article' => $article,
                'comments' => $commentRepository->findForArticle($id),
                'comment_form' => is_null($form) ? null : $form->createView()
            ]
        );
    }

    /**
     * New action.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request    HTTP request
     * @param \App\Repository\ArticleRepository            $repository Article repository
     *
     * @return \Symfony\Component\HttpFoundation\Response HTTP response
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @Route(
     *     "/new",
     *     methods={"GET", "POST"},
     *     name="article_new",
     * )
     */
    public function new(Request $request, ArticleRepository $repository): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $article->setAuthor($this->getUser());
            $repository->save($article);

            $this->addFlash('success', 'message.created_successfully');

            return $this->redirectToRoute('article_index');
        }

        return $this->render(
            'article/new.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * Edit action.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request    HTTP request
     * @param \App\Repository\ArticleRepository            $repository Article repository
     * @param int                            $id         Element Id
     *
     * @return \Symfony\Component\HttpFoundation\Response HTTP response
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @Route(
     *     "/{id}/edit",
     *     methods={"GET", "PUT"},
     *     requirements={"id": "[1-9]\d*"},
     *     name="article_edit",
     * )
     */
    public function edit(Request $request, ArticleRepository $repository, int $id):Response
    {
        $article = $repository->find($id);
        $form = $this->createForm(ArticleType::class, $article, ['method' => 'PUT']);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $repository->save($article);

            $this->addFlash('success', 'message.updated_successfully');
            return $this->redirectToRoute('article_index');
        };

        return $this->render(
            'article/edit.html.twig',
            [
                'form' => $form->createView(),
                'article' => $article,
            ]
        );
    }

    /**
     * Delete action.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request    HTTP request
     * @param \App\Repository\ArticleRepository            $repository Article repository
     * $category = $repository->find($id);
     *
     * @return \Symfony\Component\HttpFoundation\Response HTTP response
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @Route(
     *     "/{id}/delete",
     *     methods={"GET", "DELETE"},
     *     requirements={"id": "[1-9]\d*"},
     *     name="article_delete",
     * )
     */
    public function delete(Request $request, ArticleRepository $repository, int $id):Response
    {
        $article = $repository->find($id);

        $form = $this->createForm(FormType::class, $article, ['method' => 'DELETE']);
        $form->handleRequest($request);

        if ($request->isMethod('DELETE') && !$form->isSubmitted()) {
            $form->submit($request->request->get($form->getName()));
        }

        if($form->isSubmitted() && $form->isValid()) {
            $repository->delete($article);

            $this->addFlash('success', 'message.deleted_successfully');
            return $this->redirectToRoute('article_index');
        }

        return $this->render(
            'article/delete.html.twig',
            [
                'form' => $form->createView(),
                'article' => $article,
            ]
        );
    }
}


