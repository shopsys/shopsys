# Crud Controller

## Methods

Crud Controller provides several methods that allows you to customize the behavior of the controller.

- `configure()` - Method is used to configure general behavior of controller. Customizable options are available [here](#crud-config).

## Crud Config

The `CrudConfig` class is used to configure the behavior of the Crud Controller. It is used in the `configure` method of the Crud Controller.

### Methods

#### `setTitle(PageType $pageType, string $title)`

This method allows you to set the title for the given page type. The title is displayed in the page header.

#### `setActions(array $actions)`

This method defines actions that will be created for the given entity.

For example, if you call `$config->setActions([PageType::LIST, PageType::EDIT])` the Crud Controller will create routes, buttons, etc. only for list and edit actions.