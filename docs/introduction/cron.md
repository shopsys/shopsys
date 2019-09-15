# Cron

## Basics
Cron is a tool to run background jobs and is essential for the production environment.
Periodically executed Cron modules recalculate visibility, generate XML feeds and sitemaps, provide error reporting etc.

By default you can configure your own cron configurations in `config/services/cron.yml` file.

You can disable specific Cron module in database table `cron_modules` where you need to set `enabled` to false.

If you want to show Cron overview table for non superadmin users you need to override `DefaulController::getCronGridViews` and remove superadmin protection:
```diff
protected function getCronGridViews(): ?array
{
-   if ($this->isGranted(Roles::ROLE_SUPER_ADMIN) === false) {
-       return null;
-   }

    ...
}
```

!!! note
    All default crons are translated only to English. If you want to translate it to another language, you need to override `src/Resources/config/services/cron.yml` in `FrameworkBundle` and set `readableName` property.

## Default Cron Commands
There is some prepared configuration for Shopsys Framework in a file [`src/Resources/config/services/cron.yml`](https://github.com/shopsys/framework/blob/master/src/Resources/config/services/cron.yml) in `FrameworkBundle`.

## Running Cron Jobs
Do not forget to set up a cron on your server to execute [`php phing cron`](../introduction/console-commands-for-application-management-phing-targets.md#cron) every 5 minutes.

## Multiple Cron Instances
By default, all cron jobs are run as part of one, default, instance.
However, you may want to have several instances to be able to run, for example, lots of transfers from/into ERP systems and these transfers could block other cron processes.
Separating the cron jobs into two (or more) cron instances allows you to run some jobs in parallel.

The instance of cron is actually a named group of cron jobs.

You can learn how to set up multiple cron instances in [Working with Multiple Cron Instances](../cookbook/working-with-multiple-cron-instances.md) cookbook.
