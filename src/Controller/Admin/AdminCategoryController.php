<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/categories')]
class AdminCategoryController extends AbstractController
{
    #[Route('', name: 'admin_category_index')]
    public function index(CategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findAll();

        return $this->render('admin/category/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/new', name: 'admin_category_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Generate slug from name
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $category->getName())));
            $category->setSlug($slug);

            $entityManager->persist($category);
            $entityManager->flush();

            $this->addFlash('success', 'Catégorie créée avec succès.');
            return $this->redirectToRoute('admin_category_index');
        }

        return $this->render('admin/category/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_category_edit')]
    public function edit(int $id, Request $request, CategoryRepository $categoryRepository, EntityManagerInterface $entityManager): Response
    {
        $category = $categoryRepository->find($id);

        if (!$category) {
            $this->addFlash('error', 'Catégorie introuvable.');
            return $this->redirectToRoute('admin_category_index');
        }

        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $category->getName())));
            $category->setSlug($slug);

            $entityManager->flush();

            $this->addFlash('success', 'Catégorie mise à jour.');
            return $this->redirectToRoute('admin_category_index');
        }

        return $this->render('admin/category/edit.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_category_delete', methods: ['POST'])]
    public function delete(int $id, CategoryRepository $categoryRepository, EntityManagerInterface $entityManager): Response
    {
        $category = $categoryRepository->find($id);

        if (!$category) {
            $this->addFlash('error', 'Catégorie introuvable.');
            return $this->redirectToRoute('admin_category_index');
        }

        $entityManager->remove($category);
        $entityManager->flush();

        $this->addFlash('success', 'Catégorie supprimée.');
        return $this->redirectToRoute('admin_category_index');
    }
}

