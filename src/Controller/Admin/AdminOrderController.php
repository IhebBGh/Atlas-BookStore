<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/orders')]
class AdminOrderController extends AbstractController
{
    #[Route('', name: 'admin_order_index')]
    public function index(OrderRepository $orderRepository): Response
    {
        $orders = $orderRepository->findRecentOrders(50);

        return $this->render('admin/order/index.html.twig', [
            'orders' => $orders,
        ]);
    }

    #[Route('/{id}', name: 'admin_order_show')]
    public function show(int $id, OrderRepository $orderRepository): Response
    {
        $order = $orderRepository->find($id);

        if (!$order) {
            $this->addFlash('error', 'Order not found.');
            return $this->redirectToRoute('admin_order_index');
        }

        return $this->render('admin/order/show.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/{id}/update-status', name: 'admin_order_update_status', methods: ['POST'])]
    public function updateStatus(
        int $id,
        OrderRepository $orderRepository,
        EntityManagerInterface $entityManager,
        Request $request
    ): Response {
        $order = $orderRepository->find($id);

        if (!$order) {
            $this->addFlash('error', 'Order not found.');
            return $this->redirectToRoute('admin_order_index');
        }

        $oldStatus = $order->getStatus();
        $status = $request->request->get('status');
        $order->setStatus($status);
        $entityManager->flush();

        // Notify admin about status change
        if ($oldStatus !== $status) {
            $this->addFlash('success', 'Order #' . $order->getId() . ' status updated from "' . $oldStatus . '" to "' . $status . '". The customer can view the updated status in their orders.');
        } else {
            $this->addFlash('success', 'Order status updated.');
        }
        return $this->redirectToRoute('admin_order_show', ['id' => $id]);
    }
}

