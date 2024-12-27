<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Command;

use Doctrine\DBAL\Exception\ConnectionLost;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Synolia\SyliusSchedulerCommandPlugin\Entity\CommandInterface;
use Synolia\SyliusSchedulerCommandPlugin\Entity\ScheduledCommandInterface;
use Synolia\SyliusSchedulerCommandPlugin\Enum\ScheduledCommandStateEnum;
use Synolia\SyliusSchedulerCommandPlugin\Planner\ScheduledCommandPlannerInterface;
use Synolia\SyliusSchedulerCommandPlugin\Repository\CommandRepositoryInterface;
use Synolia\SyliusSchedulerCommandPlugin\Repository\ScheduledCommandRepositoryInterface;
use Synolia\SyliusSchedulerCommandPlugin\Runner\ScheduleCommandRunnerInterface;
use Synolia\SyliusSchedulerCommandPlugin\Voter\IsDueVoterInterface;

#[AsCommand(name: 'synolia:scheduler-run', description: 'Execute scheduled commands')]
final class SynoliaSchedulerRunCommand extends Command
{
    use LockableTrait;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private ScheduleCommandRunnerInterface $scheduleCommandRunner,
        private CommandRepositoryInterface $commandRepository,
        private ScheduledCommandRepositoryInterface $scheduledCommandRepository,
        private ScheduledCommandPlannerInterface $scheduledCommandPlanner,
        private IsDueVoterInterface $isDueVoter,
        private LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('id', 'i', InputOption::VALUE_OPTIONAL, 'Command ID');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $scheduledCommandId = $input->getOption('id');

        if (null !== $scheduledCommandId) {
            /** @var ScheduledCommandInterface|null $scheduledCommand */
            $scheduledCommand = $this->scheduledCommandRepository->find((int) $scheduledCommandId);

            if (!$scheduledCommand instanceof ScheduledCommandInterface) {
                return 0;
            }

            $this->executeCommand($scheduledCommand, $io);

            return 0;
        }

        $commands = $this->getCommands($input);

        /** @var CommandInterface $command */
        foreach ($commands as $command) {
            // delayed execution just after, to keep cron comparison effective
            if ($this->shouldExecuteCommand($command, $io)) {
                $this->scheduledCommandPlanner->plan($command);
                // The execution is planned, and does not need to be launched again in the future.
                $command->setExecuteImmediately(false);
            }
        }

        if (!$this->lock()) {
            $output->writeln('The command is already running in another process.');
            $this->logger->info('Scheduler is already running.');

            return 0;
        }

        /** @var ScheduledCommandInterface[] $scheduledCommands */
        $scheduledCommands = $this->scheduledCommandRepository->findAllRunnable();

        if (0 === \count($scheduledCommands)) {
            $io->success('Nothing to do.');
        }

        foreach ($scheduledCommands as $scheduledCommand) {
            $io->note(\sprintf(
                'Execute Command "%s" - last execution : %s',
                $scheduledCommand->getCommand(),
                $scheduledCommand->getExecutedAt() !== null ? $scheduledCommand->getExecutedAt()->format('d/m/Y H:i:s') : 'never',
            ));

            try {
                $this->runScheduledCommand($io, $scheduledCommand);
            } catch (ConnectionLost) {
                $this->runScheduledCommand($io, $scheduledCommand);
            }
        }

        $this->release();

        return 0;
    }

    private function runScheduledCommand(SymfonyStyle $io, ScheduledCommandInterface $scheduledCommand): void
    {
        /** prevent update during running time */
        $this->scheduledCommandRepository->find($scheduledCommand->getId());

        $this->executeCommand($scheduledCommand, $io);
    }

    private function executeCommand(ScheduledCommandInterface $scheduledCommand, SymfonyStyle $io): void
    {
        try {
            /** @var Application $application */
            $application = $this->getApplication();
            $command = $application->find($scheduledCommand->getCommand());
        } catch (\InvalidArgumentException $e) {
            $scheduledCommand->setLastReturnCode(-1);
            //persist last return code
            $this->entityManager->flush();
            $io->error('Cannot find ' . $scheduledCommand->getCommand());

            return;
        }

        // Execute command and get return code
        try {
            $io->writeln(
                '<info>Execute</info> : <comment>' . $scheduledCommand->getCommand()
                . ' ' . $scheduledCommand->getArguments() . '</comment>',
            );

            $scheduledCommand->setExecutedAt(new \DateTime());
            $this->changeState($scheduledCommand, ScheduledCommandStateEnum::IN_PROGRESS);
            $result = $this->scheduleCommandRunner->runFromCron($scheduledCommand);

            try {
                $this->changeState($scheduledCommand, $this->getStateForResult($result));
            } catch (ConnectionLost) {
                $this->changeState($scheduledCommand, $this->getStateForResult($result));
            }
        } catch (\Exception $e) {
            $this->changeState($scheduledCommand, ScheduledCommandStateEnum::ERROR);
            $io->warning($e->getMessage());
            $result = -1;
        }

        $scheduledCommand->setLastReturnCode($result);
        $this->entityManager->flush();

        /*
         * This clear() is necessary to avoid conflict between commands
         * and to be sure that none entity are managed before entering in a new command
         */
        $this->entityManager->clear();

        unset($command);
        gc_collect_cycles();
    }

    private function getCommands(InputInterface $input): iterable
    {
        $commands = $this->commandRepository->findEnabledCommand();
        if ($input->getOption('id') !== null) {
            $commands = $this->scheduledCommandRepository->findBy(['id' => $input->getOption('id')]);
        }

        return $commands;
    }

    private function shouldExecuteCommand(CommandInterface $command, SymfonyStyle $io): bool
    {
        if ($command->isExecuteImmediately()) {
            $io->note('Immediately execution asked for : ' . $command->getCommand());

            return true;
        }

        // Could be removed as getCommands fetch only enabled commands
        if (!$command->isEnabled()) {
            return false;
        }

        return $this->isDueVoter->isDue($command);
    }

    private function changeState(ScheduledCommandInterface $scheduledCommand, string $state): void
    {
        $scheduledCommand->setState($state);
        $this->entityManager->flush();
    }

    private function getStateForResult(int $returnResultCode): string
    {
        if ($returnResultCode === 143) {
            return ScheduledCommandStateEnum::TERMINATION;
        }

        if ($returnResultCode !== 0) {
            return ScheduledCommandStateEnum::ERROR;
        }

        return ScheduledCommandStateEnum::FINISHED;
    }
}
