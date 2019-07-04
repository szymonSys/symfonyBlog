<?php
/**
 * Subscription controller.
 */

namespace App\Controller;


use App\Entity\Article;
use App\Entity\User;
use App\Repository\ArticleRepository;
use App\Repository\UserRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SubscriptionController.
 *
 * @Route("/subscriptions")
 */

class SubscriptionController extends AbstractController
{
    /**
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @param UserRepository $repository
     * @return Response
     *
     * @Route(
     *     "/",
     *     name="subscriptions_index",
     * )
     */
    public function index(Request $request, PaginatorInterface $paginator, ArticleRepository $repository): Response
    {
        $subscriber = $this->getUser();

        if(!count($subscriber->getFollowedAuthors())){
            return $this->render('subscription/index.html.twig');
        }

        $pagination = $paginator->paginate(
            $repository->findAllByFollowed($subscriber),
            $request->query->getInt('page', 1),
            Article::NUMBER_OF_ITEMS
        );

        return $this->render(
            'subscription/index.html.twig',
            ['pagination' => $pagination]
        );
    }

    /**
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @param UserRepository $repository
     * @return Response
     *
     * @Route(
     *     "/authors",
     *     name="subscriptions_followed",
     * )
     */
    public function showFollowed(Request $request, PaginatorInterface $paginator, UserRepository $repository): Response
    {
        $subscriber = $this->getUser();

        if(!count($subscriber->getFollowedAuthors())){
            return $this->render('subscription/followed.html.twig');
        }

        $pagination = $paginator->paginate(
            $repository->findFollowedAuthors($subscriber),
            $request->query->getInt('page', 1),
            User::NUMBER_OF_ITEMS
        );

                return $this->render(
                    'subscription/followed.html.twig',
                    ['pagination' => $pagination]
                );
    }

    /**
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @param UserRepository $repository
     * @return Response
     *
     * @Route(
     *     "/followers",
     *     name="subscriptions_followers",
     * )
     */
    public function showFollowers(Request $request, PaginatorInterface $paginator, UserRepository $repository): Response
    {
        $subscriber = $this->getUser();

        if(!count($subscriber->getFollowers())){
            return $this->render('subscription/followers.html.twig');
        }

        $pagination = $paginator->paginate(
            $repository->findFollowers($subscriber),
            $request->query->getInt('page', 1),
            User::NUMBER_OF_ITEMS
        );


        return $this->render(
            'subscription/followers.html.twig',
            ['pagination' => $pagination]
        );
    }

    /**
     * @param UserRepository $repository
     * @param int $id
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @Route(
     *     "/{id}/follow",
     *     requirements={"id": "[1-9]\d*"},
     *     name="subscription_follow",
     * )
     */
    public  function follow(UserRepository $repository, int $id): Response
    {
        $author = $repository->find($id);

        if($this->getUser()) {
            $subscriber = $this->getUser();
            $followedAuthors = $subscriber->getFollowedAuthors();
            $isSubscribed = false;
            foreach ($followedAuthors as $followed) {
                if ($followed === $author) {
                    $isSubscribed = true;
                    break;
                }
            }

            if(!$isSubscribed) {
                $subscriber->addFollowedAuthor($author);
                $repository->save($subscriber);
                $this->addFlash('success', 'message.followed_created_successfully');
            }
        }

        return $this->redirectToRoute(
            'author_view',
            [
                'id' => $id,
                'firstName' => $author->getFirstName(),
            ]
        );
    }

    /**
     * @param UserRepository $repository
     * @param int $id
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @Route(
     *     "/{id}/unfollow",
     *     requirements={"id": "[1-9]\d*"},
     *     name="subscription_unfollow",
     * )
     */
    public function unfollow(UserRepository $repository, int $id): Response
    {
        $author = $repository->find($id);

        if($this->getUser()) {
            $subscriber = $this->getUser();
            $followedAuthors = $subscriber->getFollowedAuthors();
            $isSubscribed = false;
            foreach ($followedAuthors as $followed) {
                if ($followed === $author) {
                    $isSubscribed = true;
                    break;
                }
            }

            if($isSubscribed) {
                $subscriber->removeFollowedAuthor($author);
                $repository->save($subscriber);
                $this->addFlash('success', 'message.followed_deleted_successfully');
            }
        }

        return $this->redirectToRoute(
            'author_view',
            [
                'id' => $id,
                'firstName' => $author->getFirstName(),
            ]
        );
    }

}


