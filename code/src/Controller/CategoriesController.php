<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategoriesController extends AbstractController
{
    #[Route("/categories", name: 'app_categories')]
    public function index(Request $request, ManagerRegistry $doctrine): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $doctrine->getRepository(Category::class)->add($category, true);
            $this->addFlash('success', 'Your changes were saved');

            return $this->redirectToRoute('app_categories');
        }

        return $this->render('default/categories.html.twig', [
            'form' => $form->createView(),
            'categories' => $doctrine->getRepository(Category::class)->findAll(),
        ]);
    }
}
