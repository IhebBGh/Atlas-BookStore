<?php

namespace App\Controller;

use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function index(OrderRepository $orderRepository): Response
    {
        $user = $this->getUser();
        $recentOrders = $orderRepository->findByUser($user);

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'recentOrders' => array_slice($recentOrders, 0, 5),
        ]);
    }

    #[Route('/profile/change-password', name: 'app_profile_change_password', methods: ['POST'])]
    public function changePassword(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();
        $oldPassword = $request->request->get('old_password');
        $newPassword = $request->request->get('new_password');

        if (!$passwordHasher->isPasswordValid($user, $oldPassword)) {
            $this->addFlash('error', 'Mot de passe actuel incorrect.');
            return $this->redirectToRoute('app_profile');
        }

        if (strlen($newPassword) < 6) {
            $this->addFlash('error', 'Le nouveau mot de passe doit contenir au moins 6 caractères.');
            return $this->redirectToRoute('app_profile');
        }

        $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
        $user->setPassword($hashedPassword);

        $entityManager->flush();

        $this->addFlash('success', 'Mot de passe modifié avec succès.');
        return $this->redirectToRoute('app_profile');
    }
}

