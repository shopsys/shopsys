# Coding Standards

In order to keep the codebase unified, we use automated tools for checking coding standards. These tools are parts of our package -
[shopsys/coding-standards](https://github.com/shopsys/coding-standards/).

You can run all the standards checks by calling
``` sh
php phing standards
```

!!! hint
    In this step you were using Phing target `standards`.  
    More information about what Phing targets are and how they work can be found in [Console Commands for Application Management (Phing Targets)](../introduction/console-commands-for-application-management-phing-targets.md)

A lot of the violations of the rules can be fixed automatically by calling
``` sh
php phing standards-fix
```

## Standards that are not checked automatically
Besides the rules that are checked by automatic tools, we have few rules for which there are no automatic checks yet:

- CSS classes are named-with-dash. CSS classes for JavaScript purposes are prefixed with "js-" prefix
    ```
    <div class="js-bestselling-products list-products-line">
    ```

- Names in configuration files (eg. [`parameters.yml.dist`](https://github.com/shopsys/project-base/blob/master/config/parameters.yml.dist)) are underscored
    ```
    shopsys.doctrine.cache_driver.query_cache
    ```

- Methods for data retrieving are prefixed with "get". If the method can return `null`, it is prefixed with "find" instead.
    <!-- language: lang-php -->

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

- Names of form validation groups must be in camelCase and declared as constants (except of Symfony `Default` validation group)
    <!-- language: lang-php -->

        const VALIDATION_GROUP_COMPANY_CUSTOMER = 'companyCustomer';

- Too long XML and HTML tags are divided by the following pattern:
    <!-- language: lang-xml -->

        <tag
           attr1="foo"
           attr2="bar"
         />

- Too long conditions are divided by the following pattern:
    <!-- language: lang-php -->

        if ($orderFormData->getCompanyName() !== null
           || $orderFormData->getCompanyNumber() !== null
           || $orderFormData->getCompanyTaxNumber() !== null
         ) {

- Database table and column names are underscored. Names must not be PostgreSQL keywords. In order to prevent conflicts, names of the tables are in plural.
- Everything possible is ordered alphabetically (`.gitignore` content, configuration files, imports, ...)
- When annotating an array, it is mandatory to state the type of array's items (including scalar types)
    <!-- language: lang-php -->

        /**
         * @param int[] $ids
         * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
         */
        public function getAllByIds($ids)
        {
            return $this->getProductRepository()->findBy(['id' => $ids]);
        }

- All monetary values (*prices, account balances, discount amounts, price limits etc.*) must be represented and manipulated by [the `Money` class](../model/how-to-work-with-money.md).
