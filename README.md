[![License](https://img.shields.io/packagist/l/synolia/sylius-scheduler-command-plugin.svg)](https://github.com/synolia/SyliusSchedulerCommandPlugin/blob/master/LICENSE)
[![CI - Analysis](https://github.com/synolia/SyliusSchedulerCommandPlugin/actions/workflows/analysis.yaml/badge.svg?branch=master)](https://github.com/synolia/SyliusSchedulerCommandPlugin/actions/workflows/analysis.yaml)
[![CI - Sylius](https://github.com/synolia/SyliusSchedulerCommandPlugin/actions/workflows/sylius.yaml/badge.svg?branch=master)](https://github.com/synolia/SyliusSchedulerCommandPlugin/actions/workflows/sylius.yaml)
[![Version](https://img.shields.io/packagist/v/synolia/sylius-scheduler-command-plugin.svg)](https://packagist.org/packages/synolia/sylius-scheduler-command-plugin)
[![Total Downloads](https://poser.pugx.org/synolia/sylius-scheduler-command-plugin/downloads)](https://packagist.org/packages/synolia/sylius-scheduler-command-plugin)

<p align="center">
    <a href="https://sylius.com" target="_blank">
        <img src="https://demo.sylius.com/assets/shop/img/logo.png" />
    </a>
</p>

<h1 align="center">Scheduler Command Plugin</h1>
<p align="center">
    <img src="https://sylius.com/assets/badge-approved-by-sylius.png" width="85">
</p>
<p align="center">Schedule Symfony Commands in your Sylius admin panel.</p>

## Commands list
![Commands](/doc/images/Commands.png "Commands")

## Scheduled Commands list
![Scheduled Commands](/doc/images/ScheduledCommands.png "Scheduled Commands")

## Features

* See the list of planned command
* Add, edit, enable/disable or delete scheduled commands
* For each command, you have to define :
  * Name
  * Selected Command from the list of Symfony commands
  * Based on Cron schedule expression see [Cron formats](https://abunchofutils.com/u/computing/cron-format-helper/)
  * Output Log file prefix (optional)
  * Priority (highest is priority)
* Run the Command immediately (at the next passage of the command `synolia:scheduler-run`)
* Run a Command juste one time (from history page clic on `Launch a command` button) 
* Download or live view of log files directly from the admin panel
* Define commands with a Factory (from a Doctrine migration, for example)

## Requirements

|        | Version |
|:-------|:--------|
| PHP    | ^8.0    |
| Sylius | ^1.10   |

## Installation

1. Add the bundle and dependencies in your composer.json :

        composer config extra.symfony.allow-contrib true
        composer req synolia/sylius-scheduler-command-plugin

2. Apply migrations to your database:
   
        bin/console doctrine:migrations:migrate

3. Launch Run command in your Crontab

        * * * * * /_PROJECT_DIRECTORY_/bin/console synolia:scheduler-run

4. (optional) Showing humanized cron expression

        composer require lorisleiva/cron-translator

5. Till `symfony/recipes-contrib` is updated for the v3, you must add `sylius_scheduler_command.yaml` from `install/Application/config/{packages,routes}` to your project by respecting the same folder architecture.

        cp -R vendor/synolia/sylius-scheduler-command-plugin/install/Application/config/packages/* config/packages/
        cp -R vendor/synolia/sylius-scheduler-command-plugin/install/Application/config/routes/* config/routes/

6. Installing assets (JS and CSS) fot the plugin

        bin/console assets:install

## Usage

* Log into admin panel
* Click on `Scheduled commands` in the Scheduled commands section in main menu to manage your Scheduled commands
* Click on `Scheduled commands history` in the Scheduled commands section in main menu to see history of commands

## Fixtures
Inside sylius fixture file `config/packages/sylius_fixtures.yaml` you can add scheduled command fixtures to your suite.
```yaml
sylius_fixtures:
    suites:
        my_fixture_suite:
            fixtures:
                scheduler_command:
                    options:
                        scheduled_commands:
                            -
                                name: 'Reset Sylius'
                                command: 'sylius:fixtures:load'
                                cronExpression: '0 0 * * *'
                                logFilePrefix: 'reset'
                                priority: 0
                                enabled: true
                            -
                                name: 'Cancel Unpaid Orders'
                                command: 'sylius:cancel-unpaid-orders'
                                cronExpression: '0 0 * * *'
                                priority: 1
                                enabled: false
```

## Commands
### synolia:scheduler-run

Execute scheduled commands.

* options:
  * --id (run only a specific scheduled command)

**Run all scheduled commands :** php bin/console synolia:scheduler-run

**Run one scheduled command :** php bin/console synolia:scheduler-run --id=5

### synolia:scheduler:purge-history

Purge scheduled command history greater than {X} days old.

* options: 
  * --all (purge everything)
  * --days (number of days to keep)
  * --state (array of schedule states)
  * --dry-run

**Example to remove all finished and in error scheduled commands after 7 days :**

php bin/console synolia:scheduler:purge-history --state=finished --state=error --days=7

## Optional services
```yaml
services:
...
    # By enabling this service, it will be requested to vote after the other EveryMinuteIsDueChecker checker.
    # Using some cloud providers, even if the master cron is set to run every minute, it is actually not run that often
    # This service allows you to set a soft threshold limit, so if your provider is actually running the master cron every 5 minutes
    # This service will execute the cron if we are still in the threshold limit ONLY IF it was not already executed another time in the same range.
    #
    # CONFIGURATION SCENARIO: cron set to be run at 01:07 in the scheduler command plugin
    #
    # SCENARIO CASES AT 1 CRON PASS EVERY 5 MINUTES FROM THE PROVIDER
    # cron passes at 01:04 - 1..5 minutes: IS NOT DUE
    # cron passes at 01:05 - 1..5 minutes: IS NOT DUE
    # cron passes at 01:06 - 1..5 minutes: IS NOT DUE
    # cron passes at 01:07 - 1..5 minutes: IS DUE (but it should already be handled by EveryMinuteIsDueChecker)
    # cron passes at 01:08 - 1..5 minutes: IS DUE
    # cron passes at 01:09 - 1..5 minutes: IS DUE #should not if another has started during the threshold period
    # cron passes at 01:10 - 1..5 minutes: IS DUE #should not if another has started during the threshold period
    # cron passes at 01:11 - 1..5 minutes: IS DUE #should not if another has started during the threshold period
    # cron passes at 01:12 - 1..5 minutes: IS DUE #should not if another has started during the threshold period
    # cron passes at 01:13 - 1..5 minutes: IS NOT DUE
    Synolia\SyliusSchedulerCommandPlugin\Checker\SoftLimitThresholdIsDueChecker:
        tags:
            - { name: !php/const Synolia\SyliusSchedulerCommandPlugin\Checker\IsDueCheckerInterface::TAG_ID }
        #optionnal, default value is 5 minutes
        arguments:
            $threshold: 5 #soft limit threshold in minutes
```

## Development

See [How to contribute](CONTRIBUTING.md).

## License

This library is under the MIT license.

## Credits

Developed by [Synolia](https://synolia.com/).
