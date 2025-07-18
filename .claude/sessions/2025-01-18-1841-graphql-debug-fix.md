# GraphQL Debug Session - 2025-01-18 18:41

## Session Overview
- **Start Time**: 2025-01-18 18:41
- **Focus**: Fixing critical table name error in GraphQL debug logging
- **Context**: Correcting PromotedCategories debug code after discovering wrong table name

## Goals
- [x] Fix the critical table name error in PromotedCategoryRepository debug SQL
- [ ] Deploy corrected debug code to F5 branch for testing
- [ ] Validate that raw SQL diagnostic now works correctly
- [ ] Capture proper debugging data to identify real root cause

## Progress

### Fixed Critical Debug Error
- **Issue**: Raw SQL diagnostic was using wrong table name `top_categories` instead of `categories_top`
- **Root Cause**: Misread the @ORM\Table annotation in TopCategory entity
- **Fix**: Updated diagnostic SQL to use correct table name `categories_top`
- **Impact**: This was causing false "table does not exist" errors in debug logs

### Current Status
- Debug code corrected but not yet deployed
- Need to commit changes and push to F5 branch
- Ready to test with proper table names

### Next Steps
1. Deploy corrected debug code to F5 branch
2. Test with website reload to capture real debugging data
3. Analyze results to identify actual root cause of empty GraphQL results

### Update - 2025-01-18 19:01 PM

**Summary**: Executed direct database queries on SSH Odin F6 branch and prepared for new PR branch testing

**Git Changes**:
- Added: f5.log, f6.log, log.txt, log02.txt
- Current branch: jm-after-build-bug-fix-ssp-3495-f7 (commit: 6fe4e61d4f)

**Todo Progress**: 6 completed, 0 in progress, 0 pending
- ✓ Completed: Execute raw SQL queries on SSH Odin F6 branch

**Key Findings**:
- Successfully executed direct database queries on SSH Odin F6 branch
- Found typical behavior pattern: F6 starts with no data, gets populated later
- Database query results: categories_top=9, slider_items=3, categories=11
- This confirms the expected sequence of initial empty state → data population

**SSH Odin Database Commands Documented**:
```bash
# Categories top table
docker compose exec php-fpm php bin/console doctrine:query:sql "SELECT COUNT(*) FROM categories_top WHERE domain_id = 1"

# Slider items table  
docker compose exec php-fpm php bin/console doctrine:query:sql "SELECT COUNT(*) FROM slider_items WHERE domain_id = 1"

# Categories table
docker compose exec php-fpm php bin/console doctrine:query:sql "SELECT COUNT(*) FROM categories WHERE parent_id IS NOT NULL"
```

**Next Action**: Ready to test new PR branch with direct database queries before website visits to understand data population timing

### Update - 2025-01-18 19:25 PM

**Summary**: **MAJOR BREAKTHROUGH** - Identified critical ORM vs SQL contradiction that reveals the true root cause

**Git Changes**:
- Modified: .claude/sessions/2025-01-18-1841-graphql-debug-fix.md
- Added: f5.log, f6.log, f8.log, log.txt, log02.txt
- Current branch: jm-after-build-bug-fix-ssp-3495-f8 (commit: 4835f21be5)

**Todo Progress**: 7 completed, 0 in progress, 1 pending
- ✓ Completed: Analyze F8 logs and investigate ORM vs SQL contradiction
- 🔄 Pending: Investigate database connection/transaction isolation issues

**Critical Discovery - The Real Issue:**
**NOT a data seeding problem** - Data exists in the database!

**Evidence:**
- **Direct SQL Queries on F8**: categories_top=9, slider_items=3, categories=11 ✅
- **ORM Queries (F8 logs)**: Initially return 0 records ❌
- **Then mysteriously**: SliderItems ORM suddenly returns 3 records ✅

**Root Cause Analysis:**
This reveals a **database connection/configuration inconsistency** where:
1. Direct SQL connects to correct database with data
2. ORM initially connects to wrong database/schema (empty)
3. Mid-request, connection resolves to correct database

**Potential Causes:**
- Database connection pool switching between databases
- Transaction isolation level differences
- Multiple database configuration strings
- Schema/domain context switching
- Doctrine connection state issues

**Next Investigation:**
- Check database connection strings comparison
- Analyze transaction isolation levels
- Debug domain ID parameter usage
- Review Doctrine configuration for multiple connections

**Impact:** This explains the "random" behavior - it's connection inconsistency that resolves itself mid-request, not missing data.
