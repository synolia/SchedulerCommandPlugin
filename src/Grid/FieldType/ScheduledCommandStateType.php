<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Grid\FieldType;

use Sylius\Component\Grid\Definition\Field;
use Sylius\Component\Grid\FieldTypes\FieldTypeInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

#[AutoconfigureTag('sylius.grid_field', attributes: ['type' => 'scheduled_command_state'])]
final readonly class ScheduledCommandStateType implements FieldTypeInterface
{
    public function __construct(private Environment $twig)
    {
    }

    /**
     * @inheritdoc
     */
    public function render(Field $field, $scheduleCommand, array $options): string
    {
        return $this->twig->render($options['template'], [
            'schedulerCommand' => $scheduleCommand,
        ]);
    }

    /** @inheritdoc */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('template');
    }
}
