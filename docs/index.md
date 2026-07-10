# Shopsys Platform Knowledge Base
Welcome to Shopsys Platform Knowledge Base!
On these pages, you can find everything you need to know when you want to build your own e-commerce site based on the framework.

If you want to know more about Shopsys Platform, you can look at our [README.md]({{github.link}}/README.md).

## Need help?
If you are not able to find the desired information here, you can always ask us directly on our [GitHub Discussions](https://github.com/shopsys/shopsys/discussions) or [report an issue](https://github.com/shopsys/shopsys/issues/new) on GitHub.
If you are struggling with Docker, [Docker Troubleshooting](./docker/docker-troubleshooting.md) might help you.

## What is new and how to upgrade
* For step-by-step upgrade instructions, see the particular `UPGRADE` file
    * it is important to switch the [shopsys/shopsys monorepo](https://github.com/shopsys/shopsys/) repository to the proper branch that corresponds to the version you are upgrading to
        *  e.g., for upgrading to version `v17.0.0`, you find [the upgrading instructions](https://github.com/shopsys/shopsys/blob/17.0/UPGRADE-17.0.md) in the `17.0` branch, while for upgrading to version `v16.0.0`, you find [the upgrading instructions](https://github.com/shopsys/shopsys/blob/16.0/UPGRADE-16.0.md) in the `16.0` branch, etc.
* In `CHANGELOG` file you can find the list of all important changes in all repositories maintained in [shopsys/shopsys monorepo](https://github.com/shopsys/shopsys/)
* Thanks to our [Backward Compatibility Promise](./contributing/backward-compatibility-promise.md), it should be clear to which versions you can upgrade safely and how we plan to maintain the code in the future

## Table of Contents
* [Installation](./installation/index.md)
    * Application configuration, requirements, and installation guides for various platforms, including Docker.
* [Introduction](./introduction/index.md)
    * Information about basic concepts and terms in Shopsys Platform.
* [Model](./model/index.md)
    * Basics about model architecture, entities, ...
* [Cookbook](./cookbook/index.md)
    * Step by step how-to guides.
* [Functional](./functional/index.md)
    * How Shopsys Platform works from a user point of view.
* [Frontend](./frontend/index.md)
    * Design implementation and customization, LESS, ...
* [Administration](./administration/index.md)
    * Administration menu and grids.
* [Frontend API](./frontend-api/index.md)
    * Information about the frontend GraphQL API dedicated to connecting external storefronts or mobile apps.
* [Storefront](./storefront/index.md)
    * Documentation for demo frontend client.
* [Extensibility](./extensibility/index.md)
    * How to customize the behavior of Shopsys Platform to suit your needs.
* [Integration](./integration/index.md)
    * Information about integrating third-party applications and services.
* [AI](./ai/index.md)
    * Documentation for the built-in MCP server and AI client authentication flows.
* [Asynchronous processing](./asynchronous-processing/index.md)
    * How to use and implement asynchronous processing in Shopsys Platform.
* [Automated Testing](./automated-testing/index.md)
    * Information about available types of tests and how to run them.
* [Contributing](./contributing/index.md)
    * Guidelines and handy information for Shopsys Platform contributors.

## Frontend
* [Design implementation and Customization](./frontend/design-implementation-and-customization.md)
* [Introduction to LESS](./frontend/introduction-to-less.md)
* [Frontend Troubleshooting](./frontend/frontend-troubleshooting.md)
* [Understanding the Style Directory](./frontend/understanding-the-style-directory.md)

## FAQ
See [FAQ and Common Issues](./introduction/faq-and-common-issues.md).

## Documenting your own project
Not only does Shopsys Platform itself need documentation, but your project also deserves its own docs. The tips for writing project documentation are written in [Guidelines for Project Documentation](./project/guidelines-for-project-documentation.md).
