# CRUD Controllers

The CRUD Controller is a powerful tool that allows you to easily design a UI for creating, reading, updating, and deleting records in the database. It is a part of the [Administration](../index.md) module.

It's designed to speed up the development of the administration interface and provide a consistent user experience. At the same time, it's flexible enough to allow for customizations.

CRUD controller is built using known Shopsys components like [Grid](../internal-grid/index.md) and Symfony components.

## Getting started
- [Creating a new CRUD Controller](getting-started/creating-a-new-crud-controller.md)
- [Configure List page (Datagrid)](getting-started/configure-list-page.md)
- [Adding Create, Edit, and Delete Actions](getting-started/adding-create-edit-and-delete-actions.md)
- [Extending existing CRUD Controller](getting-started/extending-existing-crud-controller.md)

## Reference
- [Configuration](reference/crud-controller.md)
- [Handlers](reference/handlers.md)
- [Actions](reference/actions.md)

## Datagrid

The list page of a CRUD controller is a [Datagrid](../datagrid/index.md). Its own documentation covers what `configureDatagrid()` can declare:

- [Fields](../datagrid/fields.md) — the columns, including the `searchable` option of the quick search
- [Row actions](../datagrid/row-actions.md)
- [Filters](../datagrid/filters.md) — the rules the administrator composes, the built-in filter types and how to write your own
- [Narrowing the records](../datagrid/narrowing.md) — the conditions behind the domain control, the search and the filters, and `addCondition()` for a fixed one
