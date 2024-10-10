# Releasing a new version of Shopsys Platform

For releasing a new version of Shopsys Platform, we are leveraging `monorepo:release` command.

All the source codes and configuration of our release process can be found in the `utils/releaser` folder located in the monorepo's root.

Each step of the release process is defined as an implementation of `\Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker`,
therefore, we refer to the step definitions as to "release workers".

## Stages

The whole release process is divided into 3 stages that are run separately:

1. `release-candidate`
    - steps that are done before the release candidate branch is sent to code review and testing
    - the release workers are defined in `src/ReleaseWorker/ReleaseCandidate` folder
1. `release`
    - steps that are done during the actual release
    - the release workers are defined in `src/ReleaseWorker/Release` folder
1. `after-release`
    - steps that are done after the release
    - the release workers are defined in `src/ReleaseWorker/AfterRelease` folder

## Release command

!!! caution

    Before you start releasing, you need to mount your `.gitconfig` to `php-fpm` docker container to be able to perform automated commits within the container.<br>
    Add following line into your `docker-compose.yml` in `services -> php-fpm -> volumes` path:

    <!-- language: lang-yaml -->

        - ~/.gitconfig:/home/www-data/.gitconfig

To perform a desired stage, run the following command in the `php-fpm` docker container and follow the instructions that you'll be asked in the console.

```sh
php bin/console monorepo:release <release-number> --stage <stage> --initial-branch <initial-branch> -v
```

!!! note

    -   The "release-number" argument is the desired tag you want to release. It should always follow [the semantic versioning](https://semver.org/)
        and start with the "v" prefix, e.g., `v7.0.0`.
    -   The "initial-branch" argument is the name of branch the version has been built on e.g. `7.0`.

If you want only to display a particular stage, along with the release worker class names, add the `--dry-run` argument:

```sh
php bin/console monorepo:release <release-number> --dry-run --stage <stage> --initial-branch <initial-branch> -v
```

!!! note

    Releasing a stage is a continuously running process, so do not exit your CLI if it is not necessary.
    If you need to stop the process, you can then use `--resume-step <number-of-step>` to resume the process from the desired step.
