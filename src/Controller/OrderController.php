<?php

namespace App\Controller;

use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class OrderController extends AbstractController
{
    #[Route('/orders', name: 'app_orders')]
    public function index(OrderRepository $orderRepository): Response
    {
        $orders = $orderRepository->findByUser($this->getUser());

        return $this->render('order/index.html.twig', [
            'orders' => $orders,
        ]);
    }

    #[Route('/order/{id}', name: 'app_order_show')]
    public function show(int $id, OrderRepository $orderRepository): Response
    {
        $order = $orderRepository->find($id);

        if (!$order || $order->getUser() !== $this->getUser()) {
            $this->addFlash('error', 'Order not found.');
            return $this->redirectToRoute('app_orders');
        }

        return $this->render('order/show.html.twig', [
            'order' => $order,
        ]);
    }
}

