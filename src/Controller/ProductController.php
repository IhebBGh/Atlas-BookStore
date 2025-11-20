<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use App\Repository\ReviewRepository;
use App\Repository\WishlistRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ProductController extends AbstractController
{
    #[Route('/product/{id}', name: 'app_product_show')]
    public function show(
        int $id,
        ProductRepository $productRepository,
        ReviewRepository $reviewRepository,
        WishlistRepository $wishlistRepository
    ): Response {
        $product = $productRepository->find($id);

        if (!$product) {
            $this->addFlash('error', 'Produit introuvable.');
            return $this->redirectToRoute('app_home');
        }

        // Get reviews
        $reviews = $reviewRepository->findByProduct($product);
        $averageRating = $reviewRepository->getAverageRating($product);
        $ratingCount = $reviewRepository->getRatingCount($product);

        // Check if in wishlist
        $inWishlist = false;
        if ($this->getUser()) {
            $wishlistItem = $wishlistRepository->findOneByUserAndProduct($this->getUser(), $product);
            $inWishlist = $wishlistItem !== null;
        }

        // Get related products (same category, excluding current)
        $relatedProducts = [];
        if ($product->getCategory()) {
            $relatedProducts = $productRepository->createQueryBuilder('p')
                ->where('p.category = :category')
                ->andWhere('p.id != :id')
                ->setParameter('category', $product->getCategory())
                ->setParameter('id', $product->getId())
                ->setMaxResults(4)
                ->getQuery()
                ->getResult();
        }

        return $this->render('product/show.html.twig', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
            'reviews' => $reviews,
            'averageRating' => $averageRating,
            'ratingCount' => $ratingCount,
            'inWishlist' => $inWishlist,
        ]);
    }

    #[Route('/category/{slug}', name: 'app_category_show')]
    public function category(string $slug, CategoryRepository $categoryRepository, ProductRepository $productRepository): Response
    {
        $category = $categoryRepository->findOneBy(['slug' => $slug]);

        if (!$category) {
            $this->addFlash('error', 'Catégorie introuvable.');
            return $this->redirectToRoute('app_home');
        }

        $products = $productRepository->findBy(['category' => $category]);

        return $this->render('product/category.html.twig', [
            'category' => $category,
            'products' => $products,
        ]);
    }

    #[Route('/search', name: 'app_search')]
    public function search(Request $request, ProductRepository $productRepository): Response
    {
        $query = $request->query->get('q', '');
        $products = [];

        if ($query) {
            $products = $productRepository->createQueryBuilder('p')
                ->where('p.nom LIKE :query')
                ->orWhere('p.description LIKE :query')
                ->setParameter('query', '%' . $query . '%')
                ->getQuery()
                ->getResult();
        }

        return $this->render('product/search.html.twig', [
            'products' => $products,
            'query' => $query,
        ]);
    }
}

