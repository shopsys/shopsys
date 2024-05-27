# Shopsys Platform Knowledge Base

Welcome to Shopsys Platform Knowledge Base!
On these pages, you can find everything you need to know when you want to build your own e-commerce site based on the framework.

If you want to know more about Shopsys Platform, you can look at our [README.md]({{github.link}}/README.md).

## Need help?

If you are not able to find the desired information here, you can always:

- Ask us directly on our [public slack](https://join.slack.com/t/shopsysframework/shared_invite/zt-11wx9au4g-e5pXei73UJydHRQ7nVApAQ)
- [Report an issue](https://github.com/shopsys/shopsys/issues/new) on GitHub
- [Open a discussion](https://github.com/shopsys/shopsys/discussions) on GitHub

Feel free to reach out, and we'll be happy to assist you!

If you are struggling with Docker, [Docker Troubleshooting](./docker/docker-troubleshooting.md) might help you.

## What is new and how to upgrade

* On the [Releases on GitHub](https://github.com/shopsys/shopsys/releases) you can find the list of all important changes in released versions in all repositories maintained in [shopsys/shopsys monorepo](https://github.com/shopsys/shopsys/)
* For step-by-step upgrade instructions, see [UPGRADE.md]({{github.link}}/UPGRADE-15.0.md)
* Thanks to our [Backward Compatibility Promise](./contributing/backward-compatibility-promise.md), it should be clear to which versions you can upgrade safely and how we plan to maintain the code in the future

## Table of Contents

1. Introduction
    * Overview
    * Key Concepts
    * Getting Started
2. Installation
    * System Requirements
    * Docker Installation
    * Native Installation
    * Troubleshooting
3. Configuration
    * Application Configuration
    * Environment Variables
    * CDN Setup
4. Development Guide
    * Project Structure
    * Development Workflow
    * Console Commands (Phing Targets)
    * Database Migrations
5. Model Architecture
    * Introduction to Model Architecture
    * Working with Entities
    * Custom Entities
    * Elasticsearch Integration
6. Frontend Development
    * Design Implementation (LESS, Tailwind CSS)
    * Npm and Webpack Configuration
    * Frontend Troubleshooting
7. Administration
    * Administration Menu
    * Grid Customization
    * Adding New Pages and Features
8. Cookbook
    * Step-by-step Guides for Common Tasks
9. Asynchronous Processing




* [Installation](./installation/index.md)
    * Application requirements, and installation guides for various platforms, including Docker.
* [Working on your project](./project/index.md)
    * Information about the project structure, development workflow, and database migrations.
* [Configuration](./configuration/index.md)
    * Information about application configuration, environment variables, and CDN setup.
* [Basic concepts](./basic-concepts/index.md)
    * Overview of key concepts and terms in Shopsys Platform.
* [Functional](./functional/index.md)
    * How Shopsys Platform works from a user point of view.
* [Extensibility](./extensibility/index.md)
    * How to customize the behavior of Shopsys Platform to suit your needs.
* [Storefront](./storefront/index.md)
    * Documentation for demo frontend client.
* [Automated Testing](./automated-testing/index.md)
    * Information about available types of tests and how to run them.
* [Integrations](./integrations/index.md)
    * Information about integrating Shopsys Platform with other systems.
* [Contributing](./contributing/index.md)
    * Guidelines and handy information for Shopsys Platform contributors.

* [Introduction](./introduction/index.md)
    * Information about basic concepts and terms in Shopsys Platform.
* [Model](./model/index.md)
    * Basics about model architecture, entities, ...
* [Cookbook](./cookbook/index.md)
    * Step-by-step how-to guides.

* [Frontend](./frontend/index.md)
    * Design implementation and customization, LESS, ...
* [Administration](./administration/index.md)
    * Administration menu and grids.
* [Frontend API](./frontend-api/index.md)
    * Information about the frontend GraphQL API dedicated to connecting external storefronts or mobile apps.
* [Storefront](./storefront/index.md)
    * Documentation for demo frontend client.

* [Asynchronous processing](./asynchronous-processing/index.md)
    * How to use and implement asynchronous processing in Shopsys Platform.



## Frontend

* [Design implementation and Customization](./frontend/design-implementation-and-customization.md)
* [Introduction to LESS](./frontend/introduction-to-less.md)
* [Frontend Troubleshooting](./frontend/frontend-troubleshooting.md)
* [Understanding the Style Directory](./frontend/understanding-the-style-directory.md)

## FAQ

See [FAQ and Common Issues](./introduction/faq-and-common-issues.md).

## Documenting your own project

Not only does the Shopsys Platform itself need documentation, but your project also deserves its own docs.
The tips for writing project documentation are written in [Guidelines for Project Documentation](./project/guidelines-for-project-documentation.md).
