---
description: "GraphQL Empty Results Investigation - PromotedCategoriesQuery & SliderItemsQuery"
created: "2025-07-18"
status: "Investigation Phase"
---

# GraphQL Empty Results Investigation

## Problem Statement

On GitHub preview branches (built when pushing to branch and making PR), homepage is missing some parts because two specific GraphQL queries return empty results on first load but work properly on reload:

- `PromotedCategoriesQuery` 
- `SliderItemsQuery`

**Key Characteristics:**
- Other GraphQL queries work fine on first load
- No errors returned - just empty arrays `[]`
- Status 200 responses with valid GraphQL structure
- After reload, queries return proper data
- Issue occurs on review branches and current dev branch (17.0) running on Odin

## Evidence from Logs

### First Load (Empty Results)
From `log.txt`:
- Line 7692: `promotedCategories: []` 
- Line 7701: `sliderItems: []`
- Both show `hasData: true, hasError: false` - structurally valid but empty

### After Reload (Working)
From `log02.txt`:
- Line 2220: `promotedCategories` returns full data with categories
- Line 2212: `sliderItems` returns full data with slider items

## Technical Details

### Cache Behavior
- Both cases show "Cache miss, fetching fresh data"
- This means Redis cache is not the issue - backend PHP logic is being called
- Empty results are cached with TTL 3600 seconds after first load
- On reload, backend returns proper data which gets cached

### GraphQL Flow
1. Frontend requests `PromotedCategoriesQuery` and `SliderItemsQuery`
2. Redis cache miss occurs
3. Backend PHP GraphQL resolvers are called
4. PHP queries Postgres database
5. **Something returns empty results specifically for these two queries**
6. Empty results are cached and returned to frontend
7. Frontend components see empty data and don't render

## Key Questions for Root Cause Analysis

1. **Why do these specific queries return empty results while others work?**
2. **What is different about the PHP GraphQL resolvers for these queries?**
3. **Are there specific database conditions or dependencies for these queries?**
4. **Could there be a race condition in the PHP backend initialization?**
5. **Are there any special permissions, filtering, or domain-specific logic?**

## Debug Code Location

Branch: `origin/debug-build-bug-03`
Commit: `de6db9ac9d5b8794ca863777536d1a390128e764`

## Next Steps

1. Analyze the PHP GraphQL resolvers for these specific queries
2. Identify the database queries being executed
3. Add comprehensive logging to trace the execution path
4. Determine if there are timing or dependency issues
5. Investigate if there are domain-specific or environment-specific conditions

## Environment Details

- **Server**: `odin.shopsys.cloud` 
- **Current Branch**: `jm-after-build-bug-fix-ssp-3495`
- **Current Directory**: `/home/github-runner/actions-runner/_work/shopsys/shopsys`
- **User**: `github-runner`

## Investigation Focus

Focus on PHP backend logic and database queries rather than cache mechanisms, since cache miss indicates the backend is being called but returning empty results for these specific queries only.