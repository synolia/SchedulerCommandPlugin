<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\DependencyInjection;

use Sylius\Bundle\CoreBundle\DependencyInjection\PrependDoctrineMigrationsTrait;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class SynoliaSyliusSchedulerCommandExtension extends Extension implements PrependExtensionInterface
{
    use PrependDoctrineMigrationsTrait;

    /**
     * @inheritDoc
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(\dirname(__DIR__, 2) . '/config'));
        $loader->load('services.yaml');
    }

    /**
     * @inheritdoc
     */
    public function prepend(ContainerBuilder $container): void
    {
        $this->prependDoctrineMigrations($container);
    }

    protected function getMigrationsNamespace(): string
    {
        return 'Synolia\SyliusSchedulerCommandPlugin\Migrations';
    }

    protected function getMigrationsDirectory(): string
    {
        return '@SynoliaSyliusSchedulerCommandPlugin/migrations';
    }

    protected function getNamespacesOfMigrationsExecutedBefore(): array
    {
        return [];
    }
}
