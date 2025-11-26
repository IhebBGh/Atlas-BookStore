<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use App\Repository\OrderRepository;
use App\Repository\MessageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/admin', name: 'admin_dashboard')]
    public function index(
        ProductRepository $productRepository,
        UserRepository $userRepository,
        OrderRepository $orderRepository,
        MessageRepository $messageRepository
    ): Response {
        // Stats logic
        $totalProducts = count($productRepository->findAll());
        $allUsers = $userRepository->findAll();
        $totalUsers = count($allUsers);
        
        $adminUsers = 0;
        foreach ($allUsers as $user) {
            if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
                $adminUsers++;
            }
        }
        $regularUsers = $totalUsers - $adminUsers;
        
        // Fetch recent data
        $recentProducts = $productRepository->findBy([], ['id' => 'DESC'], 5);
        $recentUsers = $userRepository->findBy([], ['id' => 'DESC'], 5);
        $recentOrders = $orderRepository->findBy([], ['id' => 'DESC'], 5);
        $recentMessages = $messageRepository->findBy([], ['id' => 'DESC'], 5);
        
        // Calculate stats
        $allOrders = $orderRepository->findAll();
        $totalOrders = count($allOrders);
        
        $allMessagesData = $messageRepository->findAll();
        $totalMessages = count($allMessagesData);
        $unreadMessages = count(array_filter($allMessagesData, function($msg) {
            return $msg->getIsRead() === false;
        }));
        
        // Notifications count (unread messages + recent orders)
        $notificationsCount = $unreadMessages + min(3, count($recentOrders));

        return $this->render('admin/dashboard.html.twig', [
            'totalProducts' => $totalProducts,
            'totalUsers' => $totalUsers,
            'adminUsers' => $adminUsers,
            'regularUsers' => $regularUsers,
            'totalOrders' => $totalOrders,
            'totalMessages' => $totalMessages,
            'unreadMessages' => $unreadMessages,
            'notificationsCount' => $notificationsCount,
            'recentProducts' => $recentProducts,
            'recentUsers' => $recentUsers,
            'recentOrders' => $recentOrders,
            'recentMessages' => $recentMessages,
        ]);
    }
}
