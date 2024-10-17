# Shopsys Convertim Bundle

[![Downloads](https://img.shields.io/packagist/dt/shopsys/convertim.svg)](https://packagist.org/packages/shopsys/convertim)

This bundle for [Shopsys Platform](https://www.shopsys.com) integrates the Convertim one-click checkout for ecommerce.
The bundle is dedicated for projects based on Shopsys Platform (i.e., created from [`shopsys/project-base`](https://github.com/shopsys/project-base)) exclusively.
This repository is maintained by [shopsys/shopsys](https://github.com/shopsys/shopsys) monorepo.

## Documentation

[Documentation](https://docs.shopsys.com/en/latest/) can be found in Shopsys Platform Knowledge Base.

The documentation of Convertim can be found on the [https://docs.convertim.com](https://docs.convertim.com).

## Installation

The plugin is a Symfony bundle and is installed in the same way:

### Download

First, you download the package using [Composer](https://getcomposer.org/):

```
composer require shopsys/convertim
```

### Set environment variable

For setting Convertim configuration, you need to set the `CONVERTIM_CONFIG` environment variable with provided configuration from Convertim for each domain separately like in the example:

```
CONVERTIM_CONFIG='{
    "1": {
        "enabled": true,
        "authorizationHeader": "HEADER"
    },
    "2": {
        "enabled": false,
        "authorizationHeader": "HEADER"
    }
}'
```

## Contributing

Thank you for your contributions to Shopsys Convertim Bundle.
Together, we are making Shopsys Platform better.

This repository is READ-ONLY.
If you want to [report issues](https://github.com/shopsys/shopsys/issues/new) and/or send [pull requests](https://github.com/shopsys/shopsys/compare),
please use the main [Shopsys repository](https://github.com/shopsys/shopsys).

Please, check our [Contribution Guide](https://github.com/shopsys/shopsys/blob/HEAD/CONTRIBUTING.md) before contributing.

## Support

What to do when you are in trouble or need some help?
The best way is to join our [Slack](https://join.slack.com/t/shopsysframework/shared_invite/zt-11wx9au4g-e5pXei73UJydHRQ7nVApAQ).

If you want to [report issues](https://github.com/shopsys/shopsys/issues/new), please use the main [Shopsys repository](https://github.com/shopsys/shopsys).
