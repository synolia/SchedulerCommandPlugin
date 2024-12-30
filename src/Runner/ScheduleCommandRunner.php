<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Runner;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;
use Synolia\SyliusSchedulerCommandPlugin\Entity\ScheduledCommandInterface;
use Synolia\SyliusSchedulerCommandPlugin\Repository\ScheduledCommandRepositoryInterface;

#[AutoconfigureTag(attributes: ['name' => 'sylius.grid_field', 'type' => 'scheduled_command_url'])]
class ScheduleCommandRunner implements ScheduleCommandRunnerInterface
{
    public function __construct(
        private readonly ScheduledCommandRepositoryInterface $scheduledCommandRepository,
        private readonly EntityManagerInterface $entityManager,
        #[Autowire(env: 'string:SYNOLIA_SCHEDULER_PLUGIN_LOGS_DIR')]
        private readonly string $logsDir,
        #[Autowire(param: 'kernel.project_dir')]
        private readonly string $projectDir,
        #[Autowire(env: 'int:SYNOLIA_SCHEDULER_PLUGIN_PING_INTERVAL')]
        private readonly int $pingInterval = 60,
        #[Autowire(env: 'bool:SYNOLIA_SCHEDULER_PLUGIN_KEEP_ALIVE')]
        private readonly bool $keepConnectionAlive = false,
    ) {
    }

    public function runImmediately(string $scheduledCommandId): bool
    {
        /** @var ScheduledCommandInterface|null $scheduledCommand */
        $scheduledCommand = $this->scheduledCommandRepository->find($scheduledCommandId);
        if (!$scheduledCommand instanceof ScheduledCommandInterface) {
            return false;
        }

        $process = Process::fromShellCommandline(
            $this->getCommandLine($scheduledCommand),
            $this->projectDir,
        );

        $scheduledCommand->setExecutedAt(new \DateTime());
        $process->setTimeout($scheduledCommand->getTimeout());
        $process->setIdleTimeout($scheduledCommand->getIdleTimeout());
        $this->startProcess($process);

        $result = $process->getExitCode();

        if (null === $result) {
            $result = 0;
        }

        $scheduledCommand->setLastReturnCode($result);
        $scheduledCommand->setCommandEndTime(new \DateTime());
        $this->entityManager->flush();

        return true;
    }

    public function runFromCron(ScheduledCommandInterface $scheduledCommand): int
    {
        $process = Process::fromShellCommandline($this->getCommandLine($scheduledCommand));
        $process->setTimeout($scheduledCommand->getTimeout());
        $process->setIdleTimeout($scheduledCommand->getIdleTimeout());

        try {
            $this->startProcess($process);
        } catch (ProcessTimedOutException) {
        }

        $result = $process->getExitCode();
        $scheduledCommand->setCommandEndTime(new \DateTime());

        if (null === $result) {
            $result = 0;
        }

        return $result;
    }

    private function startProcess(Process $process): void
    {
        if (!$this->keepConnectionAlive) {
            $process->run();

            return;
        }

        $process->start();
        while ($process->isRunning()) {
            $process->checkTimeout();

            try {
                $this->entityManager->getConnection()->executeQuery($this->entityManager->getConnection()->getDatabasePlatform()->getDummySelectSQL());
            } catch (\Doctrine\DBAL\Exception) {
            }

            for ($i = 0; $i < $this->pingInterval; ++$i) {
                if (!$process->isRunning()) {
                    return;
                }
                \sleep(1);

                $process->checkTimeout();
            }
        }
    }

    private function getLogOutput(ScheduledCommandInterface $scheduledCommand): ?string
    {
        if ($scheduledCommand->getLogFile() === null || $scheduledCommand->getLogFile() === '') {
            return null;
        }

        return $this->logsDir . \DIRECTORY_SEPARATOR . $scheduledCommand->getLogFile();
    }

    private function getCommandLine(ScheduledCommandInterface $scheduledCommand): string
    {
        $commandLine = sprintf(
            '%s/bin/console %s %s',
            $this->projectDir,
            $scheduledCommand->getCommand(),
            $scheduledCommand->getArguments() ?? '',
        );

        $logOutput = $this->getLogOutput($scheduledCommand);
        if (null !== $logOutput) {
            $commandLine = sprintf(
                '%s/bin/console %s %s >> %s 2>> %s',
                $this->projectDir,
                $scheduledCommand->getCommand(),
                $scheduledCommand->getArguments() ?? '',
                $logOutput,
                $logOutput,
            );
        }

        return $commandLine;
    }
}
