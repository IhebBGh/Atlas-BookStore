<?php

namespace App\Controller;

use App\Repository\MessageRepository;
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
    public function index(OrderRepository $orderRepository, MessageRepository $messageRepository): Response
    {
        $user = $this->getUser();
        $recentOrders = $orderRepository->findByUser($user);
        $recentMessages = $messageRepository->findByUser($user);
        
        // Count messages with responses
        $messagesWithResponse = array_filter($recentMessages, function($message) {
            return $message->getAdminResponse() !== null;
        });

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'recentOrders' => array_slice($recentOrders, 0, 5),
            'recentMessages' => array_slice($recentMessages, 0, 3),
            'messagesWithResponse' => count($messagesWithResponse),
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
            $this->addFlash('error', 'Current password is incorrect.');
            return $this->redirectToRoute('app_profile');
        }

        if (strlen($newPassword) < 6) {
            $this->addFlash('error', 'The new password must contain at least 6 characters.');
            return $this->redirectToRoute('app_profile');
        }

        $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
        $user->setPassword($hashedPassword);

        $entityManager->flush();

        $this->addFlash('success', 'Password changed successfully.');
        return $this->redirectToRoute('app_profile');
    }
}

