<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Parser;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\KernelInterface;
use Webmozart\Assert\Assert;

class CommandParser implements CommandParserInterface
{
    /** @var string[] */
    private readonly array $excludedNamespaces;

    public function __construct(
        private readonly KernelInterface $kernel,
        #[Autowire(['_complete'])]
        array $excludedNamespaces = [],
    ) {
        Assert::allString($excludedNamespaces);
        $this->excludedNamespaces = $excludedNamespaces;
    }

    public function getCommands(): array
    {
        $application = new Application($this->kernel);

        $commandsList = [];
        $commands = $application->all();
        foreach ($commands as $command) {
            $name = $command->getName() ?? '';
            $namespace = \explode(':', $name)[0];
            if (in_array($namespace, $this->excludedNamespaces, true)) {
                continue;
            }
            $commandsList[$namespace][$command->getName()] = $name;
        }

        return $commandsList;
    }
}
