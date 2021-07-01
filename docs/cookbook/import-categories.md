# Import Categories

Category import is one of the specific imports.
Due to the tree structure it's really easy to create ineffective script.
In this cookbook we highlight some concepts and how to improve category import performance.

## Task

> _As an eshop owner I want to import categories from ERP system, so I don't need to edit categories in two places._

Let's presume we already have working information system, and we are able to obtain data we need.
For the future references we can imagine it as a method with following description

```php
public function getCategoryDataFromErp(): iterable {
    // exact implementation is not important
    // returns an iterable which each element looks like
    //   [
    //       'uuid' => '1bb2dd5d-92e0-4263-8ca3-d63aee840c62',
    //       'parent_uuid' => '3af3c39a-d4b0-4a59-9e36-2e5d54e7ca21',
    //       'name_en' => 'Electronics',
    //       'name_cs' => 'Elektronika',
    //       'description_1' => 'Description of Electronics',
    //       'description_2' => 'Description of Electronics in Czech',
    //   ];
}
```

Method returns data sorted exactly as they should be on the eshop, meaning category with the same parent will be first, if it comes first from the method.  
And to make matters worse, ERP can only return all 10000 categories.

## First import

In this cookbook we first write the code that's obvious, but not ideal and as we progress, we improve the import to demonstrate the concepts and caveats of importing categories. 

!!! note
    If you're not familiar with the concepts of creating data imports, take a look at the [Basic data import cookbook](./basic-data-import.md)

The main working loop is straightforward

```php
public function run(): void
{
    foreach ($this->getCategoryDataFromErp() as $importData) {
        $this->importCategory($importData);
    }
}

/**
 * @param array{uuid: string, parent_uuid: string|null, name_en: string, name_cs: string, description_1: string, description_2: string} $importData
 */
private function importCategory(array $importData): void
{
    $categoryUuid = $importData['uuid'];

    try {
        $category = $this->categoryFacade->getByUuid($categoryUuid);

        $categoryData = $this->categoryDataFactory->createFromCategory($category);

        $categoryData = $this->mapImportDataToCategoryData($importData, $categoryData);

        $category = $this->categoryFacade->edit($category->getId(), $categoryData);
    } catch (CategoryNotFoundException $exception) {
        $categoryData = $this->categoryDataFactory->create();

        $categoryData = $this->mapImportDataToCategoryData($importData, $categoryData);

        $category = $this->categoryFacade->create($categoryData);
    }
}

/**
 * @param array{uuid: string, parent_uuid: string|null, name_en: string, name_cs: string, description_1: string, description_2: string} $importData
 * @param \Shopsys\FrameworkBundle\Model\Category\CategoryData $categoryData
 * @return \Shopsys\FrameworkBundle\Model\Category\CategoryData
 */
private function mapImportDataToCategoryData(array $importData, CategoryData $categoryData): CategoryData
{
    foreach ($this->domain->getAll() as $domainConfig) {
        $locale = $domainConfig->getLocale();
        $domainId = $domainConfig->getId();

        $categoryData->name[$locale] = $importData['name_' . $locale];
        $categoryData->descriptions[$domainId] = $importData['description_' . $domainId];
    }

    $categoryData->uuid = $importData['uuid'];
    $categoryData->parent = $importData['parent_uuid'] ? $this->categoryFacade->getByUuid($importData['parent_uuid']) : null;

    return $categoryData;
}
```

As you can see, we iterate over all categories obtained from the ERP and use UUID field to connect categories from the ERP with categories on the shop.
(In your application it can be some kind of new field, for example `external_id`)

If a category is found on the shop, data are edited, otherwise the category is created.

!!! note
    This is merely a trivial example.  
    In a real project, you don't want to load all categories to memory, but rather load them in batch or consider a different approach, for example, creating some type of queue to process data from.

So far so good, so let's run this import.

It's more than likely that the import will take much longer than we would like.

> _Import finished in 15 minutes and 30 seconds_

## Fix sorting

Our import works, but not exactly the way we want.
Categories should be sorted by position in the source data, but right now, when the position of the categories on the same level changes, this change is not propagated.
It's actually logical.
Position is not set anywhere, and the relation to the parent do not hold any positional information.

Fix is not that hard.
All we need to do is to start sorting categories while importing.

```diff
 private function importCategory(array $importData): void
 {
     // ...

+    $this->categoryRepository->persistAsLastChild($category);
+    $this->em->flush();
 }
```

> _Import finished in 20 minutes and 10 seconds_

Apart the increased import time, we also had to add two new dependencies, `CategoryRepository` and `EntityManager`.

## Better way

One of the reasons, why the import takes so long, is the category tree structure.
After each flush, the nested set is recalculated for the tree and this takes some time.

But we can edit data first, and sort categories later.
This should provide a noticeable performance improvement.

At first, we remove setting parent_id in the mapping method, we will do it differently.

```diff
 private function mapImportDataToCategoryData(array $importData, CategoryData $categoryData): CategoryData
 {
     foreach ($this->domain->getAll() as $domainConfig) {
         $locale = $domainConfig->getLocale();
         $domainId = $domainConfig->getId();

         $categoryData->name[$locale] = $importData['name_' . $locale];
         $categoryData->descriptions[$domainId] = $importData['description_' . $domainId];
     }

     $categoryData->uuid = $importData['uuid'];
-    $categoryData->parent = $importData['parent_uuid'] ? $this->categoryFacade->getByUuid($importData['parent_uuid']) : null;

     return $categoryData;
 }
```

Also, we don't need the code for sorting siblings, but we need to build the array representing the sorted adjacency list (relation to the parent).

```diff
 private array $parentIdByCategoryId;

 private function importCategory(array $importData): void
 {
     // ...

-    $this->categoryRepository->persistAsLastChild($category);
-    $this->em->flush();
+    $parentCategoryIdOrNull = $importData['parent_uuid'] ? $this->categoryFacade->getByUuid($importData['parent_uuid'])->getId(): null;
+    $this->parentIdByCategoryId[$category->getId()] = $parentCategoryIdOrNull;
 }
```

As we don't change the parent and we don't sort the siblings, it's no reason to recalculate the tree and the import is much faster.

> _Import finished in 7 minutes and 45 seconds_

## Bring back the sorting

Now we have all categories created or edited, and we have built the sorted array of ids with related parent ids.

```diff
+ use Shopsys\FrameworkBundle\Model\Category\CategoryNestedSetCalculator;

 public function run(): void
 {
     foreach ($this->getCategoryDataFromIs() as $importData) {
         $this->importCategory($importData);
     }
     
+    $categoriesSortingData = CategoryNestedSetCalculator::calculateNestedSetFromAdjacencyList($this->parentIdByCategoryId);
+    $this->categoryFacade->reorderByNestedSetValues($categoriesSortingData);
 }
```

> _Import finished in 7 minutes and 50 seconds_

!!! note
    Sometimes it's better to create a specific DQL query to update the field you need instead of the whole load-edit routine
