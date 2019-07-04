<?php
/**
 * Search Contorller.
 */

namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Repository\TagRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SearchController.
 *
 * @Route("/search")
 */
class SearchController extends AbstractController
{
    /**
     * @param Request           $request
     * @param UserRepository    $userRepository
     * @param ArticleRepository $articleRepository
     * @param TagRepository     $tagRepository
     * @param string            $search
     *
     * @return Response
     *
     * @Route(
     *     "/",
     *     methods={"GET"},
     *     name="search_view",
     * )
     */
    public function view(Request $request, UserRepository $userRepository, ArticleRepository $articleRepository, TagRepository $tagRepository): Response
    {
        $searchParam = $request->query->get('search');

        $articlesResult = $articleRepository->search($searchParam);
//        $articlesByTagResult = $articleRepository->searchByTag($searchParam);
        $tagsResult = $tagRepository->search($searchParam);
        $usersResult = $userRepository->search($searchParam);

        return $this->render(
            'search/view.html.twig',
            [
                'articles' => $articlesResult,
                'authors' => $usersResult,
                'tags' => $tagsResult,
                'searchParam' => $searchParam,
            ]
        );
    }
}
