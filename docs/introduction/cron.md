# Cron

## Basics

Cron is a tool to run background jobs and is essential for the production environment.
Periodically executed Cron modules recalculate visibility, generate XML feeds and sitemaps, provide error reporting, etc.

By default, you can configure your own cron configurations in `app/config/cron.yaml` file.

Cron modules use **standard crontab expressions** (5 fields: minute, hour, day of month, month, day of week) for scheduling.
This means you can use all standard cron syntax features including ranges (`1-5`), lists (`0,15,30`), steps (`*/10`), and combinations thereof.

For example:

```yaml
App\Cron\MyModule:
    tags:
        - { name: shopsys.cron, cron: '*/15 * * * *', instanceName: default, readableName: 'My task' }
```

This would run the module every 15 minutes. See [crontab syntax](https://en.wikipedia.org/wiki/Cron#Cron_expression) for the full expression reference.

If you want to show Cron overview table for non-superadmin users you need add parameter `shopsys.display_cron_overview_for_superadmin_only` set to `false` in your `config/parameters.yaml`:

!!! note

    Cron frequency descriptions shown in admin are automatically translated to the logged-in administrator's locale. If you want to override the description, you can set `readableFrequency` property for cron in `app/config/cron.yaml`.

## Default Cron Commands

There is some prepared configuration in a file [`app/config/cron.yaml`]({{github.link}}/project-base/app/config/cron.yaml) in `project-base`.

!!! note

    Times in cron expressions in [`app/config/cron.yaml`]({{github.link}}/project-base/app/config/cron.yaml) are evaluated in the timezone set in `shopsys.cron_timezone` parameter in [`config/parameters_common.yaml`]({{github.link}}/project-base/app/config/parameters_common.yaml) file.

## Running Cron Jobs

Do not forget to set up a cron on your server to execute [`php phing cron`](../introduction/console-commands-for-application-management-phing-targets.md#cron) every 5 minutes.

## Multiple Cron Instances

By default, all cron jobs are run as part of one, default, instance.
However, you may want to have several instances to be able to run, for example, lots of transfers from/into ERP systems and these transfers could block other cron processes.
Separating the cron jobs into two (or more) cron instances allows you to run some jobs in parallel.

The instance of cron is actually a named group of cron jobs.

You can learn how to set up multiple cron instances in [Working with Multiple Cron Instances](../cookbook/working-with-multiple-cron-instances.md) cookbook.

!!! note

    For testing purposes (e.g., on CI server) there is a special phing target `run-all-crons-serially` that allows you to run all the CRON modules serially.

## Cron Limitations

One cron run can only be run for a limited time by default to prevent high-memory usage of long-running jobs in PHP.
You can configure the behaviour of each instance in `app/config/cron.yaml`:

```yaml
parameters:
    cron_instances:
        default:
            run_every_min: 5
            timeout_iterated_cron_sec: 240
            stop_on_failure: true
```

That means, if the time needed to run all planned cron modules is higher than `240 seconds`, not all cron modules will be run in a current iteration.
That's usually not a problem as long-running cron modules are not executed every iteration set in `run_every_min` with default to `5 minutes`,
but in some cases, the overall time of the "every 5 minutes" cron modules can be higher (for example, considerable amount of products to export to Elasticsearch).
Then it's possible, some cron modules will never be run.

It's crucial to monitor your crons and, if necessary, update their periodicity and timeout or split them into [multiple Cron Instances](#multiple-cron-instances).

The `stop_on_failure` option controls whether the instance stops executing subsequent cron modules when one module fails.
It defaults to `true`.
Set it to `false` for instances whose modules are independent of each other so that a failure in one module does not prevent the remaining modules from running.

!!! note

    Crons implementing `Shopsys\Plugin\Cron\IteratedCronModuleInterface` with the correct implementation of iterate, wakeUp, and sleep methods will be checked during every iteration if their memory limit is not approaching and if so, they will be stopped and started again in the next iteration.
