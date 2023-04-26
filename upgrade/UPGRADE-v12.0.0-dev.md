# [Upgrade from v11.1.0 to v12.0.0-dev](https://github.com/shopsys/shopsys/compare/v11.1...12.0)

This guide contains instructions to upgrade from version v11.1.0 to v12.0.0-dev.

**Before you start, don't forget to take a look at [general instructions](https://github.com/shopsys/shopsys/blob/12.0/UPGRADE.md) about upgrading.**
There you can find links to upgrade notes for other versions too.

- encapsulation of AdditionalImageData ([#1934](https://github.com/shopsys/shopsys/pull/1934))
    - `Shopsys\FrameworkBundle\Component\Image\AdditionalImageData`
        - type of property `$media` changed from having no type to `string`
        - type of property `$url` changed from having no type to `string`
