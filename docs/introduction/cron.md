# Cron

## Basics
Cron is a tool to run background jobs and is essential for the production environment.
Periodically executed Cron modules recalculate visibility, generate XML feeds and sitemaps, provide error reporting etc.

By default you can configure your own cron configurations in `config/services/cron.yaml` file.

If you want to show Cron overview table for non-superadmin users you need add parameter `shopsys.display_cron_overview_for_superadmin_only` set to `false` in your `config/parameters.yaml`:

!!! note
    All default crons are translated only to English. If you want to translate it to another language, you need to set `readableName` property for cron in `config/services/cron.yaml`.

!!! note
    If you have different cron frequency set using crontab and you want to set readable frequency manually, you can use `readableFrequency` property for cron in `config/services/cron.yaml`.

## Default Cron Commands
There is some prepared configuration in a file [`config/services/cron.yaml`](https://github.com/shopsys/project-base/blob/master/config/services/cron.yaml) in `project-base`.
!!! note
    Hours set in [`config/services/cron.yaml`](https://github.com/shopsys/project-base/blob/master/config/services/cron.yaml) are consider to be in timezone set in `shopsys.cron_timezone` parameter in [`config/parameters_common.yaml`](https://github.com/shopsys/project-base/blob/master/config/parameters_common.yaml) file.

## Running Cron Jobs
Do not forget to set up a cron on your server to execute [`php phing cron`](../introduction/console-commands-for-application-management-phing-targets.md#cron) every 5 minutes.

## Multiple Cron Instances
By default, all cron jobs are run as part of one, default, instance.
However, you may want to have several instances to be able to run, for example, lots of transfers from/into ERP systems and these transfers could block other cron processes.
Separating the cron jobs into two (or more) cron instances allows you to run some jobs in parallel.

The instance of cron is actually a named group of cron jobs.

You can learn how to set up multiple cron instances in [Working with Multiple Cron Instances](../cookbook/working-with-multiple-cron-instances.md) cookbook.

## Cron Limitations
One cron run can only be run for a limited time by default to prevent high memory usage of long-running jobs in PHP.
In `shopsys/framework/src/Resources/config/cron.yaml` is set the default timeout to `240 seconds`:

```yaml
parameters:
    cron_instances:
        default:
            run_every_min: 5
            timeout_iterated_cron_sec: 240
```

That means, if the time needed to run all planned cron modules is higher than `240 seconds`, not all cron modules will be run in a current iteration.
That's usually not a problem as long-running cron modules are not executed every iteration set in `run_every_min` with default to `5 minutes`,
but in some cases, the overall time of the "every 5 minutes" cron modules can be higher (for example, considerable amount of products to export to Elasticsearch).
Then it's possible, some cron modules will never be run.

It's crucial to monitor your crons and, if necessary update their periodicity and timeout or split them into [multiple Cron Instances](#multiple-cron-instances).
