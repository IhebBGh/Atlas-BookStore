<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Product;

class CartService
{
    private RequestStack $requestStack;
    private EntityManagerInterface $em;

    public function __construct(RequestStack $requestStack, EntityManagerInterface $em)
    {
        $this->requestStack = $requestStack;
        $this->em = $em;
    }

    private function getSession()
    {
        return $this->requestStack->getSession();
    }

    // Récupère le panier avec les objets Product rechargés
    public function getCart(): array
    {
        $session = $this->getSession();
        $rawCart = $session->get('cart', []);
        $cart = [];

        foreach ($rawCart as $productId => $quantity) {
            $product = $this->em->getRepository(Product::class)->find($productId);
            if ($product) {
                $cart[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                ];
            }
        }

        return $cart;
    }

    // Ajoute un produit au panier (stocke seulement l'ID et la quantité)
    public function addProduct(Product $product, int $quantity = 1): void
    {
        $session = $this->getSession();
        $cart = $session->get('cart', []);
        $productId = $product->getId();

        if (!isset($cart[$productId])) {
            $cart[$productId] = 0;
        }

        $cart[$productId] += $quantity;
        $session->set('cart', $cart);
    }

    // Supprime un produit
    public function removeProduct(int $productId): void
    {
        $session = $this->getSession();
        $cart = $session->get('cart', []);
        unset($cart[$productId]);
        $session->set('cart', $cart);
    }

    // Met à jour la quantité
    public function updateQuantity(int $productId, int $quantity): void
    {
        if ($quantity <= 0) {
            $this->removeProduct($productId);
            return;
        }

        $session = $this->getSession();
        $cart = $session->get('cart', []);
        $cart[$productId] = $quantity;
        $session->set('cart', $cart);
    }

    // Vide le panier
    public function clear(): void
    {
        $session = $this->getSession();
        $session->remove('cart');
    }

    // Total du panier
    public function getTotal(): float
    {
        $total = 0;
        foreach ($this->getCart() as $item) {
            $total += (float)$item['product']->getPrice() * $item['quantity'];
        }
        return $total;
    }

    // Nombre total d'articles
    public function getItemCount(): int
    {
        $session = $this->getSession();
        $count = 0;
        foreach ($session->get('cart', []) as $quantity) {
            $count += $quantity;
        }
        return $count;
    }
}