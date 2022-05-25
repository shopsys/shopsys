# [Upgrade from v10.0.0 to v11.0.0-dev](https://github.com/shopsys/shopsys/compare/10.0.0...master)

This guide contains instructions to upgrade from version 10.0.0 to 11.0.0-dev.

**Before you start, don't forget to take a look at [general instructions](https://github.com/shopsys/shopsys/blob/master/UPGRADE.md) about upgrading.**
There you can find links to upgrade notes for other versions too.

## Application

- fix implementations of FileVisitorInterface::visitTwigFile ([#2465](https://github.com/shopsys/shopsys/pull/2465))
    - in the following classes, an interface of `visitTwigFile` was fixed to comply with `FileVisitorInterface`
        - `ConstraintMessageExtractor`
        - `ConstraintMessagePropertyExtractor`
        - `ConstraintViolationExtractor`
        - `JsFileExtractor`
        - `PhpFileExtractor`
        - `TwigFileExtractor`
