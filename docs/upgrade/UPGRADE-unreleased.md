# [Upgrade from v7.1.0 to Unreleased]

This guide contains instructions to upgrade from version v7.1.0 to Unreleased.

**Before you start, don't forget to take a look at [general instructions](/UPGRADE.md) about upgrading.**
There you can find links to upgrade notes for other versions too.

## [shopsys/framework]
### Configuration
 - *(low priority)* use standard format for redis prefixes ([#928](https://github.com/shopsys/shopsys/pull/928))
    - change prefixes in `app/config/packages/snc_redis.yml` and `app/config/packages/test/snc_redis.yml`. Please find inspiration in [#928](https://github.com/shopsys/shopsys/pull/928/files)
    - once you finish this change, you still should deal with older redis cache keys that don't use new prefixes. Such keys are not removed even by `clean-redis-old`, please find and remove them manually (via console or UI)

    **Be careful, this upgrade will remove sessions**

### Tools
 - *(low priority)* improve Phing property `is-multidomain` in `build.xml` to detect domains automatically ([#941](https://github.com/shopsys/shopsys/pull/941))
    ```diff
    -    <property name="is-multidomain" value="true" />
    +    <loadfile property="domains" file="${path.app}/config/domains.yml"/>
    +  
    +    <exec executable="${path.php.executable}" outputProperty="domains.count">
    +        <arg value="-r echo substr_count('${domains}','-');"/>
    +    </exec>
    +    <if>
    +        <equals arg1="${domains.count}" arg2="1" trim="true"/>
    +        <then>
    +            <property name="is-multidomain" value="false" />
    +        </then>
    +        <else>
    +            <property name="is-multidomain" value="true" />
    +        </else>
    +    </if>
    ```


[Upgrade from v7.1.0 to Unreleased]: https://github.com/shopsys/shopsys/compare/v7.1.0...HEAD
