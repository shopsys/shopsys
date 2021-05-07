# [Upgrade from v9.1.0 to v9.2.0-dev](https://github.com/shopsys/shopsys/compare/v9.1.0...master)

This guide contains instructions to upgrade from version v9.1.0 to v9.2.0-dev.

**Before you start, don't forget to take a look at [general instructions](https://github.com/shopsys/shopsys/blob/7.3/UPGRADE.md) about upgrading.**
There you can find links to upgrade notes for other versions too.

## Application
- use different css classes for javascript and tests ([#2179](https://github.com/shopsys/shopsys/pull/2179))
    - see #project-base-diff to update your project

- class `Shopsys\FrameworkBundle\Component\Csv\CsvReader` is deprecated, use `SplFileObject::fgetcsv()` instead ([#2218](https://github.com/shopsys/shopsys/pull/2218))

- replace html icon tags with new twig tag icon ([#2274](https://github.com/shopsys/shopsys/pull/2274))
    - search for all occurrences of
        ```twig
        <i class="svg svg-<svg-icon-name>"></i>
        ```
        and replace it by
        ```twig
        {{ icon('<svg-icon-name>') }}
        ```
        Full example:
        ```diff
        -   <i class="svg svg-question"></i>
        +   {{ icon('question') }}
        ```
    - for more information read our article [Icon function](https://docs.shopsys.com/en/9.1/frontend/icon-function/)

## Tools
- enable automated check of the alias configuration for extended classes in your project ([#2309](https://github.com/shopsys/shopsys/pull/2309))
    - in your `build.xml` add the following line to include the check of the alias configuration in your `services.yaml`
    ```diff
    + <property name="check-alias-configuration" value="true"/>
      <property name="check-and-fix-annotations" value="true"/>
      <property name="path.root" value="${project.basedir}"/>
      ...
      <import file="${path.framework}/build.xml"/>
    ```
    - run `php phing annotations-alias-check` to check the relevant configuration of your extended classes
    - then run `php phing annotations-fix` to fix the annotations due to improved understanding of the class extension
    - thanks to the fixes, your IDE (PHPStorm) and static analysis (PHPStan) will understand your code better
    - afterwards, running `php phing standards` is highly recommended 
