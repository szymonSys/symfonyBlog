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

    public function __construct(FileUploader $uploaderService)
    {
        $this->uploaderService = $uploaderService;
    }

    /**
     * New action.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request    HTTP request
     * @param \App\Repository\PhotoRepository           $repository Photo repository
     * @param int $id
     *
     * @return \Symfony\Component\HttpFoundation\Response HTTP response
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
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

        if($article->getAuthor()->getId() !== $this->getUser()->getId()) {
            return $this->redirectToRoute(
                'article_view',
                [
                    'id' => $id
                ]
            );
        }

        $photo = new Photo();
        $form = $this->createForm(PhotoType::class, $photo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $photo->setArticle($article);
            $photoRepository->save($photo);
            $this->addFlash('success', 'message.created_successfully');

            return $this->redirectToRoute(
                'article_view',
                [
                    'id' => $id
                ]
            );
        }

        return $this->render(
            'photo/new.html.twig',
            [
                'id' => $id,
                'form' => $form->createView()
            ]
        );
    }

    /**
     * Edit action.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request    HTTP  request
     * @param \App\Entity\Photo                         $photo      Photo entity
     * @param \App\Repository\PhotoRepository           $repository Photo repository
     * @param \Symfony\Component\Filesystem\Filesystem  $filesystem Filesystem component
     *
     * @return \Symfony\Component\HttpFoundation\Response HTTP response
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
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

        if(!$this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirectToRoute('security_login');
        }

        if($article->getAuthor()->getId() !== $this->getUser()->getId()) {
            return $this->redirectToRoute(
                'article_view',
                [
                    'id' => $articleId
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

            $this->addFlash('success', 'message.updated_successfully');

            return $this->redirectToRoute(
                'article_view',
                [
                    'id' => $articleId
                ]
            );
        }

        return $this->render(
            'photo/edit.html.twig',
            [
                'form' => $form->createView(),
                'id' => $id,
                'articleId' => $articleId
            ]
        );
    }
}