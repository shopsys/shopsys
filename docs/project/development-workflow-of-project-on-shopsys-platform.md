# Development Workflow of Project on Shopsys Platform

## Requirements

-   Shopsys Platform [installed](../installation/index.md)
-   [GIT repository](https://git-scm.com/book/en/v2/Git-Basics-Getting-a-Git-Repository) created from Shopsys Platform [project-base](https://github.com/shopsys/project-base/)

## How to develop a new feature

1.  Create a new branch from `main` branch e.g., `my-new-feature`

1.  Develop your feature

1.  Check and automatically fix your code standards using `php phing standards-fix-diff`

    !!! hint

        In this step you were using Phing target `standards-fix-diff`.<br>
        More information about what Phing targets are and how they work can be found in [Console Commands for Application Management (Phing Targets)](../introduction/console-commands-for-application-management-phing-targets.md)

1.  Check if all tests are passing using `php phing tests`

1.  Run acceptance tests `php phing tests-acceptance`

    !!! tip

        We suggest you running acceptance tests on your Continuous Integration server because it takes several minutes to run them.

1.  [Create commit](https://git-scm.com/docs/git-commit) with a descriptive commit message about changes you have made.

    !!! tip

        We have [Guidelines for Creating Commits](../contributing/guidelines-for-creating-commits.md) for contributors to Shopsys Platform.<br>
        These guidelines suggest some best practices for creating commits that you could adopt on your project.

1.  [Push changes](https://git-scm.com/docs/git-push) to your remote GIT repository

1.  Let some colleague review your code and fix all reported problems

    !!! note

        We pay a lot of attention to code quality in Shopsys company.<br>
        Apart from automatic testing and coding standards checks at least one of the colleagues reviews your code and another colleague tests changes whether they are working properly.<br>
        We found this practices very important and we suggest them to you as part of your development process.

1.  [Rebase](https://git-scm.com/docs/git-rebase) your branch on the current version of `main` branch

    !!! note

        This leads to [clean GIT history](https://blog.shopsys.com/keep-your-git-history-clean-with-minimum-effort-4b86b5619b1).

1.  [Merge](https://git-scm.com/docs/git-merge) your branch (e.g., `my-new-feature`) into `main` branch.

1.  Push `main` branch
