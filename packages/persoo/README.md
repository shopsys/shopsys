# Shopsys Persoo

[![Downloads](https://img.shields.io/packagist/dt/shopsys/persoo.svg)](https://packagist.org/packages/shopsys/persoo)

This bundle for [Shopsys Platform](https://www.shopsys.com) adds advanced search using [Persoo](https://www.persoo.ai).
The bundle is dedicated for projects based on Shopsys Platform (i.e. created from [`shopsys/project-base`](https://github.com/shopsys/project-base)) exclusively.
This repository is maintained by [shopsys/shopsys] monorepo, information about changes is in [monorepo CHANGELOG.md](https://github.com/shopsys/shopsys/blob/master/CHANGELOG.md).

## Documentation

[Documentation](https://docs.shopsys.com/en/latest/) can be found in Shopsys Platform Knowledge Base.

## Installation

The plugin is a Symfony bundle and is installed in the same way:

### Download

First, you download the package using [Composer](https://getcomposer.org/):

```
composer require shopsys/persoo
```

## Configuration

Set these environment variables in your project:

```
PERSOO_IS_PRODUCTION_MODE // 'true' or 'false'
PERSOO_ENABLED_DOMAIN_IDS // comma separated list of domain ids e.g. '1,2'
PERSOO_ACCOUNT_ID // value provided by Persoo company
PERSOO_RECOMMENDATION_ALGORITHM_ID // value provided by Persoo company
PERSOO_RECOMMENDATION_OFFER_ID // value provided by Persoo company
PERSOO_RECOMMENDATION_LOCATION_ID // value provided by Persoo company
PERSOO_SEARCH_ALGORITHM_ID // value provided by Persoo company
PERSOO_SEARCH_OFFER_ID // value provided by Persoo company
PERSOO_SEARCH_LOCATION_ID // value provided by Persoo company
```

## Contributing

Thank you for your contributions to Shopsys Persoo package.
Together, we are making Shopsys Platform better.

This repository is READ-ONLY.
If you want to [report issues](https://github.com/shopsys/shopsys/issues/new) and/or send [pull requests](https://github.com/shopsys/shopsys/compare),
please use the main [Shopsys repository](https://github.com/shopsys/shopsys).

Please check our [Contribution Guide](https://github.com/shopsys/shopsys/blob/master/CONTRIBUTING.md) before contributing.

## Support

What to do when you are in troubles or need some help?
The best way is to join our [Slack](https://join.slack.com/t/shopsysframework/shared_invite/zt-11wx9au4g-e5pXei73UJydHRQ7nVApAQ).

If you want to [report issues](https://github.com/shopsys/shopsys/issues/new), please use the main [Shopsys repository](https://github.com/shopsys/shopsys).

[shopsys/shopsys]: (https://github.com/shopsys/shopsys)
