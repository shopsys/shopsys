# Contributing

You can take part in making Shopsys Framework better.

* [Create a pull request](https://github.com/shopsys/shopsys/compare)
* [Report an issue](https://github.com/shopsys/shopsys/issues/new)
* [Backward Compatibility Promise](https://docs.shopsys.com/en/latest/contributing/backward-compatibility-promise/)
* [Guidelines for Working with Monorepo](https://docs.shopsys.com/en/7.3/introduction/monorepo/)
* [Guidelines for Creating Commits](https://docs.shopsys.com/en/7.3/contributing/guidelines-for-creating-commits/)
* [Guidelines for Writing Documentation](https://docs.shopsys.com/en/7.3/contributing/guidelines-for-writing-documentation/)
* [Guidelines for Pull Request](https://docs.shopsys.com/en/7.3/contributing/guidelines-for-pull-request/)
* [Guidelines for Dependencies](https://docs.shopsys.com/en/7.3/contributing/guidelines-for-dependencies/)
* [Guidelines for writing UPGRADE.md](https://docs.shopsys.com/en/7.3/contributing/guidelines-for-writing-upgrade/)
* [Merging on Github](https://docs.shopsys.com/en/7.3/contributing/merging-on-github/)
* [Releasing a new version of Shopsys Framework monorepo](https://docs.shopsys.com/en/7.3/contributing/releasing-a-new-version-of-shopsys-framework/)
* [Code Quality Principles](https://docs.shopsys.com/en/7.3/contributing/code-quality-principles/)

For your code to be accepted, you should follow our guidelines mentioned above,
and the code must pass [coding standards](https://docs.shopsys.com/en/7.3/contributing/coding-standards/) checks and tests:
```
php phing standards tests tests-acceptance
```

Your code may not infringe the copyrights of any third party.
If you are changing a composer's dependency in composer.json or you are changing the npm dependencies in package.json, you need to reflect this change into a list of [Open Source License Acknowledgements and Third Party Copyrights](./open-source-license-acknowledgements-and-third-party-copyrights.md).
Apply the same procedure if you make the changes in Dockerfile or docker-compose.yml files.

These rules ensure that the code will remain consistent and the project is maintainable in the future.

*Tip: Read more about automatic checks in [Console Commands for Application Management (Phing Targets)](https://docs.shopsys.com/en/7.3/introduction/console-commands-for-application-management-phing-targets/) and [Running Acceptance Tests](https://docs.shopsys.com/en/7.3/introduction/running-acceptance-tests/).*
