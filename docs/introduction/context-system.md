# Context System

## Introduction

The Context System is a component that allows you to determine and differentiate between various execution environments in your application. This enables environment-specific behavior and configuration based on the current context.

## What are Contexts?

Contexts represent different execution environments or conditions under which your application code runs. Each context can match specific criteria and can have dependencies on other contexts.

### Built-in Contexts

Shopsys Platform includes several built-in contexts:

- **AdminContext** - Matches administration interface requests (`/admin/*` routes)
- **ConsoleContext** - Matches console/CLI command execution
- **ConsumerContext** - Matches message queue consumer execution (requires ConsoleContext)
- **CronContext** - Matches scheduled cron job execution (requires ConsoleContext)
- **FrontendApiContext** - Matches GraphQL API requests (`/graphql` endpoints)

!!! note

    You can use `php bin/console shopsys:contexts:list` command to list all registered contexts

## Basic Usage

### Checking Current Context

You can check if a specific context matches the current environment using the `ContextResolverInterface`:

```php
use Shopsys\FrameworkBundle\Component\Context\ContextResolverInterface;
use Shopsys\FrameworkBundle\Component\Context\AdminContext;
use Shopsys\FrameworkBundle\Component\Context\ConsoleContext;

class MyService
{
    public function __construct(
        private readonly ContextResolverInterface $contextResolver,
    ) {
    }

    public function doSomething(): void
    {
        if ($this->contextResolver->isCurrentContext(AdminContext::class)) {
            // This code runs only in admin
            $this->doAdminSpecificLogic();
        }

        if ($this->contextResolver->isCurrentContext(ConsoleContext::class)) {
            // This code runs only in console commands
            $this->doConsoleSpecificLogic();
        }
    }
}
```

## Creating Custom Contexts

### 1. Create Context Class

Create a new context by extending `AbstractContext`:

```php
<?php

namespace App\Component\Context;

use Shopsys\FrameworkBundle\Component\Context\AbstractContext;

final class ApiContext extends AbstractContext
{
    public function matches(): bool
    {
        // Your matching logic here
        return $this->request->getPathInfo() === '/api';
    }

    public function getDescription(): string
    {
        return 'REST API requests';
    }
}
```

### 2. Context with Dependencies

You can create contexts that depend on other contexts:

```php
<?php

namespace App\Component\Context;

use Shopsys\FrameworkBundle\Component\Context\AbstractContext;
use Shopsys\FrameworkBundle\Component\Context\AdminContext;

final class AdminApiContext extends AbstractContext
{
    public function getRequiredContexts(): array
    {
        // This context only matches if AdminContext also matches
        return [AdminContext::class];
    }

    public function matches(): bool
    {
        // Your implementation
    }

    public function getDescription(): string
    {
        return 'Matches Admin API requests';
    }
}
```

## Context Dependencies

### How Dependencies Work

Context dependencies ensure that a context only matches when all its required contexts also match. This creates a hierarchical system:

```
ConsoleContext (base CLI context)
├── ConsumerContext (message processing)
└── CronContext (scheduled jobs)
    └── ImportContext (custom import commands)
```

## Best Practices

### 1. Keep Contexts Simple

All contexts should have a single, clear purpose. Avoid complex logic that could change frequently, as this can lead to maintenance issues.
Also matches() method should be performant and not do any heavy operations like database queries or complex calculations as all contexts are checked on every request.

```php
// Good: Simple, focused logic
public function matches(): bool
{
    return str_starts_with($this->request->getPathInfo(), '/admin');
}

// Avoid: Expensive logic that can call DB via Doctrine proxy
public function matches(): bool
{
    $token = $this->tokenStorage->getToken();

    if ($token === null || !$token->isAuthenticated()) {
        return false;
    }

    $user = $token->getUser();

    return $user !== null && $user->isActive() && $user->hasRole('ROLE_ADMIN');
}
```

### 2. Use Dependencies Wisely

Only create dependencies when you truly need hierarchical matching:

```php
// Good: API context that only works in admin
public function getRequiredContexts(): array
{
    return [AdminContext::class];
}

// Avoid: Unnecessary dependencies
public function getRequiredContexts(): array
{
    return [ConsoleContext::class, AdminContext::class]; // ConsoleContext and AdminContext are mutually exclusive
}
```
