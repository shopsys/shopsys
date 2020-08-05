# Cron

## Basics
Cron is a tool to run background jobs and is essential for the production environment.
Periodically executed Cron modules recalculate visibility, generate XML feeds and sitemaps, provide error reporting etc.

By default you can configure your own cron configurations in `src/Shopsys/ShopBundle/Resources/config/services/cron.yml` file.

## Default Cron Commands
There is some prepared configuration for Shopsys Framework in a file `src/Resources/config/services/cron.yml` in `FrameworkBundle`.

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
The constant `Shopsys\FrameworkBundle\Component\Cron\CronFacade::TIMEOUT_SECONDS` set the default timeout to 240 seconds.
This value can be changed by redeclaration of the constant `TIMEOUT_SECONDS` in the extended class.

That means, if the time needed to run all planned cron modules is higher than 240s, not all cron modules will be run in a current iteration.
That's usually not a problem as long-running cron modules are not executed every iteration (eg. every 5 minutes),
but in some cases, the overall time of the "every 5 minutes" cron modules can be higher (for example considerable amount of products to export to Elasticsearch).
Then it's possible, some cron modules will never be run.

It's crucial to monitor your crons and, if necessary, split them into [multiple Cron Instances](#multiple-cron-instances).
