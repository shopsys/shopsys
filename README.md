# Shopsys Platform

[![MCP Toplist](https://mcptoplist.com/badge/pulsemcp%2Fshopsys.svg)](https://mcptoplist.com/server/pulsemcp%2Fshopsys)

[![Build Status - master](https://github.com/shopsys/shopsys/workflows/Docker%20build/badge.svg?branch=master)](https://github.com/shopsys/shopsys/actions?query=workflow%3A%22Docker+build%22+branch%3A%22master%22)

Shopsys Platform is a **fully functional e-commerce solution for businesses transitioning into tech companies with their own software development team**.
It contains the most common B2C and B2B features for online stores, and its infrastructure is prepared for high scalability.

Shopsys Platform is **the fruit of our 16 years of experience in creating custom-made online stores, and it’s dedicated to the best in-house devs teams who work with online stores with tens of millions of Euros of turnover per year**.

Our platform’s **architecture is modern and corresponds to the latest trends in the production of software for leading e-commerce solutions**.
Deployment and scaling of our system are comfortable thanks to containerization and orchestration concepts (**Docker, Kubernetes**).
The platform is based on one of the best PHP frameworks on the market - **Symfony**.

## Shopsys Platform Infrastructure

![Shopsys Platform Infrastructure](./docs/img/shopsys-platform-infrastructure.png 'Shopsys Platform Infrastructure')

## Current State and Roadmap

### Current State

Shopsys Platform is a fully functional e-commerce solution with all the basic functionality all e-commerce sites need:

- Product catalog
- Registered customers
- Basic orders management
- Back-end administration
- Front-end [full-text search](https://docs.shopsys.com/en/latest/model/front-end-product-searching/) and [product filtering](https://docs.shopsys.com/en/latest/model/front-end-product-filtering/)
- 3-step ordering process
- Basic CMS
- Support for several currencies, [languages, and domains](https://docs.shopsys.com/en/latest/introduction/domain-multidomain-multilanguage/)
- Full friendly URL for main entities and full control over SEO aspects of online presence
- Performance optimization through Elasticsearch, Redis, PostgreSQL
- Full core upgradability
- GDPR compliance
- Preparation for scalability
- Manifest for orchestration via [Kubernetes](https://docs.shopsys.com/en/latest/kubernetes/introduction-to-kubernetes/)
- Support to easier [deployment to Google Cloud via Terraform](https://docs.shopsys.com/en/latest/kubernetes/how-to-deploy-ssfw-to-google-cloud-platform/)
- Basic GraphQL Front-end API for implementation of own front-end and PWA

### Plans for next releases

An overview of our goals and priorities can be found in our [Shopsys Platform Roadmap](https://www.shopsys.com/product-roadmap/)

## How to Start a New Project

The _shopsys/shopsys_ package is a monolithic repository, a single development environment, for the management of all parts of Shopsys Platform.
See more information about the monorepo approach in [the Monorepo article](https://docs.shopsys.com/en/latest/introduction/monorepo/).

For the purposes of building a new project, use the [shopsys/project-base](https://github.com/shopsys/project-base),
which is fully ready as the base for building your Shopsys Platform project.

For more detailed instructions, follow one of the installation guides:

- [Installation Guide](https://docs.shopsys.com/en/latest/installation/installation-guide/)
- [Installation on the production server](https://docs.shopsys.com/en/latest/installation/installation-using-kubernetes-on-production-server/)

## Documentation

For documentation of Shopsys Platform itself, see [Shopsys Platform Knowledge Base](https://docs.shopsys.com/en/latest/).

For the frequently asked questions, see [FAQ and Common Issues](https://docs.shopsys.com/en/latest/introduction/faq-and-common-issues/).

## Contributing

Let us know if you have some ideas or want to help improve Shopsys Platform!
We are looking forward to your insights, feedback, and improvements.
Thank you for helping us make Shopsys Platform better.

All the necessary information is in our [Contribution Guide](./CONTRIBUTING.md).

## Support

What to do when you are in troubles or need some help?
The best way is to ask in our [GitHub Discussions](https://github.com/shopsys/shopsys/discussions).

If you are experiencing problems during installation or running Shopsys Platform on Docker,
please see our [Docker troubleshooting](https://docs.shopsys.com/en/latest/docker/docker-troubleshooting/).

Or ultimately, just [report an issue](https://github.com/shopsys/shopsys/issues/new).

## License

The core of the Shopsys Platform is provided under the **Development License**.  
This license allows you to:

- **use the software free of charge** for **evaluation, development, testing, and experimentation**,
- **modify the source code** for these non-production purposes.

The Development License **does not permit** using the platform to run a live e-commerce site, process real orders, or operate the software in any **commercial or production environment**.

To deploy the Shopsys Platform for a real online store or any commercial use case, a **Commercial License** is required.  
For licensing inquiries, please contact us via [contact form](https://www.shopsys.com/contact/).

Some of Shopsys Platform repositories including [HTTP smoke testing](https://github.com/shopsys/http-smoke-testing) and [Monorepo Tools](https://github.com/shopsys/monorepo-tools) are distributed under standard MIT license so generally you can use it without any restriction. The information about the license is placed in the LICENSE file in the root of each repository.

Shopsys Platform also uses some third-party components and images which are licensed under their own respective licenses.
The list of these licenses is summarized in [Open Source License Acknowledgements and Third Party Copyrights](./open-source-license-acknowledgements-and-third-party-copyrights.md).
