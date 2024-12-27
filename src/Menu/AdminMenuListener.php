<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Menu;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: 'sylius.menu.admin.main', method: 'addAdminMenuItems')]
final class AdminMenuListener
{
    public function addAdminMenuItems(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();
        $newSubmenu = $menu
            ->addChild('scheduler')
            ->setLabel('synolia.menu.admin.main.configuration.scheduler_command')
        ;
        $newSubmenu
            ->addChild('scheduler-command', [
                'route' => 'synolia_admin_command_index',
            ])
            ->setAttribute('type', 'link')
            ->setLabel('synolia.menu.admin.main.configuration.scheduler_command')
            ->setLabelAttribute('icon', 'list')
        ;
        $newSubmenu
            ->addChild('scheduler-command-history', [
                'route' => 'synolia_admin_scheduled_command_index',
            ])
            ->setAttribute('type', 'link')
            ->setLabel('synolia.menu.admin.main.configuration.scheduler_command_history')
            ->setLabelAttribute('icon', 'clock')
        ;
    }
}
