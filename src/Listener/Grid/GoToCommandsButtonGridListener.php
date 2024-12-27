<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Listener\Grid;

use Sylius\Component\Grid\Definition\Action;
use Sylius\Component\Grid\Definition\ActionGroup;
use Sylius\Component\Grid\Event\GridDefinitionConverterEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: 'sylius.grid.synolia_admin_scheduled_commands', method: 'onSyliusGridAdmin')]
final class GoToCommandsButtonGridListener
{
    public function onSyliusGridAdmin(GridDefinitionConverterEvent $event): void
    {
        $grid = $event->getGrid();

        if (!$grid->hasActionGroup('main')) {
            $grid->addActionGroup(ActionGroup::named('main'));
        }

        $actionGroup = $grid->getActionGroup('main');

        if ($actionGroup->hasAction('go_to_commands')) {
            return;
        }

        $action = Action::fromNameAndType('go_to_commands', 'link');
        $action->setLabel('synolia.menu.admin.main.configuration.scheduler_command');
        $action->setOptions([
            'class' => 'blue',
            'icon' => 'list',
            'link' => [
                'route' => 'synolia_admin_command_index',
                'parameters' => [],
            ],
        ]);

        $actionGroup->addAction($action);
    }
}
