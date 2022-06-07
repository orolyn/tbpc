<?php

namespace App\Tests\Validator;

use App\Entity\Task;
use App\Repository\TaskRepository;
use App\Tests\UnitTester;
use App\Validator\TaskDateTimeRange;
use App\Validator\TaskDateTimeRangeValidator;
use DateTime;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

final class TaskDateTimeRangeValidatorTest extends \Codeception\Test\Unit
{
    /**
     * @var UnitTester
     */
    protected UnitTester $tester;

    /**
     * @var TaskRepository|MockObject
     */
    private TaskRepository $repository;

    /**
     * @var ExecutionContextInterface|MockObject
     */
    private ExecutionContextInterface $context;

    /**
     * @var ConstraintViolationBuilderInterface
     */
    private ConstraintViolationBuilderInterface $violationBuilder;

    /**
     * @var TaskDateTimeRangeValidator
     */
    private TaskDateTimeRangeValidator $validator;

    protected function _before()
    {
        $this->repository = $this->getMockBuilder(TaskRepository::class)->disableOriginalConstructor()->getMock();
        $this->context =
            $this->getMockBuilder(ExecutionContextInterface::class)->disableOriginalConstructor()->getMock();

        $this->violationBuilder =
            $this->getMockBuilder(ConstraintViolationBuilderInterface::class)->disableOriginalConstructor()->getMock();

        $this->validator = new TaskDateTimeRangeValidator($this->repository);
        $this->validator->initialize($this->context);
    }

    protected function _after()
    {
    }

    // tests
    public function testFinishDateCannotComeBeforeStartDate()
    {
        $task = new Task();
        $task
            ->setStart(new DateTime())
            ->setFinish(new DateTime('-1 hour'));

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with(
                'The finish date must not come before the start date.'
            )
            ->willReturn($this->violationBuilder);

        $this->validator->validate($task, new TaskDateTimeRange());
    }

    // tests
    public function testTaskDateRangeCannotExceedTheLimit()
    {
        $task = new Task();
        $task
            ->setStart(new DateTime())
            ->setFinish(new DateTime('+9 hours'));

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with(
                sprintf('The task cannot last more than %s hours.', TaskDateTimeRangeValidator::LIMIT)
            )
            ->willReturn($this->violationBuilder);

        $this->validator->validate($task, new TaskDateTimeRange());
    }

    // tests
    public function testTaskDateRangeCannotCrossOverMidnight()
    {
        $task = new Task();
        $task
            ->setStart(new DateTime('today +22 hours'))
            ->setFinish(new DateTime('tomorrow +4 hours'));

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with('You cannot work past midnight.')
            ->willReturn($this->violationBuilder);

        $this->validator->validate($task, new TaskDateTimeRange());
    }

    // tests
    public function testTasksDoNotIntersect()
    {
        $otherTask = new Task();
        $otherTask
            ->setStart(new DateTime('today'))
            ->setFinish(new DateTime('today +4 hours'));

        $this->repository->method('getTasksStartingOnDay')->willReturn([$otherTask]);

        $task = new Task();
        $task
            ->setStart(new DateTime('today +3 hours'))
            ->setFinish(new DateTime('today +7 hours'));

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with('A task cannot intersect with another task on the same day.')
            ->willReturn($this->violationBuilder);

        $this->validator->validate($task, new TaskDateTimeRange());
    }

    public function testTasksInDayDoNotExceedLimit()
    {
        $otherTasks = [
            (new Task())->setStart(new DateTime('today'))->setFinish(new DateTime('today +4 hours')),
            (new Task())->setStart(new DateTime('today +4 hours'))->setFinish(new DateTime('today +8 hours'))
        ];

        $this->repository->method('getTasksStartingOnDay')->willReturn($otherTasks);

        $task = new Task();
        $task
            ->setStart(new DateTime('today +8 hours'))
            ->setFinish(new DateTime('today +12 hours'));

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with(
                sprintf(
                    'The accumulative tasks on the same day cannot exceed %s hours',
                    TaskDateTimeRangeValidator::LIMIT
                )
            )
            ->willReturn($this->violationBuilder);

        $this->validator->validate($task, new TaskDateTimeRange());
    }
}
