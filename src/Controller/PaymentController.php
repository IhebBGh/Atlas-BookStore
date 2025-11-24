<?php

namespace App\Controller;

use App\Entity\Order;
use App\Repository\OrderRepository;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class PaymentController extends AbstractController
{
    #[Route('/payment/process', name: 'app_payment_process', methods: ['POST'])]
    public function process(
        Request $request,
        CartService $cartService,
        EntityManagerInterface $entityManager
    ): Response {
        $cartItems = $cartService->getCart();
        
        if (empty($cartItems)) {
            $this->addFlash('error', 'Your cart is empty.');
            return $this->redirectToRoute('app_cart');
        }

        $paymentMethod = $request->request->get('payment_method', 'cash');
        $paymentToken = $request->request->get('stripeToken');

        // Create order
        $order = new Order();
        $order->setUser($this->getUser());
        $order->setTotal((string) $cartService->getTotal());
        $order->setStatus('pending');
        $order->setPaymentMethod($paymentMethod);
        $order->setPaymentStatus($paymentMethod === 'card' && $paymentToken ? 'paid' : 'pending');
        $order->setShippingAddress($request->request->get('shipping_address', ''));
        $order->setBillingAddress($request->request->get('billing_address', ''));

        // Process payment if card
        if ($paymentMethod === 'card' && $paymentToken) {
            // In production, integrate with Stripe API here
            // For now, simulate successful payment
            $order->setPaymentStatus('paid');
            $order->setStatus('processing');
        }

        // Create order items
        foreach ($cartItems as $item) {
            $orderItem = new \App\Entity\OrderItem();
            $orderItem->setProduct($item['product']);
            $orderItem->setQuantity($item['quantity']);
            $orderItem->setPrice((string) $item['product']->getPrice());
            $order->addItem($orderItem);
        }

        $entityManager->persist($order);
        $entityManager->flush();

        $cartService->clear();
        $this->addFlash('success', 'Order placed successfully! Your order #' . $order->getId() . ' has been confirmed.');

        return $this->redirectToRoute('app_order_show', ['id' => $order->getId()]);
    }

    #[Route('/payment/success', name: 'app_payment_success')]
    public function success(): Response
    {
        return $this->render('payment/success.html.twig');
    }

    #[Route('/payment/cancel', name: 'app_payment_cancel')]
    public function cancel(): Response
    {
        $this->addFlash('warning', 'Payment was cancelled.');
        return $this->redirectToRoute('app_cart');
    }
}

