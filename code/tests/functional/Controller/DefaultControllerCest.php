<?php

namespace App\Tests\Functional\Controller;

use App\Entity\Task;
use App\Tests\FunctionalTester;

class DefaultControllerCest
{
    public function test(FunctionalTester $I)
    {
        $I->amOnRoute('app_default_index');
        $I->see('Tasks', 'h1');
        $I->fillField('form[description]', 'Create awesome website');
        $I->click('Create Task');

        $I->seeInRepository(Task::class, [
            'description' => 'Create awesome website',
        ]);
    }
}
