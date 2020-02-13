# [Upgrade from v7.2.0 to v7.2.1](https://github.com/shopsys/shopsys/compare/v7.2.0...v7.2.1)

This guide contains instructions to upgrade from version v7.2.0 to v7.2.1.

**Before you start, don't forget to take a look at [general instructions](https://github.com/shopsys/shopsys/blob/7.3/UPGRADE.md) about upgrading.**
There you can find links to upgrade notes for other versions too.

## [shopsys/framework]

### Application
- call `Form::isSubmitted()` before `Form::isValid()` ([#1041](https://github.com/shopsys/shopsys/pull/1041))
    - search for `$form->isValid() && $form->isSubmitted()` and fix the order of calls (in `shopsys/project-base` the wrong order could have been found in `src/Shopsys/ShopBundle/Controller/Front/PersonalDataController.php`):
        ```diff
        - if ($form->isValid() && $form->isSubmitted()) {
        + if ($form->isSubmitted() && $form->isValid()) {
        ```
- fix the typo in Twig template `@ShopsysShop/Front/Content/Category/panel.html.twig` ([#1043](https://github.com/shopsys/shopsys/pull/1043))
    - `categoriyWithLazyLoadedVisibleChildren` ⟶ `categoryWithLazyLoadedVisibleChildren`
- create an empty file `app/Resources/.gitkeep` to prepare a folder for [your overwritten templates](https://docs.shopsys.com/en/latest/cookbook/modifying-a-template-in-administration/) ([#1073](https://github.com/shopsys/shopsys/pull/1073))
- fix `FilterQueryTest` to use ElasticSearch index prefix properly via `ElasticsearchStructureManager` ([#1082](https://github.com/shopsys/shopsys/pull/1082))
    ```diff
    - private const ELASTICSEARCH_INDEX = 'product1';
    + private const ELASTICSEARCH_INDEX = 'product';
    ```
    ```diff
    - $filter = $filterQueryFactory->create(self::ELASTICSEARCH_INDEX);
    + /** @var \Shopsys\FrameworkBundle\Component\Elasticsearch\ElasticsearchStructureManager $elasticSearchStructureManager */
    + $elasticSearchStructureManager = $this->getContainer()->get(ElasticsearchStructureManager::class);
    +
    + $elasticSearchIndexName = $elasticSearchStructureManager->getIndexName(1, self::ELASTICSEARCH_INDEX);
    +
    + $filter = $filterQueryFactory->create($elasticSearchIndexName);
    ```

### Infrastructure
- replace url part in `infrastructure/google-cloud/nginx-ingress.tf` to use released version of this nginx-ingress configuration ([#1077](https://github.com/shopsys/shopsys/pull/1077))
    ```diff
    - command     = "kubectl apply -f https://raw.githubusercontent.com/kubernetes/ingress-nginx/master/deploy/mandatory.yaml"
    + command     = "kubectl apply -f https://raw.githubusercontent.com/kubernetes/ingress-nginx/nginx-0.24.1/deploy/mandatory.yaml"
- add configuration into `kubernetes/deployments/webserver-php-fpm.yml` to run initialization process of php-fpm container as user www-data(33) ([#1078](https://github.com/shopsys/shopsys/pull/1078))
    ```diff
      -   name: copy-source-codes-to-volume
          image: ~
    +     securityContext:
    +         runAsUser: 33
          command: ["sh", "-c", "cp -r /var/www/html/. /tmp/source-codes"]
    ```
    ```diff
      -   name: initialize-database
          image: ~
    +     securityContext:
    +         runAsUser: 33
          command: ["sh", "-c", "cd /var/www/html && ./phing db-create dirs-create db-demo product-search-recreate-structure product-search-export-products grunt error-pages-generate warmup"]
    ```

[shopsys/framework]: https://github.com/shopsys/framework
