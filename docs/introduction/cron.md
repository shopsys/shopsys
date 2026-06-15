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

## Sentry Cron Monitoring

Cron modules can opt into [Sentry Cron Monitoring](https://docs.sentry.io/product/crons/) so that missed runs, failures, and overrunning jobs are reported to Sentry (and from there to alert channels like Slack):

```yaml
Shopsys\FrameworkBundle\Model\Sitemap\SitemapCronModule:
    tags:
        - {
              name: shopsys.cron,
              cron: '0 4 * * *',
              instanceName: export,
              readableName: 'Generate Sitemap',
              sentryMonitoring: true,
              sentryCheckinMargin: 5,
              sentryFailureThreshold: 3,
          }
```

When `sentryMonitoring` is enabled, an `in_progress` check-in is sent when the module starts and an `ok`/`error` check-in when it finishes.
The Sentry monitor is created and updated automatically with a schedule matching the module's cron expression, so no manual setup in Sentry is needed.

The `shopsys.cron` tag supports these monitoring attributes:

| Attribute                 | Meaning                                                                                                                                                                                                |
| ------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| `sentryMonitoring`        | enables monitoring for the module (default `false`)                                                                                                                                                    |
| `sentryMaxRuntime`        | minutes a single run may take before Sentry marks the check-in as timed out; defaults to `timeout_iterated_cron_sec` (rounded up to whole minutes) for iterated modules and to `30` for simple modules |
| `sentryCheckinMargin`     | minutes Sentry waits for the start check-in after the scheduled time before reporting a missed run; defaults to the instance's `run_every_min`                                                         |
| `sentryFailureThreshold`  | number of consecutive failed or missed check-ins before Sentry creates an issue                                                                                                                        |
| `sentryRecoveryThreshold` | number of consecutive successful check-ins before Sentry resolves the issue                                                                                                                            |

Things to be aware of:

- check-ins are only sent when Sentry is configured via the `SENTRY_DSN` environment variable; without a DSN, they are no-ops
- the monitor schedule is evaluated in the `shopsys.cron_timezone` timezone (the server timezone when the parameter is not set), the same timezone used for evaluating cron expressions
- modules within one instance run sequentially, so a module can start several minutes after its scheduled time when earlier modules run long — set `sentryCheckinMargin` generously for modules with a fixed schedule time
- the minute field of a monitored module's cron expression must align with the instance's `run_every_min` grid — for example, `7 * * * *` never runs with `run_every_min: 5`, yet Sentry would expect a check-in every hour and report it as missed
- a disabled monitored module reports a healthy run whenever its schedule fires, so intentionally disabling a module does not trigger false "missed run" alerts
- an iterated module that is suspended and resumed reports each chunk as a separate successful check-in
- when you remove `sentryMonitoring` from a module, the existing monitor stays in Sentry and keeps alerting about missed check-ins — delete or mute it in the Sentry UI
