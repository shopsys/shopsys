# Post-Deploy Tasks

Post-deploy tasks let you run code as part of the third deploy phase (`build-deploy-part-3-non-blocking`). They run after the maintenance page is turned off, so they are non-blocking for shop visitors. Tasks can be one-shot data backfills (run on the next deploy and never again), or recurring jobs (run on every deploy).

If you are looking for schema migrations, see [Database Migrations](database-migrations.md). Post-deploy tasks complement migrations: migrations change the schema during the maintenance window, post-deploy tasks do non-blocking application-level work afterwards.

## How they run

The console command `deploy:post-deploy:run-tasks` is invoked by the `run-post-deploy-tasks` phing target, which is a dependency of `build-deploy-part-3-non-blocking`. It does the following:

1. Loads `shopsys_framework.post_deploy.tasks` from the Symfony container configuration. Framework defaults are prepended by the bundle, and project configuration can override individual task entries by name.
2. Validates the merged entries during container compilation and sorts them by priority.
3. For each task, decides whether to run it based on its `run` mode and (for `one_time` tasks) whether it has already been recorded as executed in the `one_time_post_deploy_tasks` table.
4. Stops on the first failure. The failed task is left unmarked, so re-running the command picks up where it left off.

## Registering a task

A post-deploy task is a service implementing `Shopsys\FrameworkBundle\Component\PostDeploy\Task\PostDeployTaskInterface`. The interface has a single method:

```php
public function run(SymfonyStyle $style): void;
```

Place the class somewhere in your project's `src/` tree. Any class whose file name ends in `Task.php` is auto-registered as a service by the project's `services.yaml`, so no manual DI configuration is needed.

Then add an entry to `shopsys_framework.post_deploy.tasks` in `app/config/packages/shopsys_framework.yaml`:

```yaml
shopsys_framework:
    post_deploy:
        tasks:
            backfill_legacy_customer_flags:
                run: one_time
                priority: 50
                service: App\PostDeploy\Task\BackfillLegacyCustomerFlagsTask
```

## Configuration format

The `tasks` node is a mapping keyed by task name (snake_case, lowercase, must start with a letter; serves as the DB key for `one_time` tasks). Each value is a mapping with these fields:

| field      | required                             | type                              | notes                                              |
| ---------- | ------------------------------------ | --------------------------------- | -------------------------------------------------- |
| `run`      | yes                                  | `one_time` \| `always` \| `never` | determines execution semantics (see below)         |
| `service`  | when `run` is `one_time` or `always` | FQCN                              | the service implementing `PostDeployTaskInterface` |
| `priority` | optional                             | integer                           | higher runs earlier; default `0`                   |

## `run` modes

- **`one_time`** — runs once, then is recorded in the `one_time_post_deploy_tasks` table. Subsequent deploys see the row and skip the task. Used for data backfills or any other operation that should happen exactly once across the application's lifetime.
- **`always`** — runs on every deploy, no DB tracking. Used for recurring maintenance like cache warmup or sanity checks.
- **`never`** — registered but not executed. Used to opt out of a task declared by the framework. Only `run` is required for `never` entries.

## Priority and execution order

Tasks are sorted by `priority` descending (higher runs earlier). When two tasks share the same priority, the order in the merged Symfony configuration breaks the tie: framework-declared entries come first (via the bundle's `prepend()`), then project entries in the order they appear in `shopsys_framework.yaml`. An override keeps the position of the framework entry it replaces.

`priority: 0` is the default. Pick concrete values for entries that need to interleave with framework tasks; for project-only tasks the default is usually fine.

## Overriding a framework task

A project overrides a framework-shipped task by declaring an entry under the same key in `app/config/packages/shopsys_framework.yaml`. Symfony merges the configuration, so you only need to specify values that differ from the framework default.

Note: framework-shipped entries supply `run` (which is otherwise required), so a partial override that omits `run` works only for keys that already exist in the framework's prepend. For project-only tasks, `run` must always be specified.

Disable the framework's file-size recalculation:

```yaml
shopsys_framework:
    post_deploy:
        tasks:
            recalculate_file_sizes:
                run: never
```

Keep the framework task running but bump its priority above another:

```yaml
shopsys_framework:
    post_deploy:
        tasks:
            recalculate_file_sizes:
                priority: 999
```

A `run: never` override does not delete any existing `one_time_post_deploy_tasks` row — it only prevents future executions. Flipping the same entry back to `run: one_time` later causes the runner to skip it as already-executed.

## Failure handling

If a task throws, the runner:

1. Throws `PostDeployTaskFailedException` with the task name and run-mode in the message.
2. Stops processing further tasks.

Re-running the command after the underlying issue is fixed continues from where it stopped: completed `one_time` tasks are skipped, the previously failing one is retried.

## Manual invocation

Run all registered post-deploy tasks ad-hoc:

```bash
docker compose exec php-fpm php bin/console deploy:post-deploy:run-tasks -vv
```
