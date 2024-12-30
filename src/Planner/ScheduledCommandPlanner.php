<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Planner;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Synolia\SyliusSchedulerCommandPlugin\Entity\CommandInterface;
use Synolia\SyliusSchedulerCommandPlugin\Entity\ScheduledCommandInterface;
use Synolia\SyliusSchedulerCommandPlugin\Enum\ScheduledCommandStateEnum;
use Synolia\SyliusSchedulerCommandPlugin\Repository\ScheduledCommandRepository;

class ScheduledCommandPlanner implements ScheduledCommandPlannerInterface
{
    public function __construct(
        private readonly FactoryInterface $scheduledCommandFactory,
        private readonly EntityManagerInterface $entityManager,
        private readonly ScheduledCommandRepository $scheduledCommandRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function plan(CommandInterface $command): ScheduledCommandInterface
    {
        /** @var ScheduledCommandInterface[] $scheduledCommands */
        $scheduledCommands = $this->scheduledCommandRepository->findBy([
            'command' => $command->getCommand(),
            'state' => ScheduledCommandStateEnum::WAITING,
        ]);

        if (0 !== count($scheduledCommands)) {
            return array_shift($scheduledCommands);
        }

        /** @var ScheduledCommandInterface $scheduledCommand */
        $scheduledCommand = $this->scheduledCommandFactory->createNew();

        $scheduledCommand
            ->setName($command->getName())
            ->setCommand($command->getCommand())
            ->setArguments($command->getArguments())
            ->setTimeout($command->getTimeout())
            ->setIdleTimeout($command->getIdleTimeout())
            ->setOwner($command)
        ;

        if (null !== $command->getLogFilePrefix() && '' !== $command->getLogFilePrefix()) {
            $scheduledCommand->setLogFile(\sprintf(
                '%s-%s-%s.log',
                $command->getLogFilePrefix(),
                (new \DateTime())->format('Y-m-d'),
                \uniqid(),
            ));
        }

        $this->entityManager->persist($scheduledCommand);
        $this->entityManager->flush();

        $this->logger->info('Command has been planned for execution.', ['command_name' => $command->getName()]);

        return $scheduledCommand;
    }
}
