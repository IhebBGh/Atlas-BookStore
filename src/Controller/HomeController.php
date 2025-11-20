<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends AbstractController
{
    public function homepage(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findAll();
        return $this->render('home/index.html.twig', [
            'products' => $products,
        ]);
    }

    public function index(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findAll();
        return $this->render('home/index.html.twig', [
            'products' => $products,
        ]);
    }
       
    public function about(): Response
    {
        return $this->render('home/about.html.twig');
    }
}