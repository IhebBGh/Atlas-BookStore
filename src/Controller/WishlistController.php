<?php

namespace App\Controller;

use App\Entity\Wishlist;
use App\Repository\ProductRepository;
use App\Repository\WishlistRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class WishlistController extends AbstractController
{
    #[Route('/wishlist', name: 'app_wishlist')]
    public function index(WishlistRepository $wishlistRepository): Response
    {
        $wishlistItems = $wishlistRepository->findByUser($this->getUser());

        return $this->render('wishlist/index.html.twig', [
            'wishlistItems' => $wishlistItems,
        ]);
    }

    #[Route('/wishlist/add/{id}', name: 'app_wishlist_add')]
    public function add(
        int $id,
        ProductRepository $productRepository,
        WishlistRepository $wishlistRepository,
        EntityManagerInterface $entityManager,
        Request $request
    ): Response {
        $product = $productRepository->find($id);

        if (!$product) {
            $this->addFlash('error', 'Product not found.');
            return $this->redirectToRoute('app_home');
        }

        // Check if already in wishlist
        $existing = $wishlistRepository->findOneByUserAndProduct($this->getUser(), $product);
        if ($existing) {
            $this->addFlash('info', 'Product is already in your wishlist.');
        } else {
            $wishlist = new Wishlist();
            $wishlist->setUser($this->getUser());
            $wishlist->setProduct($product);

            $entityManager->persist($wishlist);
            $entityManager->flush();

            $this->addFlash('success', 'Product added to wishlist!');
        }

        $referer = $request->headers->get('referer');
        return $referer ? $this->redirect($referer) : $this->redirectToRoute('app_home');
    }

    #[Route('/wishlist/remove/{id}', name: 'app_wishlist_remove')]
    public function remove(
        int $id,
        WishlistRepository $wishlistRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $wishlist = $wishlistRepository->find($id);

        if (!$wishlist || $wishlist->getUser() !== $this->getUser()) {
            $this->addFlash('error', 'Wishlist item not found.');
            return $this->redirectToRoute('app_wishlist');
        }

        $entityManager->remove($wishlist);
        $entityManager->flush();

        $this->addFlash('success', 'Product removed from wishlist.');
        return $this->redirectToRoute('app_wishlist');
    }
}

