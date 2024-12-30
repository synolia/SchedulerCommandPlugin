<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Command;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Synolia\SyliusSchedulerCommandPlugin\Entity\ScheduledCommandInterface;
use Synolia\SyliusSchedulerCommandPlugin\Enum\ScheduledCommandStateEnum;
use Synolia\SyliusSchedulerCommandPlugin\Repository\ScheduledCommandRepositoryInterface;

#[AsCommand(name: 'synolia:scheduler:purge-history', description: 'Purge scheduled command history greater than {X} days old.')]
class PurgeScheduledCommandCommand extends Command
{
    private const DEFAULT_PURGE_PERIODE_IN_DAYS = 3;

    private const DEFAULT_STATE = ScheduledCommandStateEnum::FINISHED;

    private const DEFAULT_BATCH = 100;

    private SymfonyStyle $io;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ScheduledCommandRepositoryInterface $scheduledCommandRepository,
        private readonly LoggerInterface $logger,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->addOption('all', 'p', InputOption::VALUE_NONE, 'Remove all schedules with specified state (default is finished).')
            ->addOption('days', 'd', InputOption::VALUE_OPTIONAL, '{X} days old', self::DEFAULT_PURGE_PERIODE_IN_DAYS)
            ->addOption('state', 's', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'State of scheduled history to be cleaned', [self::DEFAULT_STATE])
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Dry run')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $purgeAll = $input->getOption('all');
        $daysOld = (int) $input->getOption('days');
        $state = $input->getOption('state');
        /** @var bool $dryRun */
        $dryRun = $input->getOption('dry-run') ?? false;

        /** @var ScheduledCommandInterface[] $schedules */
        $schedules = $this->getScheduledHistory($purgeAll, $daysOld, $state);

        $counter = 0;
        foreach ($schedules as $schedule) {
            $this->logger->info(\sprintf(
                'Removed scheduled command "%s" (%d)',
                $schedule->getName(),
                $schedule->getId(),
            ));

            if ($dryRun) {
                continue;
            }

            $this->entityManager->remove($schedule);

            if ($counter % self::DEFAULT_BATCH === 0) {
                $this->entityManager->flush();
            }
            ++$counter;
        }

        $this->entityManager->flush();

        return 0;
    }

    private function getScheduledHistory(bool $purgeAll, int $daysOld, array $states): iterable
    {
        if ($purgeAll) {
            $this->io->note(\sprintf(
                'All schedules with states ["%s"] will be purged.',
                \implode(',', $states),
            ));

            return $this->scheduledCommandRepository->findAllHavingState($states);
        }

        $maxDate = new \DateTime();
        $maxDate->modify(\sprintf('-%d days', $daysOld));

        $this->io->note(\sprintf(
            'Schedules with states ["%s"] lesser than %s days(s) (%s) will be purged.',
            \implode(',', $states),
            $daysOld,
            $maxDate->format('Y-m-d'),
        ));

        return $this->scheduledCommandRepository->findAllSinceXDaysWithState($maxDate, $states);
    }
}
