<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Repository\UserRepository;
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
        UserRepository $userRepository
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
        
        $recentProducts = $productRepository->findBy([], ['id' => 'DESC'], 5);
        $recentUsers = $userRepository->findBy([], ['id' => 'DESC'], 5);

        return $this->render('admin/dashboard.html.twig', [
            'totalProducts' => $totalProducts,
            'totalUsers' => $totalUsers,
            'adminUsers' => $adminUsers,
            'regularUsers' => $regularUsers,
            'recentProducts' => $recentProducts,
            'recentUsers' => $recentUsers,
        ]);
    }
}