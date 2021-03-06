<?php
/**
 * Photo controller.
 */

namespace App\Controller;

use App\Entity\Photo;
use App\Form\PhotoType;
use App\Repository\ArticleRepository;
use App\Repository\PhotoRepository;
use App\Service\FileUploader;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class PhotoController.
 *
 * @Route("article")
 */
class PhotoController extends AbstractController
{
    private $uploaderService = null;

    /**
     * PhotoController constructor.
     *
     * @param FileUploader $uploaderService
     */
    public function __construct(FileUploader $uploaderService)
    {
        $this->uploaderService = $uploaderService;
    }

    /**
     * New action.
     *
     * @param Request           $request           HTTP request
     * @param PhotoRepository   $photoRepository   Photo repository
     * @param ArticleRepository $articleRepository Article repository
     * @param int               $id                Element Id
     *
     * @return Response HTTP response
     *
     * @throws ORMException
     * @throws OptimisticLockException
     *
     * @Route(
     *     "/{id}/photo/new",
     *     methods={"GET", "POST"},
     *     name="photo_new",
     * )
     */
    public function new(Request $request, PhotoRepository $photoRepository, ArticleRepository $articleRepository, int $id): Response
    {
        if (!$this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirectToRoute('security_login');
        }

        $article = $articleRepository->find($id);

        if (!$this->isGranted('ROLE_ADMIN') && $article->getAuthor()->getId() !== $this->getUser()->getId()) {
            return $this->redirectToRoute(
                'article_view',
                [
                    'id' => $id,
                ]
            );
        }

        $photo = new Photo();
        $form = $this->createForm(PhotoType::class, $photo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $photo->setArticle($article);
            $photoRepository->save($photo);
            $this->addFlash('success', 'message.photo_created_successfully');

            return $this->redirectToRoute(
                'article_view',
                [
                    'id' => $id,
                ]
            );
        }

        return $this->render(
            'photo/new.html.twig',
            [
                'id' => $id,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * Edit action.
     *
     * @param Request           $request           HTTP request
     * @param ArticleRepository $articleRepository Article repository
     * @param PhotoRepository   $photoRepository   Photo repository
     * @param Filesystem        $filesystem        Filesystem component
     * @param int               $articleId         Article Id
     * @param int               $id                Element Id
     *
     * @return Response HTTP response
     *
     * @throws ORMException
     * @throws OptimisticLockException
     *
     * @Route(
     *     "/{articleId}/photo/{id}/edit",
     *     methods={"GET", "PUT"},
     *     requirements={"id": "[1-9]\d*"},
     *     name="photo_edit",
     * )
     */
    public function edit(Request $request, ArticleRepository $articleRepository, PhotoRepository $photoRepository, Filesystem $filesystem, int $articleId, int $id): Response
    {
        $article = $articleRepository->find($articleId);
        $photo = $photoRepository->find($id);

        if (!$this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirectToRoute('security_login');
        }

        if (!$this->isGranted('ROLE_ADMIN') && $article->getAuthor()->getId() !== $this->getUser()->getId()) {
            return $this->redirectToRoute(
                'article_view',
                [
                    'id' => $articleId,
                ]
            );
        }

        $originalPhoto = clone $photo;

        $form = $this->createForm(PhotoType::class, $photo, ['method' => 'PUT']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            if ($formData->getFile() instanceof UploadedFile) {
                $photoRepository->save($photo);
                $file = $originalPhoto->getFile();
                $filesystem->remove($file->getPathname());
            }

            $this->addFlash('success', 'message.photo_updated_successfully');

            return $this->redirectToRoute(
                'article_view',
                [
                    'id' => $articleId,
                ]
            );
        }

        return $this->render(
            'photo/edit.html.twig',
            [
                'form' => $form->createView(),
                'id' => $id,
                'articleId' => $articleId,
            ]
        );
    }
}
