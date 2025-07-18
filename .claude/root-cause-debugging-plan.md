---
description: "Root Cause Debugging Plan - Domain-Specific Data Missing"
date: "2025-07-18"
status: "Ready for Implementation"
---

# Root Cause Debugging Plan: Domain-Specific Data Missing

## Identified Root Cause Hypothesis
**The issue is missing domain-specific data** in the GitHub preview branches, causing `PromotedCategoriesQuery` and `SliderItemsQuery` to return empty results.

## Verification Strategy

### Phase 1: Database Data Verification
Execute these queries in the PostgreSQL database to confirm the hypothesis:

#### Check Domain Configuration
```sql
-- Check what domains exist
SELECT id, name, url, locale FROM domains;

-- Check current domain ID being used
SELECT id, name, url FROM domains WHERE url LIKE '%jm-after-build-bug-fix-ssp-3495%';
```

#### Check TopCategory Data (PromotedCategoriesQuery)
```sql
-- Check if TopCategory records exist for the domain
SELECT tc.id, tc.domain_id, tc.position, c.id as category_id, ct.name 
FROM categories_top tc 
JOIN categories c ON tc.category_id = c.id 
JOIN category_translations ct ON c.id = ct.translatable_id 
WHERE tc.domain_id = [DOMAIN_ID];

-- Check total TopCategory count per domain
SELECT domain_id, COUNT(*) as count FROM categories_top GROUP BY domain_id;
```

#### Check SliderItem Data (SliderItemsQuery)
```sql
-- Check if SliderItem records exist for the domain
SELECT id, domain_id, name, hidden, position, 
       datetime_visible_from, datetime_visible_to
FROM slider_items 
WHERE domain_id = [DOMAIN_ID];

-- Check total SliderItem count per domain
SELECT domain_id, COUNT(*) as count FROM slider_items GROUP BY domain_id;
```

### Phase 2: PHP Debug Logging Implementation
Add comprehensive logging to the resolvers to capture domain and query information:

#### PromotedCategoriesQuery Logging
**File**: `project-base/app/src/FrontendApi/Resolver/Category/PromotedCategory/PromotedCategoryRepository.php`

```php
public function getVisiblePromotedCategoriesOnDomain(DomainConfig $domainConfig): array
{
    error_log("🔍 [PromotedCategories] Domain: {$domainConfig->getName()} (ID: {$domainConfig->getId()})");
    error_log("🔍 [PromotedCategories] Locale: {$domainConfig->getLocale()}");
    
    $queryBuilder = $this->categoryRepository->getAllVisibleByDomainIdQueryBuilder($domainConfig->getId());
    
    $result = $queryBuilder
        ->addSelect('ct, cd')
        ->join(TopCategory::class, 'tc', Join::WITH, 'tc.category = c AND tc.domainId = :domainId')
        ->join('c.translations', 'ct', Join::WITH, 'ct.locale = :locale')
        ->setParameter('locale', $domainConfig->getLocale())
        ->orderBy('tc.position')
        ->getQuery()->getResult();
    
    error_log("🔍 [PromotedCategories] Query result count: " . count($result));
    
    return $result;
}
```

#### SliderItemsQuery Logging
**File**: `packages/framework/src/Model/Slider/SliderItemRepository.php`

```php
public function getAllVisibleByDomainId(int $domainId): array
{
    error_log("🔍 [SliderItems] Domain ID: {$domainId}");
    
    $dateToday = new DateTime();
    $dateToday = $dateToday->format('Y-m-d 00:00:00');
    
    error_log("🔍 [SliderItems] Date filter: {$dateToday}");
    
    $queryBuilder = $this->getSliderItemQueryBuilder()
        ->where('si.domainId = :domainId')
        ->andWhere('si.hidden = :hidden')
        ->andWhere('si.datetimeVisibleFrom is NULL or si.datetimeVisibleFrom <= :now')
        ->andWhere('si.datetimeVisibleTo is NULL or si.datetimeVisibleTo >= :now')
        ->orderBy('si.position')
        ->addOrderBy('si.id');

    $queryBuilder->setParameters([
        'domainId' => $domainId,
        'hidden' => false,
        'now' => $dateToday,
    ]);

    $result = $queryBuilder->getQuery()->execute();
    error_log("🔍 [SliderItems] Query result count: " . count($result));
    
    return $result;
}
```

### Phase 3: Container Log Analysis
Monitor PHP-FPM container logs to see the debug output:

```bash
# On ODIN server
cd ~/actions-runner/_work/shopsys/shopsys/jm-after-build-bug-fix-ssp-3495
docker compose logs -f php-fpm | grep -E "(PromotedCategories|SliderItems)"
```

### Phase 4: Data Seeding Verification
Check if demo data is properly seeded for the preview branch:

```bash
# Check demo data seeding status
docker compose exec php-fpm php phing demo-data-status
```

## Expected Outcomes

### If Root Cause is Confirmed:
- Database queries will show **0 records** for TopCategory and/or SliderItem for the preview branch domain
- PHP logs will show domain information and **0 result counts**
- Demo data seeding may be incomplete or failed

### If Root Cause is Different:
- Database queries will show **existing records** for the domain
- PHP logs will show **non-zero result counts** but queries still return empty
- Need to investigate deeper into query logic or filtering conditions

## Next Steps After Verification

### If Data is Missing:
1. **Re-run demo data seeding**: `docker compose exec php-fpm php phing demo-data`
2. **Check domain configuration**: Verify domain setup for preview branches
3. **Investigate build process**: Check if demo data is properly seeded during branch builds

### If Data Exists:
1. **Investigate query filters**: Check if domain ID or other filters are incorrect
2. **Verify date/time conditions**: Check if slider items are outside visible date range
3. **Debug JOIN conditions**: Verify TopCategory relationships are correct

## Implementation Priority
1. **High**: Execute database verification queries
2. **High**: Add PHP debug logging 
3. **Medium**: Monitor container logs during first load
4. **Low**: Investigate data seeding process if data is missing

This plan will definitively identify whether the issue is missing domain-specific data or a deeper query logic problem.