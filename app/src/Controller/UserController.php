<?php
/**
 * User Controller.
 */

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class UserController.
 *
 * @Route(
 *     "/users"
 * )
 */
class UserController extends AbstractController
{
    /**
     * @param Request        $request
     * @param UserRepository $repository
     * @param int            $id
     *
     * @return Response
     *
     * @Route(
     *     "/{id}/editRole",
     *     methods={"GET", "PUT"},
     *     requirements={"id": "[1-9]\d*"},
     *     name="edit_role",
     * )
     */
    public function editRole(Request $request, UserRepository $repository, int $id): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('author_index');
        }
        $user = $repository->find($id);
        $form = $this->createForm(FormType::class, $user, ['method' => 'PUT']);
        $form->handleRequest($request);
        $isAdmin = $user->checkIfAdmin();
        if ($form->isSubmitted() && $form->isValid()) {
            if ($isAdmin) {
                $user->divestAdmin();
            } else {
                $user->makeAdmin();
            }
            $repository->save($user);
            $this->addFlash('success', 'message.user_role_edited_successfully');

            return $this->redirectToRoute('author_index');
        }

        return $this->render(
            'users/edit.html.twig',
            [
                'form' => $form->createView(),
                'id' => $id,
                'title' => $isAdmin ? 'label.remove_admin_privileges' : 'label.add_admin_privileges',
                'userName' => $user->getFirstName(),
            ]
        );
    }

    /**
     * Delete action.
     *
     * @param Request        $request
     * @param UserRepository $repository
     * @param int            $id
     *
     *@return Response
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @Route(
     *     "/{id}/delete",
     *     methods={"GET", "DELETE"},
     *     requirements={"id": "[1-9]\d*"},
     *     name="delete_user",
     * )
     */
    public function delete(Request $request, UserRepository $repository, int $id): Response
    {
        $user = $repository->find($id);
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('author_index');
        }
        $form = $this->createForm(FormType::class, $user, ['method' => 'DELETE']);
        $form->handleRequest($request);
        if ($request->isMethod('DELETE') && !$form->isSubmitted()) {
            $form->submit($request->request->get($form->getName()));
        }
        if ($form->isSubmitted() && $form->isValid()) {
            $repository->delete($user);
            $this->addFlash('success', 'message.user_deleted_successfully');

            return $this->redirectToRoute('author_index');
        }

        return $this->render(
            'users/delete.html.twig',
            [
                'form' => $form->createView(),
                'id' => $id,
            ]
        );
    }
}
