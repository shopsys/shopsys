# Shopsys Project-base

**Shopsys Project-base is the foundation you build a new project on top of.** It is the starting point of every project on [Shopsys Platform](https://github.com/shopsys/shopsys) — a **scalable e-commerce platform** for fast-growing online stores, built by in-house teams and agencies. Create your own project from this repository by following the [Installation Guide](https://docs.shopsys.com/en/latest/installation/installation-guide/), and you have a working e-shop right away — backend, administration, and Next.js storefront with a complete Docker development environment — ready for you to develop your store on top of.

This repository is **read-only**: it is generated and maintained from the [shopsys/shopsys](https://github.com/shopsys/shopsys) monorepo, where all development happens. See the monorepo's README for more information and its `CHANGELOG` for the history of changes.

## Installation

Create your own project from this package with:

```sh
composer create-project shopsys/project-base --no-install --keep-vcs --ignore-platform-reqs
```

See the [Installation Guide](https://docs.shopsys.com/en/latest/installation/installation-guide/) for detailed instructions.

We recommend to choose **installation via Docker** because it is the easiest and fastest way to start using Shopsys Platform.
Docker contains complete development environment necessary for running your application.
Whenever we add new technologies to Shopsys Platform,
**updating your development environment to use these technologies will be very easy with Docker**
because such an update will be done just by running `docker compose build`.
And that is all!

## Documentation

For documentation of Shopsys Platform itself see [Shopsys Platform Knowledge Base](https://docs.shopsys.com/en/latest/).

## Contributing

Thank you for your contributions to Shopsys Project-base.
Together we are making Shopsys Platform better.

This repository is READ-ONLY.
If you want to [report issues](https://github.com/shopsys/shopsys/issues/new) and/or send [pull requests](https://github.com/shopsys/shopsys/compare),
please use the main [Shopsys repository](https://github.com/shopsys/shopsys).

Please check our [Contribution Guide](https://github.com/shopsys/shopsys/blob/HEAD/CONTRIBUTING.md) before contributing.

## Support

What to do when you are in troubles or need some help?
The best way is to ask in our [GitHub Discussions](https://github.com/shopsys/shopsys/discussions).

If you want to [report issues](https://github.com/shopsys/shopsys/issues/new), please use the main [Shopsys repository](https://github.com/shopsys/shopsys).
