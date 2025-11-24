<?php

namespace App\Controller\Admin;

use App\Entity\Message;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/messages')]
class AdminMessageController extends AbstractController
{
    #[Route('', name: 'admin_message_index')]
    public function index(MessageRepository $messageRepository, Request $request): Response
    {
        $status = $request->query->get('status', 'all');
        
        if ($status === 'all') {
            $messages = $messageRepository->findAllOrdered();
        } else {
            $messages = $messageRepository->findByStatus($status);
        }

        $unreadCount = $messageRepository->countUnread();

        return $this->render('admin/message/index.html.twig', [
            'messages' => $messages,
            'currentStatus' => $status,
            'unreadCount' => $unreadCount,
        ]);
    }

    #[Route('/{id}', name: 'admin_message_show')]
    public function show(int $id, MessageRepository $messageRepository, EntityManagerInterface $entityManager): Response
    {
        $message = $messageRepository->find($id);

        if (!$message) {
            $this->addFlash('error', 'Message not found.');
            return $this->redirectToRoute('admin_message_index');
        }

        // Mark as read if not already read
        if ($message->getStatus() === 'new') {
            $message->markAsRead();
            $entityManager->flush();
        }

        return $this->render('admin/message/show.html.twig', [
            'message' => $message,
        ]);
    }

    #[Route('/{id}/mark-read', name: 'admin_message_mark_read', methods: ['POST'])]
    public function markAsRead(int $id, MessageRepository $messageRepository, EntityManagerInterface $entityManager): Response
    {
        $message = $messageRepository->find($id);

        if (!$message) {
            $this->addFlash('error', 'Message not found.');
            return $this->redirectToRoute('admin_message_index');
        }

        $message->markAsRead();
        $entityManager->flush();

        $this->addFlash('success', 'Message marked as read.');
        return $this->redirectToRoute('admin_message_show', ['id' => $id]);
    }

    #[Route('/{id}/reply', name: 'admin_message_reply', methods: ['POST'])]
    public function reply(int $id, MessageRepository $messageRepository, EntityManagerInterface $entityManager, Request $request): Response
    {
        $message = $messageRepository->find($id);

        if (!$message) {
            $this->addFlash('error', 'Message not found.');
            return $this->redirectToRoute('admin_message_index');
        }

        $response = trim($request->request->get('response', ''));

        if (empty($response)) {
            $this->addFlash('error', 'Response cannot be empty.');
            return $this->redirectToRoute('admin_message_show', ['id' => $id]);
        }

        $message->setAdminResponse($response);
        $message->markAsReplied();
        $entityManager->flush();

        $this->addFlash('success', 'Response saved. The customer can view it in their account.');
        return $this->redirectToRoute('admin_message_show', ['id' => $id]);
    }

    #[Route('/{id}/delete', name: 'admin_message_delete', methods: ['POST'])]
    public function delete(int $id, MessageRepository $messageRepository, EntityManagerInterface $entityManager): Response
    {
        $message = $messageRepository->find($id);

        if (!$message) {
            $this->addFlash('error', 'Message not found.');
            return $this->redirectToRoute('admin_message_index');
        }

        $entityManager->remove($message);
        $entityManager->flush();

        $this->addFlash('success', 'Message deleted successfully.');
        return $this->redirectToRoute('admin_message_index');
    }
}

