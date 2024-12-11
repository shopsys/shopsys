# Domain limiting

[TOC]

## Introduction

In Shopsys Platform it's possible to limit the domain of the eshop from the two points of view:

1. Limit the demo data during the demo data installation

    This is useful to speed up the build process in the development environment.
    The demo data installation can be limited to the specific domains, so only the data interesting to the developer are installed.

2. Limit the visible domains in administration

    Administrator may select only a subset of all available domains to be visible in the administration.
    This is useful when the administrator is responsible only for a subset of domains or wants to focus on a subset of them.

## Limit the demo data during the demo data installation

If the demo data for the specific domain should be installed during the development build, it is determined by the `load_demo_data` parameter in the `domains.yaml` file.
When the parameter is set to `true` (default value), the demo data for the domain is installed.

!!! warning

    The first and second domains are used in tests and should always be installed.

!!! note

    The `load_demo_data` parameter is used only during the demo data installation.
    It doesn't affect the domain visibility in the administration.

### Respecting the domain limiting in data fixtures

In order to respect the domain limiting in the data fixtures, the `DomainsForDataFixtureProvider` service should be used to iterate over the domains.

The following code snippet shows how to iterate over the domains and create a product for each domain:

```php
$blogArticleData = $this->blogArticleDataFactory->create();

$blogArticleData->publishDate = new DateTime('2024-01-01');

foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataLocales() as $locale) {
    $blogArticleData->names[$locale] = t('Blog article example', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
}

foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomains() as $domainConfig) {
    $locale = $domainConfig->getLocale();
    $domainId = $domainConfig->getId();

    $blogArticleData->seoTitles[$domainId] = t('SEO Title', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
    $blogArticleData->seoMetaDescriptions[$domainId] = t('SEO Meta description', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
}
```

Notice using the `domainsForDataFixtureProvider` service to get the allowed domains.
This service should not be used anywhere else than in the data fixtures!

!!! note

    In some data fixtures, creating data for all domains may be necessary, not only for the allowed ones.

## Limit the visible domains in administration

Administrator may select only a subset of all available domains to be visible in the administration.
This limitation is applied only to the current administrator (e.g., each administrator may have selected a different set of domains).

This limitation only serves a purpose of focusing on a subset of domains, to reduce the visual distractions and doesn't affect the functionality of the eshop.

When the administrator selects only some domains, this is applied to the [multidomain and multilanguage](../introduction/domain-multidomain-multilanguage.md) form fields,
the domain filter/select tabs, and the data displayed on the lists.

### How to write domain-limiting compatible code

A few ground rules should be followed to make the domain limiting work correctly.

Use [`MultidomainType`](../introduction/using-form-types.md#multidomaintype) and [`LocalizedType`](../introduction/using-form-types.md#localizedtype) form types to render form fields for each domain/locale.

It's possible to create `entry_options` only for the selected domains, leveraging the `Domain::getAdminEnabledDomains()` method.

```php
foreach ($this->domain->getAdminEnabledDomains() as $domainConfig) {
    $domainId = $domainConfig->getId();

    $seoTitlesOptionsByDomainId[$domainId] = [
        'attr' => [
            'placeholder' => $this->getCategoryNameForPlaceholder($domainConfig, $options['category']),
            'class' => 'js-dynamic-placeholder',
            'data-placeholder-source-input-id' => 'category_form_name_' . $domainConfig->getLocale(),
        ],
    ];
    $seoMetaDescriptionsOptionsByDomainId[$domainId] = [
        'attr' => [
            'placeholder' => $this->seoSettingFacade->getDescriptionMainPage($domainId),
        ],
    ];
}
```

If you have the strictly domain-separated entity (e.g., orders, complaints - any entity that belongs to a single domain), use the `Domain::getAdminEnabledDomainIds()` method to prepare the datasource.

```php
$selectedDomainId = $this->adminDomainFilterTabsFacade->getSelectedDomainId($domainFilterNamespace);

if ($selectedDomainId !== null) {
    $queryBuilder
        ->andWhere('o.domainId = :selectedDomainId')
        ->setParameter('selectedDomainId', $selectedDomainId);
} else {
    $queryBuilder
        ->andWhere('o.domainId IN (:domainIds)')
        ->setParameter('domainIds', $this->domain->getAdminEnabledDomainIds());
}

$dataSource = new QueryBuilderDataSource($queryBuilder, 'o.id');
```

If you have an entity that may be enabled for multiple domains (e.g., products, blog articles), do not limit the datasource to the selected domains.
Otherwise, it will be confusing for the administrator to see only a subset of the entities.

When the entity is edited in the administration, even though fields for only some domains are visible, the data is fetched for all domains.
This reduces the complexity of the code and prevents the risk of data loss.

It's possible that some entities cannot be saved without all the data filled in – e.g., there is a not-null constraint on the database level.
In that case, it's possible to instruct the administrator to fill in the data for all domains.

```php
if ($form->isSubmitted() && $form->isValid()) {
    if (!$this->domain->hasAdminAllDomainsEnabled()) {
        $this->addErrorFlash(t('Creating a record requires all domains to be enabled as domain-specific fields cannot be empty. If you want to proceed, select all domains in the Domain filter in the header first.'));

        return $this->redirectToRoute('admin_country_new');
    }

    // ...
```

Mind the fact that the administrator may not see all the fields for all domains, but the data is saved for all domains.
This is especially important when setting the default values for the fields.

For example, if you create a new entity that may be enabled for multiple domains, and you set the default value of the enabled field to `true`.
Then, the administrator with limited domains will not see all the domains, but the entity will be saved as enabled for all domains!
