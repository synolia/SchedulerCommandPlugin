<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Synolia\SyliusSchedulerCommandPlugin\Parser\CommandParserInterface;

final class CommandChoiceType extends AbstractType
{
    public function __construct(private readonly CommandParserInterface $commandParser)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'choices' => $this->commandParser->getCommands(),
            ],
        );
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
