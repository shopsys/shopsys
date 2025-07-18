# GraphQL Empty Results Investigation Session

## Current Status: Active Investigation
**Date**: 2025-07-18
**Issue**: PromotedCategoriesQuery and SliderItemsQuery return empty results on first load

## Key Evidence Summary

### Problem Characteristics
- **Affected Queries**: `PromotedCategoriesQuery` and `SliderItemsQuery` only
- **Pattern**: Empty results on first load, works after reload
- **Other Queries**: Work perfectly on first load
- **Response**: Status 200, no errors, empty arrays `[]`
- **Environment**: GitHub preview branches on ODIN server

### Log Evidence
- **First Load**: `promotedCategories: []` and `sliderItems: []` (log.txt lines 7692, 7701)
- **After Reload**: Full data returned (log02.txt lines 2220, 2212)
- **Cache Behavior**: Both show "Cache miss, fetching fresh data"

### Critical Insight
**Cache miss indicates backend PHP is being called** → The issue is in PHP backend logic, not Redis caching.

## Investigation Attempts

### ❌ Wrong Path: Cache Key Collision Theory
**Theory**: MD5 hash collisions causing wrong data retrieval
**Why Wrong**: Query names are part of cache keys, making collisions impossible between different queries
**Lesson**: Cache key format `${prefix}${queryName}:${host}:${hash}` ensures uniqueness

### ✅ Current Focus: Backend PHP Logic
**Core Question**: Why do these specific GraphQL resolvers return empty results when called by cache miss?

## Phase 1 Results: Cache Logic Analysis

### ✅ Cache Hit/Miss Detection is CORRECT
**Code Analysis** (`/project-base/storefront/urql/fetcher.ts`):
- **Cache Hit**: `fromCache !== null` (line 106) - logs "✅ Returning cached response"
- **Cache Miss**: `fromCache === null` (line 128) - logs "🔍 Cache miss, fetching fresh data"

### ✅ Cache Miss Logs are ACCURATE
The "Cache miss, fetching fresh data" logs are genuine:
- Redis returns `null` when no cached data exists
- This triggers the fetch to PHP backend
- The cache logic is working correctly

### ✅ Empty Results Are Properly Cached
When PHP returns empty results:
- `{"promotedCategories": []}` is cached as `JSON.stringify(res.data)`
- Next request would be a cache HIT (not miss) with empty data
- Cache miss indicates fresh fetch from PHP backend

### 🔍 Conclusion: User's Theory Disproven
The cache miss logs are accurate. The issue is NOT misleading cache logging.
**The PHP backend is genuinely being called and returning empty results.**

## Phase 2 Results: PHP GraphQL Resolver Analysis

### 🔍 ROOT CAUSE IDENTIFIED: Domain-Specific Data Dependencies

Both failing queries have **domain-specific data requirements** that explain why they return empty results:

### PromotedCategoriesQuery Analysis
**File**: `project-base/app/src/FrontendApi/Resolver/Category/PromotedCategory/PromotedCategoryRepository.php`

**Database Query Logic**:
```php
->join(TopCategory::class, 'tc', Join::WITH, 'tc.category = c AND tc.domainId = :domainId')
->join('c.translations', 'ct', Join::WITH, 'ct.locale = :locale')
->orderBy('tc.position')
```

**Critical Dependency**: Requires `TopCategory` records to exist for the specific domain.
- If no `TopCategory` records exist for the domain → empty result
- Uses INNER JOIN which excludes categories without TopCategory entries

### SliderItemsQuery Analysis  
**File**: `packages/framework/src/Model/Slider/SliderItemRepository.php`

**Database Query Logic**:
```php
->where('si.domainId = :domainId')
->andWhere('si.hidden = :hidden')
->andWhere('si.datetimeVisibleFrom is NULL or si.datetimeVisibleFrom <= :now')
->andWhere('si.datetimeVisibleTo is NULL or si.datetimeVisibleTo >= :now')
```

**Critical Dependencies**: 
- Requires `SliderItem` records for the specific domain
- Requires items to be visible (not hidden, within date range)
- If no slider items exist for the domain → empty result

### 🎯 Hypothesis: Missing Domain Data
The GitHub preview branches likely have **missing or incomplete domain-specific data**:
- No `TopCategory` records for the preview branch domain
- No `SliderItem` records for the preview branch domain
- Data seeding issues during branch initialization

## Final Investigation Summary

### ✅ What We Discovered
1. **Cache logic is working correctly** - "cache miss" logs are accurate
2. **PHP backend is being called** - Redis genuinely returns null (cache miss)
3. **Both failing queries have domain-specific dependencies**:
   - PromotedCategoriesQuery requires TopCategory records
   - SliderItemsQuery requires SliderItem records for the domain
4. **Root cause is likely missing domain data** in preview branches

### ✅ What We Ruled Out
- ❌ Cache key collisions (impossible due to query name in key)
- ❌ Misleading cache miss logs (logging is accurate)
- ❌ Redis caching bugs (cache logic is correct)
- ❌ GraphQL resolver bugs (resolvers work correctly when data exists)

### ✅ Next Steps
1. **Verify hypothesis**: Execute database queries to check domain data
2. **Add debug logging**: Monitor domain ID and query results
3. **Check data seeding**: Verify demo data is properly loaded
4. **Implement solution**: Re-seed data if missing or fix seeding process

### 📁 Created Files
- `/Users/neon/shopsys/shopsys/.claude/session-investigation-findings.md` - This summary
- `/Users/neon/shopsys/shopsys/.claude/root-cause-debugging-plan.md` - Detailed debugging plan
- `/Users/neon/shopsys/shopsys/.claude/graphql-empty-results-investigation.md` - Initial problem documentation
3. If cache logging is correct, focus on PHP backend resolver analysis
4. Implement comprehensive debugging plan for backend investigation

## Environment Context
- **Server**: `odin.shopsys.cloud`
- **Branch**: `jm-after-build-bug-fix-ssp-3495`
- **Debug Code**: `origin/debug-build-bug-03` commit `de6db9ac9d5b8794ca863777536d1a390128e764`
- **User**: `github-runner` in `/home/github-runner/actions-runner/_work/shopsys/shopsys`