<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Checker;

use Cron\CronExpression;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Synolia\SyliusSchedulerCommandPlugin\Components\Exceptions\Checker\IsNotDueException;
use Synolia\SyliusSchedulerCommandPlugin\Entity\CommandInterface;
use Synolia\SyliusSchedulerCommandPlugin\Repository\ScheduledCommandRepositoryInterface;

#[AutoconfigureTag(IsDueCheckerInterface::TAG_ID)]
class SoftLimitThresholdIsDueChecker implements IsDueCheckerInterface
{
    private const PRIORITY = -100;

    public static function getDefaultPriority(): int
    {
        return self::PRIORITY;
    }

    public function __construct(
        private readonly ScheduledCommandRepositoryInterface $scheduledCommandRepository,
        /**
         * Threshold in minutes
         */
        private readonly int $threshold = 5,
    ) {
    }

    /**
     * @throws \Synolia\SyliusSchedulerCommandPlugin\Components\Exceptions\Checker\IsNotDueException
     */
    public function isDue(CommandInterface $command, ?\DateTimeInterface $dateTime = null): bool
    {
        if (!$dateTime instanceof \DateTimeInterface) {
            $dateTime = new \DateTime();
        }

        $cron = new CronExpression($command->getCronExpression());
        if ($cron->isDue($dateTime)) {
            return true;
        }

        $previousRunDate = $cron->getPreviousRunDate();
        $previousRunDateThreshold = (clone $previousRunDate)->add(new \DateInterval(\sprintf('PT%dM', $this->threshold)));

        $lastCreatedScheduledCommand = $this->scheduledCommandRepository->findLastCreatedCommand($command);

        // if never, do my command is valid for the least "threshold" minutes
        if (!$lastCreatedScheduledCommand instanceof \Synolia\SyliusSchedulerCommandPlugin\Entity\ScheduledCommandInterface) {
            if ($dateTime->getTimestamp() >= $previousRunDate->getTimestamp() && $dateTime->getTimestamp() <= $previousRunDateThreshold->getTimestamp()) {
                return true;
            }

            throw new IsNotDueException();
        }

        // check if last command has been started since scheduled datetime +0..5 minutes
        if (
            $lastCreatedScheduledCommand->getCreatedAt()->getTimestamp() >= $previousRunDate->getTimestamp() &&
            $lastCreatedScheduledCommand->getCreatedAt()->getTimestamp() <= $previousRunDateThreshold->getTimestamp() &&
            $dateTime->getTimestamp() <= $previousRunDateThreshold->getTimestamp()
        ) {
            throw new IsNotDueException();
        }

        if (
            $dateTime->getTimestamp() >= $previousRunDate->getTimestamp() &&
            $dateTime->getTimestamp() <= $previousRunDateThreshold->getTimestamp()
        ) {
            return true;
        }

        throw new IsNotDueException();
    }
}
