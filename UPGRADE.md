# Upgrade

## from ^2.0 to ^3.0

### Fixtures
* `logFile` field must be renamed to `logFilePrefix` and must not end with the file extension

Also, for old existing schedules in your database, please remove the log file extension in column `logFilePrefix`.

## from 3.6 to 3.7

* The constructors of `Synolia\SyliusSchedulerCommandPlugin\Checker\EveryMinuteIsDueChecker` and `Synolia\SyliusSchedulerCommandPlugin\Checker\SoftLimitThresholdIsDueChecker` has been modified, a new argument has been added :

   ```php
    public function __construct(
        // ...
        private ?DateTimeProviderInterface $dateTimeProvider = null,
    )
    ```
