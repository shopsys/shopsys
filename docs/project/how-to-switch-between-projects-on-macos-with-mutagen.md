# Switching between projects on macOS with Mutagen

Switching between projects can sometimes be problematic with Mutagen.
For instance, untracked or changed files, problems with packages in the vendor, and other problems may appear after the switch.
This article contains some tried and tested recommendations on how to minimize these issues.

## Avoid using `mutagen-compose down` when switching

If you are switching between two or more projects on a regular basis, it's best to avoid using `mutagen-compose down` to stop the current project and then switch to the new one.
Instead, use `mutagen-compose stop`.
The difference is that those containers will remain suspended and not be deleted (thus taking up disk space).
Moreover, this approach will diminish or completely solve the problems described in this article's introduction.
Switching is also significantly faster.
After stopping all containers, switch to the folder containing the second project and run `mutagen-compose up -d`.

!!! note

    For this solution to work properly, ensure that your containers in `docker-compose.yml` are named according to the project. For example, rewrite all container names from `e.g., container_name: shopsys-framework-postgres` to `container_name: my-best-project-postgres`

!!! warning

    Unfortunately, this solution has one drawback. All projects stopped like this, need to be stopped again after a system restart, because they will be started again.

## Problem: Untracked / Changed files in GIT after switching

If you encounter this issue after the switch, delete the affected files or perform a hard reset to your branch using `git reset --hard <branch_name>` and `git clean -fd`

## Problem: `composer install` crashes on an error in the vendor

If you encounter this issue, try the following command:

```bash
mutagen-compose exec php-fpm rm -rf vendor
mutagen-compose exec php-fpm composer install
```
