<?php
/**
 * Tag controller.
 */

namespace App\Controller;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use App\Repository\TagRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class TagController.
 *
 * @Route("/tag")
 */
class TagController extends AbstractController
{
    /**
     * Articles view action.
     *
     * @param Request            $request           HTTP request
     * @param TagRepository      $tagRepository     Tag repository
     * @param ArticleRepository  $articleRepository Article repository
     * @param PaginatorInterface $paginator         Paginator
     * @param int                $id                Element Id
     *
     * @return Response
     *
     * @Route(
     *     "/{name}/{id}",
     *     name="tag_articles",
     * )
     */
    public function articlesView(Request $request, TagRepository $tagRepository, ArticleRepository $articleRepository, PaginatorInterface $paginator, int $id): Response
    {
        $tag = $tagRepository->find($id);

        $pagination = $paginator->paginate(
            $articleRepository->findbyTag($tag),
            $request->query->getInt('page', 1),
            Article::NUMBER_OF_ITEMS
        );

        return $this->render(
            'tag/view.html.twig',
            [
                'pagination' => $pagination,
                'tagName' => $tag->getName(),
            ]
        );
    }
}
