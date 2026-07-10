# Shopsys Luigi's Box

[![Downloads](https://img.shields.io/packagist/dt/shopsys/luigis-box.svg)](https://packagist.org/packages/shopsys/luigis-box)

This bundle for [Shopsys Platform](https://www.shopsys.com) adds advanced search using [Luigi's Box](https://luigisbox.com).
The bundle is dedicated for projects based on Shopsys Platform (i.e. created from [`shopsys/project-base`](https://github.com/shopsys/project-base)) exclusively.
This repository is maintained by [shopsys/shopsys] monorepo, information about changes is in its `CHANGELOG` file.

## Documentation

[Documentation](https://docs.shopsys.com/en/latest/integration/luigis-box/) can be found in Shopsys Platform Knowledge Base.

## Installation

The plugin is a Symfony bundle and is installed in the same way:

### Download

First, you download the package using [Composer](https://getcomposer.org/):

```
composer require shopsys/luigis-box
```

## Configuration

Set these environment variables in your project:

```
LUIGIS_BOX_ENABLED_DOMAIN_IDS // comma separated list of domain ids e.g. '1,2'
LUIGIS_BOX_TRACKER_IDS_BY_DOMAIN_IDS='{"1": "<TRACKER_ID_FOR_DOMAIN_1>", "2": "<TRACKER_ID_FOR_DOMAIN_2>", ...}' // JSON object with domain id as key and tracker id as value
```

Add this data loader to your `config/packages/overblog_dataloader.yaml`:

```yaml
luigisBoxBatchLoader:
    alias: 'luigis_box_batch_loader'
    batch_load_fn: "@Shopsys\\LuigisBoxBundle\\Model\\Batch\\LuigisBoxBatchLoader:loadByBatchData"
```

Add a recommendation query to your frontend API (`Query.types.yaml`):

```yaml
- 'RecommendationQueryDecorator'
```

Add a recommendation type to your frontend API types folder (`config/graphql/types/Recommendation`):

```yaml
RecommendationType:
    type: object
    inherits:
        - 'RecommendationTypeDecorator'
```

## Contributing

Thank you for your contributions to Shopsys LuigisBox package.
Together, we are making Shopsys Platform better.

This repository is READ-ONLY.
If you want to [report issues](https://github.com/shopsys/shopsys/issues/new) and/or send [pull requests](https://github.com/shopsys/shopsys/compare),
please use the main [Shopsys repository](https://github.com/shopsys/shopsys).

Please check our [Contribution Guide](https://github.com/shopsys/shopsys/blob/HEAD/CONTRIBUTING.md) before contributing.

## Support

What to do when you are in trouble or need some help?
The best way is to ask in our [GitHub Discussions](https://github.com/shopsys/shopsys/discussions).

If you want to [report issues](https://github.com/shopsys/shopsys/issues/new), please use the main [Shopsys repository](https://github.com/shopsys/shopsys).

[shopsys/shopsys]: (https://github.com/shopsys/shopsys)
