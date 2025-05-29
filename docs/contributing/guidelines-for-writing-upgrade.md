# Guidelines for writing UPGRADE.md

Keep in mind that upgrade instructions are written for users who might not understand our system well, so the more clear they are, the more helpful they are.

- Our users work in a clone of project-base, and even when they do the upgrade, their project-base still needs to be upgraded.
  Every time you change/add anything in the project-base, write upgrade instruction how to repeat this work.
    - for anything with docker, phing, frontend, config, etc.
- There should be separate instructions for backend and storefront, use `php phing upgrade-generate` command and choose which files you would like to generate.
    - This command generates files into `upgrade-notes/` folder, which are then put together into the final `UPGRADE` file during the release of a new version.
    - In the generated files, fill in the task name, pull request ID, and all the upgrade instructions that might be handy for the project developers.
    - If there are any changes in the `project-base` folder, keep the "see #project-base-diff to update your project" phrase in the upgrade notes. This will be replaced with a link to the diff in the `project-base` repository during the release of a new version.
- Make instructions as easy to follow as possible
    - Copyable commands are great
    - _"Do this, then that"_ is a good format
    - describe all the BC breaks and provide information on how to handle them
        - e.g. _"Method MyClass::oldMethod() was removed, use MyClass::newMethod() instead"_
        - you do not need to explicitly mention changes that are easily discoverable by the standard checks or static analysis, e.g., adding a new dependency into a class constructor.
