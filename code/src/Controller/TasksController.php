<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TasksController extends AbstractController
{
    #[Route("/", name: 'app_tasks')]
    public function index(Request $request, ManagerRegistry $doctrine): Response
    {
        $task = new Task();
        $task
            ->setStart(new DateTime())
            ->setFinish(new DateTime('+1 hour'));

        $form = $this->createForm(TaskType::class, $task);;

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $doctrine->getRepository(Task::class)->add($task, true);
            $this->addFlash('success', 'Your changes were saved');

            return $this->redirectToRoute('app_tasks');
        }

        return $this->render('default/tasks.html.twig', [
            'form' => $form->createView(),
            'tasks' => $doctrine->getRepository(Task::class)->findAll(),
        ]);
    }
}
