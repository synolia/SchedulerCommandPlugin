<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Synolia\SyliusSchedulerCommandPlugin\Repository\ScheduledCommandRepositoryInterface;

final class EmptyLogsController extends AbstractController
{
    public function __construct(
        private readonly ScheduledCommandRepositoryInterface $scheduledCommandRepository,
        private readonly TranslatorInterface $translator,
        #[Autowire(env: 'string:SYNOLIA_SCHEDULER_PLUGIN_LOGS_DIR')]
        private readonly string $logsDir,
    ) {
    }

    #[Route('/scheduled-commands/empty-logs', name: 'sylius_admin_scheduler_empty_logs', defaults: ['_sylius' => ['permission' => true, 'repository' => ['method' => 'findById', 'arguments' => ['$ids']]]], methods: ['GET|PUT'])]
    public function emptyLogs(Request $request): Response
    {
        $commandIds = $request->get('ids');

        foreach ($commandIds as $commandId) {
            $command = $this->scheduledCommandRepository->find($commandId);
            if ($command !== null && $command->getLogFile() !== null) {
                @\file_put_contents($this->logsDir . \DIRECTORY_SEPARATOR . $command->getLogFile(), '');
            }
        }

        $this->addFlash('success', $this->translator->trans('sylius.ui.scheduled_command.bulk_empty_logs'));

        return $this->redirectToRoute(
            'synolia_admin_command_index',
            [],
            Response::HTTP_MOVED_PERMANENTLY,
        );
    }
}
