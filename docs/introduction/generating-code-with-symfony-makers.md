# Generating code with Symfony makers

We have embraced the [powerful tool provided by Symfony](https://symfony.com/bundles/SymfonyMakerBundle/current/index.html) and implemented our set of makers to speedup implementation of common tasks.

Using these simple console commands, you can avoid writing a boilerplate code, and easily generate new entities, their data objects, facades, repositories, data fixtures, etc.

When implementing a new entity agenda, you can use

```
php bin/console make:shopsys:entity-agenda
```

To list all the available maker commands, use

```
php bin/console make:shopsys
```
