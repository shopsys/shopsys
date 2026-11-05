<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\State;

use LogicException;
use Shopsys\AdministrationBundle\Component\Datagrid\DatagridConfig;
use Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Collects the state of one datagrid in the current request — dispatched by `DatagridStateFactory` once the datagrid
 * is configured. Every feature narrowing the datagrid (the domain control, the quick search, the filter) listens to
 * it, validates its own declarations, reads its own part of the request and adds its part of the state.
 *
 * The listeners of the features never read each other's parts, so their order does not matter. A feature is changed
 * in a project by replacing its subscriber, not by adding another one — a part of the same class can be added only
 * once.
 */
final class DatagridStateEvent extends Event
{
    /**
     * @var array<class-string, object>
     */
    private array $parts = [];

    /**
     * @var array<string, \Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor>
     */
    private array $additionalFields = [];

    private bool $invalid = false;

    /**
     * @param \Symfony\Component\HttpFoundation\Request|null $request Null when the datagrid is built outside a request (a command, a test) — the declarations are validated even then, but no form is built; a datagrid with a domain control needs a request of a logged-in administrator
     */
    public function __construct(
        public readonly DatagridConfig $config,
        private readonly ?Request $request,
    ) {
    }

    public function isInRequest(): bool
    {
        return $this->request !== null;
    }

    /**
     * The query of a GET form of the datagrid. The forms are submitted from the query by hand — `handleRequest()`
     * ignores a GET form during a POST request (symfony/symfony#12244), which is what happens when a live component
     * on the page re-renders.
     *
     * @return array<string, mixed>|null Null when the request does not carry the form
     */
    public function getQuery(string $formName): ?array
    {
        if ($this->request === null || $this->request->query->has($formName) === false) {
            return null;
        }

        return $this->request->query->all($formName);
    }

    /**
     * @throws \LogicException When a part of the same class was already added
     */
    public function addPart(object $part): void
    {
        if (array_key_exists($part::class, $this->parts)) {
            throw new LogicException(sprintf('The part "%s" of the state of the datagrid "%s" was already added, replace the subscriber adding it instead of adding another one.', $part::class, $this->config->gridId));
        }

        $this->parts[$part::class] = $part;
    }

    /**
     * Displays one more field in the datagrid — it narrows nothing, a field of the same name declared on the datagrid
     * takes precedence, and it is displayed only to an administrator allowed to see it, as any other field.
     *
     * @throws \LogicException When a field of the same name was already added
     */
    public function addField(FieldDescriptor $field): void
    {
        if (array_key_exists($field->getName(), $this->additionalFields)) {
            throw new LogicException(sprintf('The field "%s" was already added to the datagrid "%s".', $field->getName(), $this->config->gridId));
        }

        $this->additionalFields[$field->getName()] = $field;
    }

    /**
     * What the administrator asked for cannot be applied — the page answers 422.
     */
    public function markInvalid(): void
    {
        $this->invalid = true;
    }

    /**
     * @return array<class-string, object>
     */
    public function getParts(): array
    {
        return $this->parts;
    }

    /**
     * @return \Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor[]
     */
    public function getAdditionalFields(): array
    {
        return array_values($this->additionalFields);
    }

    public function isInvalid(): bool
    {
        return $this->invalid;
    }
}
