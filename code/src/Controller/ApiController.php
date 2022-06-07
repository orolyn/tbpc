<?php

namespace App\Controller;

use App\Repository\TaskRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    public function __construct(
        private TaskRepository $taskRepository
    ) {
    }

    #[Route('/api/tasks', name: 'app_api_tasks', methods: ['GET'])]
    public function tasks(): Response
    {
        $tasks = $this->taskRepository->findAll();

        $data = [];
        $min = null;
        $max = null;

        foreach ($tasks as $task) {
            $min = $min ? min($min, $task->getStart())  : $task->getStart();
            $max = $max ? min($max, $task->getFinish()) : $task->getFinish();

            $date = $task->getStart()->format('Y-m-d');

            if (!array_key_exists($date, $data)) {
                $data[$date] = [
                    'total_time' => 0,
                    'billable_time' => 0
                ];
            }

            $data[$date]['total_time']
                += ($task->getFinish()->getTimestamp() - $task->getStart()->getTimestamp());

            if ($task->getCategory()->isBillable()) {
                $data[$date]['billable_time']
                    += ($task->getFinish()->getTimestamp() - $task->getStart()->getTimestamp());
            }
        }

        $data = array_map(
            function (array $day) {
                return [
                    'total_time' =>
                        (new DateTime("@0"))->diff(new DateTime("@{$day['total_time']}"))->format('%Hh%Im'),
                    'billable_time' =>
                        (new DateTime("@0"))->diff(new DateTime("@{$day['billable_time']}"))->format('%Hh%Im')
                ];
            },
            $data
        );

        return new JsonResponse(
            [
                'data' => $data,
                'summary' => [
                    'first_date' => $min?->format('Y-m-d'),
                    'last_date' => $max?->format('Y-m-d'),
                    'total_tasks_records' => count($tasks)
                ]
            ]
        );
    }
}
