<?php

namespace App\Controller\Admin;

use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/analytics')]
class AnalyticsController extends AbstractController
{
    #[Route('', name: 'admin_analytics')]
    public function index(
        OrderRepository $orderRepository,
        ProductRepository $productRepository,
        UserRepository $userRepository
    ): Response {
        // Sales statistics
        $totalOrders = count($orderRepository->findAll());
        $totalRevenue = $orderRepository->createQueryBuilder('o')
            ->select('SUM(o.total) as total')
            ->getQuery()
            ->getSingleScalarResult() ?? 0;

        $pendingOrders = count($orderRepository->findBy(['status' => 'pending']));
        $completedOrders = count($orderRepository->findBy(['status' => 'completed']));

        // Recent orders
        $recentOrders = $orderRepository->findRecentOrders(10);

        // Top products
        $topProducts = $productRepository->createQueryBuilder('p')
            ->leftJoin('App\Entity\OrderItem', 'oi', 'WITH', 'oi.product = p')
            ->select('p.id, p.nom, SUM(oi.quantity) as totalSold')
            ->groupBy('p.id')
            ->orderBy('totalSold', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        // Monthly revenue (last 6 months)
        $monthlyRevenue = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-$i months"));
            $revenue = $orderRepository->createQueryBuilder('o')
                ->select('SUM(o.total) as total')
                ->where('DATE_FORMAT(o.createdAt, \'%Y-%m\') = :month')
                ->setParameter('month', $month)
                ->getQuery()
                ->getSingleScalarResult() ?? 0;
            $monthlyRevenue[$month] = (float) $revenue;
        }

        return $this->render('admin/analytics/index.html.twig', [
            'totalOrders' => $totalOrders,
            'totalRevenue' => $totalRevenue,
            'pendingOrders' => $pendingOrders,
            'completedOrders' => $completedOrders,
            'recentOrders' => $recentOrders,
            'topProducts' => $topProducts,
            'monthlyRevenue' => $monthlyRevenue,
        ]);
    }
}

