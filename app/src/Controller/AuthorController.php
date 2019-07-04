<?php
/**
 * Authors controller.
 */

namespace App\Controller;

use App\Entity\Article;
use App\Entity\User;
use App\Form\AuthorType;
use App\Repository\ArticleRepository;
use App\Repository\UserRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AuthorController.
 *
 * @Route("author")
 */
class AuthorController extends AbstractController
{
    /**
     * Index action.
     *
     * @param Request            $request
     * @param UserRepository     $repository
     * @param PaginatorInterface $paginator
     *
     * @return Response
     *
     * @Route(
     *     "/",
     *     name="author_index",
     * )
     */
    public function index(Request $request, UserRepository $repository, PaginatorInterface $paginator): Response
    {
        $pagination = $paginator->paginate(
            $repository->queryAll(),
            $request->query->getInt('page', 1),
            User::NUMBER_OF_ITEMS
        );

        return $this->render(
            'author/index.html.twig',
            ['pagination' => $pagination]
        );
    }

    /**
     * View action.
     *
     * @param Request            $request
     * @param PaginatorInterface $paginator
     * @param int                $id
     *
     * @return Response
     *
     * @Route(
     *     "/{firstName}/{id}",
     *     requirements={"id": "[1-9]\d*"},
     *     name="author_view",
     * )
     */
    public function view(Request $request, ArticleRepository $articleRepository, UserRepository $userRepository, PaginatorInterface $paginator, int $id): Response
    {
        $author = $userRepository->find($id);
        $authorData = $userRepository->findAuthorData($author);
        $pagination = $paginator->paginate(
            $articleRepository->findAllThanUserId($id),
            $request->query->getInt('page', 1),
            Article::NUMBER_OF_ITEMS
        );

        $isSubscribed = false;

        if ($this->getUser()) {
            $loggedUser = $this->getUser();
            $followedAuthors = $loggedUser->getFollowedAuthors();
            foreach ($followedAuthors as $followed) {
                if ($author === $followed) {
                    $isSubscribed = true;
                    break;
                }
            }
        } else {
            $loggedUser = null;
        }

        return $this->render(
            'author/view.html.twig',
            [
                'authorData' => $authorData,
                'isSubscribed' => $isSubscribed,
                'loggedUser' => $loggedUser,
                'pagination' => $pagination,
            ]
        );
    }

    /**
     * @param Request        $request
     * @param UserRepository $repository
     * @param int            $id
     *
     * @return Response
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @Route(
     *     "/{id}/edit",
     *     methods={"GET", "PUT"},
     *     requirements={"id": "[1-9]\d*"},
     *     name="edit_author",
     * )
     */
    public function editAuthor(Request $request, UserRepository $repository, int $id): Response
    {
        $author = $repository->find($id);
        $form = $this->createForm(AuthorType::class, $author, ['method' => 'PUT']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $repository->save($author);
            $this->addFlash('success', 'message.author_updated_successfully');

            return $this->redirectToRoute('author_view', ['id' => $id, 'firstName' => $author->getFirstName()]);
        }

        return $this->render(
            'author/edit.html.twig',
            [
                'form' => $form->createView(),
                'author' => $author,
            ]
        );
    }
}
