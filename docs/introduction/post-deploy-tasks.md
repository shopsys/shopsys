# Post-Deploy Tasks

Post-deploy tasks let you run code as part of the third deploy phase (`build-deploy-part-3-non-blocking`). They run after the maintenance page is turned off, so they are non-blocking for shop visitors. Tasks can be one-shot data backfills (run on the next deploy and never again), or recurring jobs (run on every deploy).

If you are looking for schema migrations, see [Database Migrations](database-migrations.md). Post-deploy tasks complement migrations: migrations change the schema during the maintenance window, post-deploy tasks do non-blocking application-level work afterwards.

## How they run

The console command `shopsys:post-deploy:run-tasks` is invoked by the `run-post-deploy-tasks` phing target, which is a dependency of `build-deploy-part-3-non-blocking`. It does the following:

1. Loads the framework's `post_deploy_tasks.yaml`, then the project's `app/config/post_deploy_tasks.yaml`. Project entries with the same key as a framework entry replace the framework entry wholesale.
2. Validates the merged entries and sorts them by priority.
3. For each task, decides whether to run it based on its `run` mode and (for `one_time` tasks) whether it has already been recorded as executed in the `one_time_post_deploy_tasks` table.
4. Stops on the first failure. The failed task is left unmarked, so re-running the command picks up where it left off.

## Registering a task

A post-deploy task is a service implementing `Shopsys\FrameworkBundle\Component\PostDeploy\Task\PostDeployTaskInterface`. The interface has a single method:

```php
public function run(SymfonyStyle $style): void;
```

Place the class somewhere in your project's `src/` tree. Any class whose file name ends in `Task.php` is auto-registered as a service by the project's `services.yaml`, so no manual DI configuration is needed.

Then add an entry to `app/config/post_deploy_tasks.yaml`. The file is a top-level mapping where each key is the task name (same shape as Symfony's `services.yaml`):

```yaml
backfill_legacy_customer_flags:
    run: one_time
    priority: 50
    service: App\PostDeploy\Task\BackfillLegacyCustomerFlagsTask
```

## YAML format

The root is a mapping keyed by task name (snake_case, lowercase, must start with a letter; serves as the DB key for `one_time` tasks). Each value is a mapping with these fields:

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

Tasks are sorted by `priority` descending (higher runs earlier). When two tasks share the same priority, the file load order breaks the tie (framework first, then project), and the declaration order within a file breaks the final tie.

`priority: 0` is the default. Pick concrete values for entries that need to interleave with framework tasks; for project-only tasks the default is usually fine.

## Overriding a framework task

A project overrides a framework-shipped task by declaring an entry under the same key in `app/config/post_deploy_tasks.yaml`. Because the project file is loaded after the framework file, the project entry wins — exactly like overriding a service definition in Symfony's `services.yaml`. The override is **full replacement**: fields are not merged, so the replacement entry must satisfy the validation rules on its own.

Disable the framework's file-size recalculation:

```yaml
recalculate_file_sizes:
    run: never
```

Keep the framework task running but bump its priority above another:

```yaml
recalculate_file_sizes:
    run: one_time
    priority: 999
    service: Shopsys\FrameworkBundle\Component\PostDeploy\Task\RecalculateFileSizesTask
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
docker compose exec php-fpm php bin/console shopsys:post-deploy:run-tasks -vv
```
