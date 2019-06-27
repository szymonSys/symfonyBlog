<?php
/**
 * Avatar controller.
 */

namespace App\Controller;


use App\Entity\Avatar;
use App\Form\AvatarType;
use App\Repository\AvatarRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\FileUploader;

/**
 * Class AvatarController
 * @package App\Controller
 *
 * @Route("/avatar")
 */
class AvatarController extends AbstractController
{
    private $uploaderService = null;

    public function __construct(FileUploader $uploaderService)
    {
        $this->uploaderService = $uploaderService;
    }

    /**
     * New action.
     *
     * @param Request $request
     * @param AvatarRepository $repository
     *
     * @return Response
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @Route(
     *     "/new",
     *     methods={"GET", "POST"},
     *     name="avatar_new",
     * )
     */
    public function new(Request $request, AvatarRepository $repository): Response
    {
        if (!$this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirectToRoute('security_login');
        }

        $avatar = new Avatar();
        $form = $this->createForm(AvatarType::class, $avatar);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $avatar->setUser($this->getUser());
            $repository->save($avatar);
            $this->addFlash('success', 'message.created_successfully');

            return $this->redirectToRoute(
                'author_view',
                [
                    'id' => $this->getUser()->getId(),
                    'firstName' => $this->getUser()->getFirstName()
                ]
            );
        }

        return $this->render(
            'avatar/new.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * Edit action.
     *
     * @param Request $request
     * @param int $id
     * @param AvatarRepository $repository
     * @param Filesystem $filesystem
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
     *     name="avatar_edit",
     * )
     */
    public function edit(Request $request, AvatarRepository $repository, Filesystem $filesystem, int $id): Response
    {
        $avatar = $repository->find($id);

        if(!$this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirectToRoute('security_login');
        }

        if($id !== $this->getUser()->getAvatar()->getId()) {
            return $this->redirectToRoute(
                'author_view',
                [
                    'id' => $this->getUser()->getId(),
                    'firstName' => $this->getUser()->getFirstName(),
                ]
            );
        }

        $originalAvatar = clone $avatar;

        $form = $this->createForm(AvatarType::class, $avatar, ['method' => 'PUT']);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            if($formData->getFile() instanceof UploadedFile) {
                $repository->save($avatar);
                $file = $originalAvatar->getFile();
                $filesystem->remove($file->getPathname());
            }

            $this->addFlash('success', 'message.updated_successfully');

            return $this->redirectToRoute(
                'author_view',
                [
                    'id' => $this->getUser()->getId(),
                    'firstName' => $this->getUser()->getFirstName(),
                ]
            );
        }

        return $this->render(
            'avatar/edit.html.twig',
            [
                'form' => $form->createView(),
                'avatar' => $avatar,
            ]
        );
    }

    /**
     * @param Request $request
     * @param AvatarRepository $repository
     * @param int $id
     *
     * @return Response
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @Route(
     *     "/{id}/delete",
     *     methods={"GET", "DELETE"},
     *     requirements={"id": "[1-9]\d*"},
     *     name="avatar_delete",
     * )
     */
    public function delete(Request $request, AvatarRepository $repository, int $id): Response
    {

        $avatar = $repository->find($id);

        if(!$this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirectToRoute('security_login');
        }

        if($id !== $this->getUser()->getAvatar()->getId()) {
            return $this->redirectToRoute(
                'author_view',
                [
                    'id' => $this->getUser()->getId(),
                    'firstName' => $this->getUser()->getFirstName(),
                ]
            );
        }

        $form = $this->createForm(FormType::class, $avatar, ['method' => 'DELETE']);
        $form->handleRequest($request);

        if ($request->isMethod('DELETE') && !$form->isSubmitted()) {
            $form->submit($request->request->get($form->getName()));
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $repository->delete($avatar);
            $this->addFlash('success', 'message.deleted_successfully');

            return $this->redirectToRoute(
                'author_view',
                [
                    'id' => $this->getUser()->getId(),
                    'firstName' => $this->getUser()->getFirstName(),
                ]
            );
        }

        return $this->render(
            'avatar/delete.html.twig',
            [
                'form' => $form->createView(),
                'avatar' => $avatar,
            ]
        );
    }
}