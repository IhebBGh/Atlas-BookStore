<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        UserRepository $userRepository
    ): Response {
        if ($request->isMethod('POST')) {
            $email = (string) $request->request->get('email', '');
            $plainPassword = (string) $request->request->get('password', '');

            if ($email === '' || $plainPassword === '') {
                $this->addFlash('error', 'Email and password are required.');
                return $this->redirectToRoute('app_register');
            }

            // Check if a user with the same email already exists
            $existing = $userRepository->findOneBy(['email' => $email]);
            if ($existing) {
                $this->addFlash('error', 'A user with this email already exists.');
                return $this->redirectToRoute('app_register');
            }

            $user = new User();
            $user->setEmail($email);

            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Account created. You can now log in.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig');
    }
}



