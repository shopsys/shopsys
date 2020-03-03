# Shopsys Framework
[![Build Status](https://travis-ci.org/shopsys/shopsys.svg?branch=master)](https://travis-ci.org/shopsys/shopsys)

Shopsys Framework is a **fully functional ecommerce platform for businesses transitioning into tech-companies with their own software development team**.
It contains the most common B2C and B2B features for online stores, and its infrastructure is prepared for high scalability.

Shopsys Framework is **the fruit of our 16 years of experience in creating custom-made online stores and it’s dedicated to best in-house devs teams who work with online stores with tens of millions of Euros of turnover per year**.

Our platform’s **architecture is modern and corresponds to the latest trends in the production of software for leading ecommerce solutions**.
Deployment and scaling of our system are comfortable thanks to the use of the containerization and orchestration concepts (**Docker, Kubernetes**).
The platform is based on one of the best PHP frameworks on the market - **Symfony**.

## Shopsys Framework Infrastructure
![Shopsys Framework Infrastructure](./docs/img/shopsys-framework-infrastructure.png 'Shopsys Framework Infrastructure')

## Current State and Roadmap

### Current State

Shopsys Framework is fully functional e-commerce platform with all basic functionality all e-commerce sites needs:
* Product catalogue
* Registered customers
* Basic orders management
* Back-end administration
* Front-end [full-text search](https://docs.shopsys.com/en/latest/model/front-end-product-searching/) and [product filtering](https://docs.shopsys.com/en/latest/model/front-end-product-filtering/)
* 3-step ordering process
* Basic CMS
* Support for several currencies, [languages, and domains](https://docs.shopsys.com/en/latest/introduction/domain-multidomain-multilanguage/)
* Full friendly URL for main entities
* Performance optimization through Elasticsearch, Redis, PostgreSQL
* Full core upgradability
* GDPR compliance
* Preparation for scalability
* Base for [Back-end API](https://docs.shopsys.com/en/latest/backend-api/)
* Manifest for orchestration via [Kubernetes](https://docs.shopsys.com/en/latest/kubernetes/introduction-to-kubernetes/)
* Support to easier [deployment to Google Cloud via Terraform](https://docs.shopsys.com/en/latest/kubernetes/how-to-deploy-ssfw-to-google-cloud-platform/)

### Plans for next releases

* GraphQL Front-end API for PWA
* Additional Premium Fetaures
* Enhancements for easier project implementation based on community feedback

## Sites Built on Shopsys Framework
List of typical projects built on previous versions of Shopsys Framework:
* [Prumex](https://www.prumex.cz/)
* [Papírnictví Pavlík](https://www.papirnictvipavlik.cz/)
* [Růžový slon](https://www.ruzovyslon.cz/)
* [AB COM CZECH](https://www.ab-com.cz/)
* [Patro](https://www.patro.cz/)
* [B2B Portal Démos](https://www.demos24plus.com/login/)
* [Agátin svět](https://www.agatinsvet.cz/)
* [Bushman](https://www.bushman.cz/)

## How to Start a New Project
The *shopsys/shopsys* package is a monolithic repository, a single development environment, for management of all parts of Shopsys Framework.
See more information about the monorepo approach in [the Monorepo article](https://docs.shopsys.com/en/latest/introduction/monorepo/).

For the purposes of building a new project use our [shopsys/project-base](https://github.com/shopsys/project-base),
which is fully ready as the base for building your Shopsys Framework project.

For more detailed instructions, follow one of the installation guides:

* [Installation Guide](https://docs.shopsys.com/en/latest/installation/installation-guide/)
* [Deployment to Google Cloud](https://docs.shopsys.com/en/latest/kubernetes/how-to-deploy-ssfw-to-google-cloud-platform/)
* [Installation on production server](https://docs.shopsys.com/en/latest/installation/installation-using-docker-on-production-server/)

## Documentation
For documentation of Shopsys Framework itself, see [Shopsys Framework Knowledge Base](https://docs.shopsys.com/en/latest/).

For the frequently asked questions, see [FAQ and Common Issues](https://docs.shopsys.com/en/latest/introduction/faq-and-common-issues/).

## Contributing
If you have some ideas or you want to help to improve Shopsys Framework, let us know!
We are looking forward to your insights, feedback, and improvements.
Thank you for helping us making Shopsys Framework better.

You can find all the necessary information in our [Contribution Guide](./CONTRIBUTING.md).

## Support
What to do when you are in troubles or need some help?
The best way is to contact us on our [Slack](http://slack.shopsys-framework.com/).

If you are experiencing problems during installation or running Shopsys Framework on Docker,
please see our [Docker troubleshooting](https://docs.shopsys.com/en/latest/docker/docker-troubleshooting/).

Or ultimately, just [report an issue](https://github.com/shopsys/shopsys/issues/new).

## License
We distribute the main parts of Shopsys Framework under two different licenses:

* [Community License](./LICENSE) in MIT style for growing small to mid-size e-commerce sites with total online sales less than 12.000.000 EUR / year (3.000.000 EUR / quarter)
* Commercial License

Learn the principles on which we distribute our product on our website at [Licenses and Pricing section](https://www.shopsys.com/licensing).

Some of the Shopsys Framework repositories including [HTTP smoke testing](https://github.com/shopsys/http-smoke-testing) and [Monorepo Tools](https://github.com/shopsys/monorepo-tools) are distributed under standard MIT license so generally you can use it without any restriction. The information about the license is placed in the LICENSE file in the root of each repository.

Shopsys Framework also uses some third-party components and images which are licensed under their own respective licenses.
The list of these licenses is summarized in [Open Source License Acknowledgements and Third Party Copyrights](./open-source-license-acknowledgements-and-third-party-copyrights.md).
