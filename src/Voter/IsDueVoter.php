<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Voter;

use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Synolia\SyliusSchedulerCommandPlugin\Checker\IsDueCheckerInterface;
use Synolia\SyliusSchedulerCommandPlugin\Components\Exceptions\Checker\IsNotDueException;
use Synolia\SyliusSchedulerCommandPlugin\Entity\CommandInterface;

class IsDueVoter implements IsDueVoterInterface
{
    public function __construct(
        #[TaggedIterator(IsDueCheckerInterface::TAG_ID)]
        private readonly iterable $checkers,
    ) {
    }

    public function isDue(CommandInterface $command, ?\DateTimeInterface $dateTime = null): bool
    {
        /** @var IsDueCheckerInterface $checker */
        foreach ($this->checkers as $checker) {
            try {
                return $checker->isDue($command, $dateTime);
            } catch (IsNotDueException) {
                continue;
            }
        }

        return false;
    }
}
