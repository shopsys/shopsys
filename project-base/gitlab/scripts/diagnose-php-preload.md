# Alpha PHP-FPM preload diagnostics

Run the optional **diagnose:alpha-php-preload** job manually in an alpha branch
pipeline. It uses the existing deploy runner, `KUBE_DEV` and the `devel-alpha`
environment's `PROJECT_NAME` (an `*-alpha` or `*-alpha-*` namespace). No new CI
variable is required. The job does not depend on a build or deploy job and uses
[`environment.action: verify`](https://docs.gitlab.com/ci/environments/#access-an-environment-for-preparation-or-verification-purposes),
so it does not record a deployment. It shares the
alpha deploy resource group to avoid inspecting pods during a CI deploy.

The job inspects **currently running pods**, even if the pipeline's build was not
deployed. Check the logged image ID, container start time and restart count before
comparing results. It inspects all running PHP-FPM replicas and continues with the
remaining replicas if one fails. A diagnostic failure is visible in the job but
does not block the pipeline.

The installed CacheTool sends one request to each pod's PHP-FPM listener. It
creates a temporary PHP file in `/tmp`, outside the webroot, and removes it when
the request completes (a forcibly terminated process may leave that file). The
probe does not bootstrap Symfony, deploy, restart, warm up or clear any cache.
Like any FPM request, it uses a worker briefly and may affect OPcache counters.
The request has a 30-second limit and the job has a five-minute limit.

## Reading the job log

- `sapi: fpm-fcgi` confirms the result comes from FPM, not CLI PHP.
- `directives.opcache.preload` shows the effective preload entry point. A path
  alone does **not** prove Symfony was preloaded.
- `preload.script_count`, `class_count` and
  `symfony_preload_script_present` show what FPM actually preloaded. Missing
  statistics with an available OPcache status means PHP did not report preload
  statistics; it is not a measurement error or proof of the underlying cause.
- `files` reports whether the wrapper and generated Symfony preload file exist
  **now**. Their timestamps and `opcache_statistics.start_time` (Unix seconds)
  help investigate startup ordering. Current file existence alone does not prove
  the generated file existed when FPM started.
- `cache_full`, `memory_usage` and `opcache_statistics` show memory pressure,
  cache hits/misses and restarts. These are cumulative OPcache counters, **not**
  Redis cache metrics or per-request PHP execution times.

The log excludes environment variables, credentials and a full cached-script or
class list. If the image lacks CacheTool or OPcache status is unavailable, the job
fails explicitly rather than substituting CLI measurements. This diagnostic
cannot by itself prove that missing preload caused a particular slow request.
