# Coding Standards

In order to keep the codebase unified, we use automated tools for checking coding standards. These tools are parts of our package - [shopsys/coding-standards](https://github.com/shopsys/coding-standards/).

You can run all the standards checks by calling
```
php phing standards
```
A lot of the violations of the rules can be fixed automatically by calling
```
php phing standards-fix
```

## Standards that are not checked automatically
Besides the rules that are checked by automatic tools, we have few rules for which there are no automatic checks yet:

- CSS classes are named-with-dash. CSS classes for JavaScript purposes are prefixed with "js-" prefix
    ```
    <div class="js-bestselling-products list-products-line">
    ```

- Names in configuration files (eg. [`services.yml`](../../src/Shopsys/ShopBundle/Resources/config/services.yml)) are underscored
    ```
    shopsys.doctrine.cache_driver.query_cache
    ```

- Methods for data retrieving are prefixed with "get". If the method can return `null`, it is prefixed with "find" instead.
    ```php
    /**
     * @param int $id
     * @return \Shopsys\FrameworkBundle\Model\Product\Product|null
     */
    public function findById($id)
    {
        return $this->getProductRepository()->find($id);
    }
    
    /**
     * @param int $id
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function getById($id)
    {
        $product = $this->findById($id);

        if ($product === null) {
            throw new \Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException('Product with ID ' . $id . ' does not exist.');
        }

        return $product;
    }
    ```
- Names of form validation groups must be in camelCase and declared as constants (except of Symfony `Default` validation group)
    ```php
    const VALIDATION_GROUP_COMPANY_CUSTOMER = 'companyCustomer';
    ```
- Too long XML and HTML tags are divided by the following pattern:
    ```xml
    <tag
       attr1="foo"
       attr2="bar"
     />
    ```
- Too long conditions are divided by the following pattern:
    ```php
    if ($orderFormData->getCompanyName() !== null
       || $orderFormData->getCompanyNumber() !== null
       || $orderFormData->getCompanyTaxNumber() !== null
     ) {
    ```
- Database table and column names are underscored. Names must not be PostgreSQL keywords. In order to prevent conflicts, names of the tables are in plural.
- Everything possible is ordered alphabetically (`.gitignore` content, configuration files, imports, ...)
- Annotations are not mandatory for constructors of data objects and constructors using autowiring
- In annotations, we use fully-qualified class names for type names (including leading slash)
    ```php
    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\Category
     */
    public function getRootCategory()
    {
        // ...
        return $rootCategory;
    }
    ```
- When annotating an array, it is mandatory to state the type of array's items (including scalar types)
    ```php
    /**
     * @param int[] $ids
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function getAllByIds($ids)
    {
        return $this->getProductRepository()->findBy(['id' => $ids]);
    }
    ```
