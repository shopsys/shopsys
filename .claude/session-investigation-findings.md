# GraphQL Empty Results Investigation Session

## Original User Report

**Initial Prompt (2025-07-18):**
> I have a big new issue now. I want you to analyze this properly. Consult heavily with Gemini 2.5 Pro. The issue is in our GitHub preview branches that are built when we push into a branch and make a pull request. There appears a new preview branch and the problem is when anyone visits the URL of the branch website, somehow the homepage is missing some parts. We have deeply debugged some storefront Next.js code and I will provide you this debug logs and I will also provide you with the debug code that is behind these logs. From the logs is visible that some two of the GraphQL queries don't return proper data. So the component that relies on the result of these queries does evaluate that there are no data so it will not display it. The component is I think sliders and homepage categories. So far we see that these queries return no data and also they don't return null results but they return empty results. So there is no error. So there is no error. It just doesn't display anything because empty data. And now comes a part where we need to find out why the backend in PHP or maybe Redis or maybe Postgres. But Postgres is called by PHP logic. And I think the Redis is called directly by storefront thatcher. Because every query can have a directive. I am not sure if these two failing queries do have this Redis directive. But I think from the logs is visible that the Redis cache is missed. So yeah, I am now realizing the Redis is maybe not the problem. But I am not 100% sure. It can be even Redis miss and somehow the Redis check could somehow result in these empty results. We don't know. We have to debug. But I would aim more firstly for a PHP backend logic that is returning these GraphQL queries. You should analyze where these codes, PHP codes for these queries are in and propose debug logging that we will see in the PHP container logs. And we could find out more about this case. Bring up any ideas. Consult with Gemini 2.5 Pro Zen MCP. Ultra thing. And now prepare a plan. How to analyze this issue. Analyze all the codes necessary. Prepare a comprehensive plan. And persist this plan. In a .cloud folder. In a new file.

**User's Key Findings:**
- **Failing Queries**: PromotedCategoriesQuery and SliderItemsQuery return empty arrays `[]`
- **Pattern**: Works after reload, fails on first load  
- **No Errors**: Status 200, structurally valid responses, just empty data
- **Environment**: GitHub preview branches on ODIN server
- **Cache Behavior**: Logs show "Cache miss, fetching fresh data"
- **Impact**: Homepage missing sliders and promoted categories components

## Current Status: CRITICAL BREAKTHROUGH
**Date**: 2025-07-18
**Issue**: 🔍 **ACTIVE** - Major discovery: Data exists but ORM queries return 0 results

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

### 🎯 ❌ DISPROVEN HYPOTHESIS: Missing Domain Data
~~The GitHub preview branches likely have **missing or incomplete domain-specific data**~~
- ~~No `TopCategory` records for the preview branch domain~~
- ~~No `SliderItem` records for the preview branch domain~~
- ~~Data seeding issues during branch initialization~~

**BREAKTHROUGH**: Database contains all expected data!

## Phase 3 Results: Database Direct Access Investigation

### 🔍 BREAKTHROUGH: Database Contains All Expected Data

**Database Query Method**: Using `docker compose exec php-fpm php bin/console doctrine:query:sql`

#### ✅ Raw Database Evidence
**categories_top table**:
```sql
SELECT domain_id, COUNT(*) as count FROM categories_top GROUP BY domain_id ORDER BY domain_id;
```
- **Domain 1**: 9 records ✅
- **Domain 2**: 9 records ✅

**slider_items table**:
```sql
SELECT domain_id, COUNT(*) as count FROM slider_items GROUP BY domain_id ORDER BY domain_id;
```
- **Domain 1**: 3 records ✅
- **Domain 2**: 3 records ✅

#### ✅ Filtered Query Testing
**SliderItems with complete filters**:
```sql
SELECT COUNT(*) FROM slider_items si 
WHERE si.domain_id = 1 
AND si.hidden = false 
AND (si.datetime_visible_from IS NULL OR si.datetime_visible_from <= '2025-07-18 00:00:00') 
AND (si.datetime_visible_to IS NULL OR si.datetime_visible_to >= '2025-07-18 00:00:00');
```
- **Result**: 3 records ✅

**TopCategories with INNER JOIN**:
```sql
SELECT COUNT(*) FROM categories_top ct 
INNER JOIN categories c ON ct.category_id = c.id 
WHERE ct.domain_id = 1;
```
- **Result**: 9 records ✅

### 🚨 CRITICAL DISCOVERY: ORM vs Raw SQL Mismatch

**Production PHP ORM Queries**: Return 0 results
**Raw SQL Queries**: Return expected results (3 slider items, 9 top categories)

**This indicates a fundamental issue with PHP ORM/Doctrine query generation or execution context.**

### 📋 How to Query Database in ODIN Environment

#### Method 1: Symfony Console (Recommended)
```bash
# Navigate to branch directory
cd ~/actions-runner/_work/shopsys/shopsys/jm-after-build-bug-fix-ssp-3495-f1

# Execute SQL query through Doctrine
docker compose exec php-fpm php bin/console doctrine:query:sql "SELECT * FROM table_name;"
```

#### Method 2: Check Available Database Commands
```bash
# List all database-related commands
docker compose exec php-fpm php bin/console list | grep -i -E '(doctrine|dbal|db)'
```

#### Method 3: Connection Details
```bash
# Check database configuration
docker compose exec php-fpm cat /var/www/html/project-base/app/.env | grep -i -E '(database|postgres|db_)'
```

**Database Configuration**:
- **Host**: `postgres`
- **Port**: `5432`
- **Database**: `shopsys`
- **User**: `root`
- **Password**: `root`

### 🔍 Root Cause Analysis

**What We Know**:
1. ✅ **Database contains all required data**
2. ✅ **Raw SQL queries return expected results**
3. ❌ **PHP ORM queries return 0 results**
4. ✅ **Cache logic is working correctly**
5. ✅ **PHP backend is being called (cache miss is accurate)**

**Possible Root Causes**:
1. **ORM Query Generation Issue** - Doctrine builds incorrect SQL
2. **Connection/Transaction Issue** - ORM connects to different database state
3. **Entity Mapping Issue** - ORM entities mapped to wrong tables/columns
4. **Query Execution Context** - ORM queries execute in different context
5. **Database Connection Pool** - Different connections see different data states

### ⚠️ What We Have NOT Ruled Out
- ORM entity mapping issues
- Database connection/transaction isolation problems
- Query execution context differences
- Doctrine query builder generating incorrect SQL
- Database connection pool issues

### 🔄 Next Investigation Steps
1. **Generate actual SQL from ORM queries** - Use Doctrine query logging to see generated SQL
2. **Compare ORM SQL vs working raw SQL** - Identify differences in query generation
3. **Check entity mapping configuration** - Verify ORM entities map to correct tables
4. **Test ORM queries in isolation** - Execute ORM queries with SQL logging enabled
5. **Investigate transaction/connection context** - Check if ORM queries execute in different context

### 🔄 Session Continuity
**Multi-Agent Framework**: See `.claude/session-continuity-framework.md` for complete handoff protocol
**Current Session**: #1 - Investigation Setup & Initial Implementation
**Next Agent Instructions**: Start with local testing validation, then deployment

### 📊 Iteration Tracking
**Current Iteration**: #1 - Comprehensive Domain & Query Logging
**Status**: Implementation Complete, Ready for Local Testing
**Files Modified**: 4 PHP files with comprehensive debugging logging
**Local Testing**: ⏳ Pending validation
**Deployment**: ⏳ Awaiting local testing approval

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