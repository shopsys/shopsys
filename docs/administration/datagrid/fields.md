# Fields

Fields are the building blocks of a datagrid. They are used to define the columns of the datagrid and to specify how the data should be displayed.

## Configuration

Fields are defined by the `Datagrid` class using the `add(string $name, array $options)`, `update(string $name, array $options)` and `remove(string $name)` methods. The `$name` parameter is the name of the field and the `$options` parameter is an array of options that define the behavior of the field.

In case you are using `OrmAdapter`, you can use dot notation to access nested properties of the entity. Here are some examples of how to define field names:

- `$datagrid->add('domainId')` - This will fetch the `domainId` property of the entity.
- `$datagrid->add('currency.code')` - This will join the `currency` relation and fetch the `code` property of the `Currency` entity.
- `$datagrid->add('status.name')` - This will join the `status` relation and look for the `name` property of the `Status` entity. In this case `Status` is a translatable entity and the `name` property is a translatable field. The datagrid will automatically fetch the correct translation based on the current locale.
- `$datagrid->add('currency')` - This will fetch the `Currency` entity. In this case you need to define `template`, `transform` or `visible` to `false` option to tell datagrid how to display this object.

### Options

The following options are available for fields:

- `label` - The label of the field. It is displayed in the header of the column.
- `visible` - If set to `true`, the field is displayed in the datagrid. If set to `false`, the field is hidden, but fetching the data is still performed.
- `sortable` - If set to `true`, the field can be sorted by clicking on the column header.
- `virtual` - If set to `true`, the field is not fetched from the data source.
- `help` - The help text that is displayed next to label in column header.
- `template` - The template that is used to render the field. The template is a path to a Twig template file.
- `transform` - A callback function that is used to transform the data before it is displayed in the field. The callback function receives the value of the field as the first parameter, row as the second argument and all rows as the third parameter.
- `property` - The property of the entity that is used to fetch the data. If not set, the field name is used as the property name.

```php
$datagrid->add('name', [
    'label' => t('Name'),
    'sortable' => true,
    'help' => t('The name of the product'),
    'template' => '@ShopsysFramework/Admin/Content/Product/datagridField_name.html.twig',
    'transform' => function (string $value, array $row, array $results) {
        return strtoupper($value);
    },
]);
```

## Examples

```php

$datagrid
    
    // You can use dot notation to access nested properties of the entity
    ->add('status.name', [
        'label' => t('Status name'),
    ])
    
    // or you can use the property option
    ->add('status', [
        'label' => t('Status name'),
        'property' => 'status.name',
    ])
    
    // You can use the transform option to modify the value of the field
    ->add('status', [
        'transform' => function (Status $status, array $row, array $results) {
            if ($status->isDeleted()) {
                return t('Deleted');
            }

            return $status->getId();
        },
    ])
    
    // You can also use the transform option to prefetch some data at once and use it
    ->add('isVisible', [
        'property' => 'id',
        'transform' => function (int $value, array $row, array $results) {
            $productIds = array_column($results, 'id');
            
            // Be sure that you are not making multiple queries for the same data
            $productVisibility = $this->productVisibilityFacade->getProductVisibilityIndexedByProductId($productIds);

            return $productVisibility[$value];
        },
    ])
;

```