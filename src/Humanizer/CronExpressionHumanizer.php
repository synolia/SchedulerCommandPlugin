<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Humanizer;

use Lorisleiva\CronTranslator\CronTranslator;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Sylius\Component\Locale\Context\LocaleNotFoundException;

class CronExpressionHumanizer implements HumanizerInterface
{
    public function __construct(private readonly LocaleContextInterface $localeContext)
    {
    }

    public function humanize(string $expression): string
    {
        if (!\class_exists(CronTranslator::class) || '' === $expression) {
            return $expression;
        }

        $locale = $this->getLocale();

        try {
            return CronTranslator::translate($expression, $locale);
        } catch (\Throwable) {
            return $expression;
        }
    }

    private function getLocale(): string
    {
        try {
            $locale = $this->localeContext->getLocaleCode();

            if (\strlen($locale) === 2) {
                return $locale;
            }

            return mb_substr($locale, 0, 2);
        } catch (LocaleNotFoundException) {
            return 'en';
        }
    }
}
