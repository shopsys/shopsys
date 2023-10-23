# Guidelines for Phing Targets

There are a few rules to help us maintain [Phing targets](../introduction/console-commands-for-application-management-phing-targets.md) in Shopsys Platform.

## Naming conventions

Names of targets should be in lowercase, using a dash (`-`) as a word separator.

Property names should also be in lowercase, using a dot (`.`) to separate levels of hierarchy and a dash (`-`) as a word separator.

When naming targets, it's usually best to start with a general subject and then mention the action (typically a verb) so that related commands are next to each other.
For example, a phing target used to generate DB migrations is named `db-migrations-generate`.
It's similar to naming Symfony commands, where you also start with a more general namespace (e.g., `shopsys:migrations:generate`).

When a target enforces some application environment, it should have the environment as a prefix (e.g., `test-db-create` or `prod-warmup`).
Targets without such prefix should use the current environment.

## Dependent phing targets vs. phingcall

There are two ways to combine phing targets together: using `depends="..."` attribute or the `<phingcall .../>` task.

You can set a dependency between targets using the `depends` property like this:

```xml
<target name="my-target" depends="first-target,second-target">
    <!-- implementation -->
</target>
```

This will mean that before the execution of `my-target`, the targets `first-target` and `second-target` will also be executed.

This is also useful when you want to implement a target that runs a sequence of subtargets.
Typically, targets starting with a `build-` prefix are such sequences.

On the other hand, you can use a `<phingcall .../>` task inside your implementation to run a specific target:

```xml
<target name="my-target">
    <!-- implementation -->
    <phingcall target="sub-target"/>
    <!-- implementation -->
</target>
```

This will execute the `sub-target` task during the execution of `my-target`.
All dependencies of `my-target` will also be called, even if they were already called during the execution.
Because of this, `phingcall` is unsuitable for the implementation of sequences, as the dependencies get called many times, making the build run longer.

Still, it can be handy when you need to control the exact time when it is called or you want to run it with some overwritten properties.

## Dependency on DIC

Phing targets that use application dependency injection container (i.e. targets that execute Symfony commands) must be run after `composer install` is run.

This means that inside targets that include a dependency on `composer-dev` or `composer-prod`, these DIC-dependent tasks must not be executed before Composer is run.

## Extensibility

When implementing a phing target inside the `shopsys/framework` package, please remember that the end users may have different needs.

Try to avoid hardcoding values that can be replaced by a property.
Such properties can be overwritten in a project repository without the need for overwriting the whole target.

For example, all paths to executables should be used via `path.*.executable` property.

Sometimes, it can even be helpful to include an argument such as `<arg line="${my-target.flags}"/>` in the `exec` task of your target along with an empty `my-target.flags` property (defined on the root level).
This will allow the end users to provide their own flags, modifying the command's behavior without overwriting the whole target.

Removing [DRY violations](./code-quality-principles.md#dont-repeat-yourself) in the phing target definitions by extracting common tasks and sequences into their own (possibly hidden) targets also help with extensibility.
Instead of having to overwrite a few targets to change the definition, in such a case, users could overwrite just the extracted part.

## Extending in monorepo

Targets in the monorepo should have the same behavior as when called from the project repository, increasing the scope of the whole monorepo.
For example, the `tests-unit` target is overwritten in the monorepo's `build.xml` to execute the original target via `<phingcall target="shopsys-framework.unit-tests"/>` and then execute the unit tests in all packages and utils repositories.

When this is impossible, please overwrite the target and display a message instead, explaining the actual outcome to avoid misunderstandings.

## Deprecations

When you want to rename or delete a phing target you should hide the old target and display a deprecation message instead.
When renaming, call the new target via `<phingcall target="..."/>`.

```xml
<target name="old-target" hidden="true">
    <phingcall target="new-target"/>
    <echo level="warning" message="This phing target is deprecated since Shopsys Platform X.Y.Z, use 'new-target' instead."/>
</target>
```

This will allow the users to get used to the new targets gradually.

## Sorting

All definitions of targets should be alphabetically sorted in the `build.xml`.
It helps developers to maintain them more easily and prevents merge conflicts.
