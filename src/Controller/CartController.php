<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart')]
    public function index(CartService $cartService): Response
    {
        $cartItems = $cartService->getCart();
        $total = $cartService->getTotal();

        return $this->render('cart/index.html.twig', [
            'cartItems' => $cartItems,
            'total' => $total,
        ]);
    }

    #[Route('/cart/add/{id}', name: 'app_add_to_cart', methods: ['POST'])]
    public function add(int $id, ProductRepository $productRepository, CartService $cartService, Request $request): Response
    {
        $product = $productRepository->find($id);
        
        if (!$product) {
            $this->addFlash('error', 'Produit introuvable.');
            return $this->redirectToRoute('app_home');
        }

        $quantity = (int) $request->request->get('quantity', 1);
        $cartService->addProduct($product, $quantity);

        $this->addFlash('success', 'Produit ajouté au panier !');
        
        // Redirect back to the page that called this
        $referer = $request->headers->get('referer');
        return $referer ? $this->redirect($referer) : $this->redirectToRoute('app_home');
    }

    #[Route('/cart/update/{id}', name: 'app_update_cart', methods: ['POST'])]
    public function update(int $id, CartService $cartService, Request $request): Response
    {
        $quantity = (int) $request->request->get('quantity', 1);
        $cartService->updateQuantity($id, $quantity);

        $this->addFlash('success', 'Quantité mise à jour.');
        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/remove/{id}', name: 'app_remove_from_cart')]
    public function remove(int $id, CartService $cartService): Response
    {
        $cartService->removeProduct($id);
        $this->addFlash('success', 'Produit retiré du panier.');
        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/clear', name: 'app_clear_cart')]
    public function clear(CartService $cartService): Response
    {
        $cartService->clear();
        $this->addFlash('success', 'Panier vidé.');
        return $this->redirectToRoute('app_cart');
    }

    #[Route('/checkout', name: 'app_checkout')]
    #[IsGranted('ROLE_USER')]
    public function checkout(CartService $cartService): Response
    {
        $cartItems = $cartService->getCart();
        
        if (empty($cartItems)) {
            $this->addFlash('error', 'Votre panier est vide.');
            return $this->redirectToRoute('app_cart');
        }

        $total = $cartService->getTotal();

        return $this->render('cart/checkout.html.twig', [
            'cartItems' => $cartItems,
            'total' => $total,
        ]);
    }
}

