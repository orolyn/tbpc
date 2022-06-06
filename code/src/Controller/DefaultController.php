<?php

namespace App\Controller;

use App\Entity\Task;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    #[Route("/")]
    public function index(Request $request, ManagerRegistry $doctrine): Response
    {
        $task = new Task();
        $task->setStart(new DateTime());
        $task->setFinish(new DateTime('+1 hour'));

        $form = $this->createFormBuilder($task)
            ->add('description', TextType::class)
            ->add('start', DateTimeType::class)
            ->add('finish', DateTimeType::class)
            ->add('save', SubmitType::class, ['label' => 'Create Task'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $doctrine->getRepository(Task::class)->add($task, true);
            $this->addFlash('success', 'Your changes were saved');

            return $this->redirectToRoute('app_default_index');
        }

        return $this->render('default/index.html.twig', [
            'form' => $form->createView(),
            'tasks' => $doctrine->getRepository(Task::class)->findAll(),
        ]);
    }
}
