<?php
/**
 * Category controller.
 */
namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class CategoryController.
 *
 * @Route("/category")
 */
class CategoryController extends AbstractController
{
    /**
     * Index action.
     *
     * @param Request            $request    HTTP request
     * @param CategoryRepository $repository Category repository
     * @param PaginatorInterface $paginator  Paginator
     *
     * @return Response
     *
     * @Route(
     *     "/",
     *     name="category_index",
     * )
     */
    public function index(Request $request, CategoryRepository $repository, PaginatorInterface $paginator): Response
    {
        $pagination = $paginator->paginate(
            $repository->queryAll(),
            $request->query->getInt('page', 1),
            Category::NUMBER_OF_ITEMS
        );

        return $this->render(
            'category/index.html.twig',
            ['pagination' => $pagination]
        );
    }

    /**
     * View action.
     *
     * @param Request            $request            HTTP request
     * @param CategoryRepository $categoryRepository Category repository
     * @param PaginatorInterface $paginator          Paginator
     * @param ArticleRepository  $articleRepository  Article repository
     * @param string             $name               Element name
     *
     * @return Response HTTP response
     *
     * @Route(
     *     "/{name}/articles",
     *     name="category_view",
     * )
     */
    public function view(Request $request, CategoryRepository $categoryRepository, PaginatorInterface $paginator, ArticleRepository $articleRepository, string $name): Response
    {
        if (!is_null($name)) {
            $categoryName = $categoryRepository->findOneBy(['name' => $name])->getName();
        } else {
            return $this->redirectToRoute('category_index');
        }

        $pagination = $paginator->paginate(
            $articleRepository->findAllThanCategory($categoryName),
            $request->query->getInt('page', 1),
            Article::NUMBER_OF_ITEMS
        );

        return $this->render(
            'category/view.html.twig',
            [
                'categoryName' => $categoryName,
                'pagination' => $pagination,
            ]
        );
    }

    /**
     * New action.
     *
     * @param Request            $request    HTTP request
     * @param CategoryRepository $repository Category repository
     *
     * @return Response HTTP response
     *
     * @throws ORMException
     * @throws OptimisticLockException
     *
     * @Route(
     *     "/new",
     *     methods={"GET", "POST"},
     *     name="category_new",
     * )
     */
    public function new(Request $request, CategoryRepository $repository): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('category_index');
        }

        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
//            $category->setCreatedAt(new \DateTime());
//            $category->setUpdatedAt(new \DateTime());
            $repository->save($category);

            $this->addFlash('success', 'message.category_created_successfully');

            return $this->redirectToRoute('category_index');
        }

        return $this->render(
            'category/new.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * Edit action.
     *
     * @param Request            $request    HTTP request
     * @param CategoryRepository $repository Category repository
     * @param int                $id         Element Id
     *
     * @return Response HTTP response
     *
     * @throws ORMException
     * @throws OptimisticLockException
     *
     * @Route(
     *     "/{id}/edit",
     *     methods={"GET", "PUT"},
     *     requirements={"id": "[1-9]\d*"},
     *     name="category_edit",
     * )
     */
    public function edit(Request $request, CategoryRepository $repository, int $id): Response
    {
        $category = $repository->find($id);

        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('category_index');
        }

        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('category_index');
        }

        $form = $this->createForm(CategoryType::class, $category, ['method' => 'PUT']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
//            $category->setUpdatedAt(new \DateTime());
            $repository->save($category);

            $this->addFlash('success', 'message.category_updated_successfully');

            return $this->redirectToRoute('category_index');
        }

        return $this->render(
            'category/edit.html.twig',
            [
                'form' => $form->createView(),
                'category' => $category,
            ]
        );
    }

    /**
     * Delete action.
     *
     * @param Request            $request    HTTP request
     * @param CategoryRepository $repository Category repository
     * @param int                $id         Element Id
     *
     * @return Response HTTP response
     *
     * @throws ORMException
     * @throws OptimisticLockException
     *
     * @Route(
     *     "/{id}/delete",
     *     methods={"GET", "DELETE"},
     *     requirements={"id": "[1-9]\d*"},
     *     name="category_delete",
     * )
     */
    public function delete(Request $request, CategoryRepository $repository, int $id): Response
    {
        $category = $repository->find($id);

        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('category_index');
        }

        if ($category->getArticles()->count()) {
            $this->addFlash('warning', 'message.category_contains_tasks');

            return $this->redirectToRoute('category_index');
        }

        $form = $this->createForm(FormType::class, $category, ['method' => 'DELETE']);
        $form->handleRequest($request);

        if ($request->isMethod('DELETE') && !$form->isSubmitted()) {
            $form->submit($request->request->get($form->getName()));
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $repository->delete($category);
            $this->addFlash('success', 'message.category_deleted_successfully');

            return $this->redirectToRoute('category_index');
        }

        return $this->render(
            'category/delete.html.twig',
            [
                'form' => $form->createView(),
                'category' => $category,
            ]
        );
    }
}
