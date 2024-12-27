<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Fixture;

use Sylius\Bundle\FixturesBundle\Fixture\AbstractFixture;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Synolia\SyliusSchedulerCommandPlugin\Entity\CommandInterface;

class SchedulerCommandFixture extends AbstractFixture
{
    public function __construct(
        private readonly RepositoryInterface $commandRepository,
        private readonly FactoryInterface $commandFactory,
    ) {
    }

    /**
     * @inheritdoc
     */
    public function load(array $options): void
    {
        if (!\is_array($options['scheduled_commands'])) {
            return;
        }

        foreach ($options['scheduled_commands'] as $commandArray) {
            /** @var CommandInterface $command */
            $command = $this->commandFactory->createNew();
            $command
                ->setName($commandArray['name'])
                ->setCommand($commandArray['command'])
                ->setArguments($commandArray['arguments'])
                ->setCronExpression($commandArray['cronExpression'])
                ->setLogFilePrefix($commandArray['logFilePrefix'])
                ->setPriority($commandArray['priority'])
                ->setTimeout($commandArray['timeout'] ?? null)
                ->setIdleTimeout($commandArray['idle_timeout'] ?? null)
                ->setExecuteImmediately($commandArray['executeImmediately'])
                ->setEnabled($commandArray['enabled'])
            ;
            $this->commandRepository->add($command);
        }
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return 'scheduler_command';
    }

    protected function configureOptionsNode(ArrayNodeDefinition $optionsNode): void
    {
        $optionsNode
            ->children()
            ->arrayNode('scheduled_commands')->arrayPrototype()->children()
            ->scalarNode('name')->isRequired()->end()
            ->scalarNode('command')->isRequired()->end()
            ->scalarNode('arguments')->defaultValue('')->end()
            ->scalarNode('cronExpression')->isRequired()->end()
            ->scalarNode('logFilePrefix')->defaultValue('')->end()
            ->integerNode('priority')->isRequired()->end()
            ->integerNode('timeout')->defaultNull()->end()
            ->integerNode('idle_timeout')->defaultNull()->end()
            ->booleanNode('executeImmediately')->defaultFalse()->end()
            ->booleanNode('enabled')->defaultTrue()->end()
            ->end()
            ->end()
        ;
    }
}
