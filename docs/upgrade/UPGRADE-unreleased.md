# [Upgrade from v7.2.0 to Unreleased](https://github.com/shopsys/shopsys/compare/v7.2.0...7.2)

This guide contains instructions to upgrade from version v7.2.0 to Unreleased.

**Before you start, don't forget to take a look at [general instructions](/UPGRADE.md) about upgrading.**
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
- create an empty file `app/Resources/.gitkeep` to prepare a folder for [your overwritten templates](/docs/cookbook/modifying-a-template-in-administration.md) ([#1073](https://github.com/shopsys/shopsys/pull/1073))
- fix `FilterQueryTest` to use ElasticSearch index prefix properly ([#1082](https://github.com/shopsys/shopsys/pull/1082))
    ```diff
    - 16:   private const ELASTICSEARCH_INDEX = 'product1';
    + 16:   private const ELASTICSEARCH_INDEX = 'product';
    ...
             /** @var \Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory $filterQueryFactory */
             $filterQueryFactory = $this->getContainer()->get(FilterQueryFactory::class);
    - 169:       $filter = $filterQueryFactory->create(self::ELASTICSEARCH_INDEX);
    + 169:
    + 170:       /** @var \Shopsys\FrameworkBundle\Component\Elasticsearch\ElasticsearchStructureManager $elasticSearchStructureManager */
    + 171:       $elasticSearchStructureManager = $elasticSearchIndexName = $this->getContainer()->get(ElasticsearchStructureManager::class);
    + 172:
    + 173:       $elasticSearchIndexName = $elasticSearchStructureManager->getIndexName(1, self::ELASTICSEARCH_INDEX);
    + 174:
    + 175:       $filter = $filterQueryFactory->create($elasticSearchIndexName);
    ```

### Infrastructure
- replace url part in `infrastructure/google-cloud/nginx-ingress.tf` to use released version of this nginx-ingress configuration ([#1077](https://github.com/shopsys/shopsys/pull/1043))
    ```diff
    - command     = "kubectl apply -f https://raw.githubusercontent.com/kubernetes/ingress-nginx/master/deploy/mandatory.yaml"
    + command     = "kubectl apply -f https://raw.githubusercontent.com/kubernetes/ingress-nginx/nginx-0.24.1/deploy/mandatory.yaml"
    ```

[shopsys/framework]: https://github.com/shopsys/framework
