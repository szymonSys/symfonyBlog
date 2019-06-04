<?php
/**
 * Security controller.
 */

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;



/**
 * Class SecurityController.
 */
class SecurityController extends AbstractController
{
    /**
     * Login form action.
     *
     * @param \Symfony\Component\Security\Http\Authentication\AuthenticationUtils $authenticationUtils Auth utils
     *
     * @return \Symfony\Component\HttpFoundation\Response HTTP response
     *
     * @Route(
     *     "/login",
     *     name="security_login",
     * )
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        if($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirectToRoute('article_index');
        };

        return $this->render(
            'security/login.html.twig',
            [
                'last_username' => $lastUsername,
                'error' => $error,
            ]
        );
    }

    /**
     * Logout action.
     *
     * @throws \Exception
     *
     * @Route(
     *     "/logout",
     *     name="security_logout",
     * )
     */
    public function logout(): void
    {
        // Request is intercepted before reaches this exception:
        throw new \Exception('Internal security module error');
    }


//    /**
//     * Register action.
//     *
//     * @param \Symfony\Component\HttpFoundation\Request $request    HTTP request
//     * @param \App\Repository\UserRepository            $repository User repository
//     *
//     * @return \Symfony\Component\HttpFoundation\Response HTTP response
//     *
//     * @throws \Doctrine\ORM\ORMException
//     * @throws \Doctrine\ORM\OptimisticLockException
//     *
//     * @Route(
//     *     "/register",
//     *     methods={"GET", "POST"},
//     *     name="security_register",
//     * )
//     */
//    public function register(Request $request, UserRepository $repository, UserPasswordEncoderInterface $passwordEncoder): Response
//    {
//        $user = new User();
//        $form = $this->createForm(UserType::class, $user);
//        $form->handleRequest($request);
//
//        if($form->isSubmitted() && $form->isValid()) {
//            $user->setPassword(
//                $passwordEncoder->encodePassword(
//                    $user,
//                    $form->get('password')->getData()
//                )
//            );
//
//            $user->setRoles(['ROLE_USER']);
//            $user->setBlogName(
//              $form->get('firstName')->getData()
//            );
//            $repository->save($user);
//            $this->addFlash('success', 'message.updated_successfully');
//
//            return $this->redirectToRoute('security_login');
//        };
//
//        return $this->render(
//            'security/register.html.twig',
//            ['form' => $form->createView()]
//        );
//    }
}


