<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusSchedulerCommandPlugin\PHPUnit\Checker;

use Synolia\SyliusSchedulerCommandPlugin\Checker\EveryMinuteIsDueChecker;
use Synolia\SyliusSchedulerCommandPlugin\Components\Exceptions\Checker\IsNotDueException;
use Tests\Synolia\SyliusSchedulerCommandPlugin\PHPUnit\AbstractIsDueTest;

class EveryMinuteIsDueCheckerTest extends AbstractIsDueTest
{
    private EveryMinuteIsDueChecker $everyMinuteIsDueChecker;

    public function setUp(): void
    {
        parent::setUp();
        $this->everyMinuteIsDueChecker = static::getContainer()->get(EveryMinuteIsDueChecker::class);
    }

    /**
     * @dataProvider isDueUsingCronExpressionDataProvider
     */
    public function testIsDue(
        string $cronExpression,
        \DateTimeInterface $currentDateTime,
        bool $expectedResult,
    ): void {
        $command = $this->setupCommand($cronExpression);

        if ($expectedResult === false) {
            $this->expectException(IsNotDueException::class);
        }

        $this->assertEquals($expectedResult, $this->everyMinuteIsDueChecker->isDue($command, $currentDateTime));
    }
}
