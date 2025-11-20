<?php

namespace App\Controller;

use App\Entity\Review;
use App\Form\ReviewType;
use App\Repository\ProductRepository;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class ReviewController extends AbstractController
{
    #[Route('/product/{id}/review', name: 'app_review_create', methods: ['POST'])]
    public function create(
        int $id,
        Request $request,
        ProductRepository $productRepository,
        ReviewRepository $reviewRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $product = $productRepository->find($id);

        if (!$product) {
            $this->addFlash('error', 'Product not found.');
            return $this->redirectToRoute('app_home');
        }

        // Check if user already reviewed this product
        $existingReview = $reviewRepository->createQueryBuilder('r')
            ->where('r.user = :user')
            ->andWhere('r.product = :product')
            ->setParameter('user', $this->getUser())
            ->setParameter('product', $product)
            ->getQuery()
            ->getOneOrNullResult();

        if ($existingReview) {
            $this->addFlash('error', 'You have already reviewed this product.');
            return $this->redirectToRoute('app_product_show', ['id' => $id]);
        }

        $review = new Review();
        $review->setUser($this->getUser());
        $review->setProduct($product);
        $review->setRating((int) $request->request->get('rating', 5));
        $review->setComment($request->request->get('comment', ''));

        if (empty($review->getComment())) {
            $this->addFlash('error', 'Please provide a review comment.');
            return $this->redirectToRoute('app_product_show', ['id' => $id]);
        }

        $entityManager->persist($review);
        $entityManager->flush();

        $this->addFlash('success', 'Thank you for your review!');
        return $this->redirectToRoute('app_product_show', ['id' => $id]);
    }
}

