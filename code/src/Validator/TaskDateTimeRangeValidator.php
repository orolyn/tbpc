<?php

namespace App\Validator;

use App\Entity\Task;
use App\Repository\TaskRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\ValidatorException;

class TaskDateTimeRangeValidator extends ConstraintValidator
{
    public const LIMIT = 8;

    public function __construct(
        private TaskRepository $taskRepository
    ) {
    }

    public function validate($value, Constraint $constraint)
    {
        /* @var TaskDateTimeRange $constraint */

        if (!$value instanceof Task) {
            throw new ValidatorException(sprintf('TaskDateTimeRangeValidator can only be used with %s', Task::class));
        }

        // Check if the task starts after it finishes
        if ($value->getFinish() <= $value->getStart()) {
            $this->context
                ->buildViolation('The finish date must not come before the start date.')
                ->addViolation();

            return;
        }

        // Initial check if the task is more than the limited number of hours
        // Saves calling the DB later
        $duration = ($value->getFinish()->getTimestamp() - $value->getStart()->getTimestamp()) / 3600;

        if ($duration > self::LIMIT) {
            $this->context
                ->buildViolation(sprintf('The task cannot last more than %s hours.', self::LIMIT))
                ->addViolation();

            return;
        }

        // Not technically in the requirements, but the simplest way to limit the hours in a day.
        if ($value->getStart()->format('d') !== $value->getFinish()->format('d')) {
            $this->context
                ->buildViolation('You cannot work past midnight.')
                ->addViolation();

            return;
        }

        // Get all tasks starting on the same day
        $tasks = $this->taskRepository->getTasksStartingOnDay($value->getStart());

        // Check if the new task intersects with any tasks on the same day
        foreach ($tasks as $task) {
            $duration += $task->getFinish()->getTimestamp() - $task->getStart()->getTimestamp();

            $intersects =
                $value->getStart()  >= $task->getStart()  && $value->getStart()  < $task->getFinish() ||
                $value->getFinish() <= $task->getFinish() && $value->getFinish() > $task->getStart();

            if ($intersects) {
                $this->context
                    ->buildViolation('A task cannot intersect with another task on the same day.')
                    ->addViolation();

                return;
            }
        }

        // Check if the total accumulated tasks exists the limit number of hours
        if ($duration / 3600 > self::LIMIT) {
            $this->context
                ->buildViolation(sprintf('The accumulative tasks on the same day cannot exceed %s hours', self::LIMIT))
                ->addViolation();

            return;
        }
    }
}
