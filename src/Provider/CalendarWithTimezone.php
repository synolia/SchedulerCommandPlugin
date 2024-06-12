<?php

/*
 * This file is part of SyliusSchedulerCommandPlugin website.
 *
 * (c) SyliusSchedulerCommandPlugin <sylius+syliusschedulercommandplugin@monsieurbiz.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Provider;

use DateTimeImmutable;
use DateTimeZone;
use Exception;
use Sylius\Calendar\Provider\DateTimeProviderInterface;

final class CalendarWithTimezone implements DateTimeProviderInterface
{
    public function __construct(
        private ?string $timezone = null,
    ) {
    }

    /**
     * @throws Exception
     */
    public function now(): \DateTimeInterface
    {
        $timezone = $this->timezone ?? date_default_timezone_get();

        return new DateTimeImmutable(timezone: new DateTimeZone($timezone));
    }
}
