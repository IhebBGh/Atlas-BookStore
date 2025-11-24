<?php

namespace App\Controller;

use App\Repository\MessageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class UserMessageController extends AbstractController
{
    #[Route('/my-messages', name: 'app_my_messages')]
    public function index(MessageRepository $messageRepository): Response
    {
        $user = $this->getUser();
        $messages = $messageRepository->findByUser($user);

        // Count messages with responses
        $messagesWithResponse = array_filter($messages, function($message) {
            return $message->getAdminResponse() !== null;
        });

        return $this->render('user/message/index.html.twig', [
            'messages' => $messages,
            'messagesWithResponse' => count($messagesWithResponse),
        ]);
    }

    #[Route('/my-messages/{id}', name: 'app_my_message_show')]
    public function show(int $id, MessageRepository $messageRepository): Response
    {
        $user = $this->getUser();
        $message = $messageRepository->find($id);

        if (!$message) {
            $this->addFlash('error', 'Message not found.');
            return $this->redirectToRoute('app_my_messages');
        }

        // Security check: user can only view their own messages
        if ($message->getUser() !== $user) {
            $this->addFlash('error', 'You do not have permission to view this message.');
            return $this->redirectToRoute('app_my_messages');
        }

        return $this->render('user/message/show.html.twig', [
            'message' => $message,
        ]);
    }
}

