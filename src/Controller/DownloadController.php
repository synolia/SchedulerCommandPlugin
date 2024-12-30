<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Synolia\SyliusSchedulerCommandPlugin\Repository\ScheduledCommandRepositoryInterface;

final class DownloadController extends AbstractController
{
    public function __construct(
        private readonly ScheduledCommandRepositoryInterface $scheduledCommandRepository,
        #[Autowire(param: 'env(string:SYNOLIA_SCHEDULER_PLUGIN_LOGS_DIR)')]
        private readonly string $logsDir,
    ) {
    }

    #[Route('/scheduled-commands/download/logfile/{command}', name: 'download_schedule_log_file', defaults: ['_sylius' => ['permission' => true]], methods: ['GET'])]
    public function downloadLogFile(string $command): Response
    {
        $scheduleCommand = $this->scheduledCommandRepository->find($command);

        if (
            null === $scheduleCommand ||
            null === $scheduleCommand->getLogFile()
        ) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        $filePath = $this->logsDir . \DIRECTORY_SEPARATOR . $scheduleCommand->getLogFile();
        if (!\file_exists($filePath)) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        return $this->file($filePath);
    }
}
