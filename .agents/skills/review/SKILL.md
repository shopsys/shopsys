---
name: review
description: >
  Deep, comprehensive code review with fresh eyes - zero tolerance for mistakes.
  Use when the user asks to review code, do a code review, or invokes /review.
user_invocable: true
version: 1.0.0
---

# Deep Code Review Command

**MINDSET: You are a senior reviewer seeing this code for the FIRST TIME. Forget all prior context. Read every line as if you've never seen it before. Question everything. Trust nothing.**

**CRITICAL: Files to review:** `$ARGUMENTS`

**If files are specified above - STOP and read ONLY those files. Do NOT run git status. Do NOT check other files. Review ONLY the files listed above.**

If no files specified above (empty) - then review all uncommitted changes.

---

## Phase 1: Gather Context

### When Specific Files Provided

1. Read ONLY the specified files using the Read tool
2. Load project standards from `CLAUDE.md` and `.claude/skills/` (if exists)
3. For each file, also read surrounding context (imports, related files, interfaces it implements) to verify correctness

### When No Files Provided

1. Run git commands to gather the full picture:
```bash
git --no-pager status
git --no-pager diff --stat
git --no-pager diff --cached --stat
git --no-pager log --oneline -10
```
Then read every changed file in full (not just diffs) to understand complete context.
2. Load project standards from `CLAUDE.md` and `.claude/skills/` (if exists)
3. Review all changed files

---

## Phase 2: Deep Verification (FRESH EYES)

For EVERY changed file, verify each of these with zero assumptions:

### Reality Check - Is Everything True?
- Does every variable/function name accurately describe what it does?
- Do comments match the actual behavior of the code?
- Are all TODO/FIXME items addressed or intentionally left?
- Do error messages accurately describe the error condition?
- Are all magic numbers/strings explained or extracted to constants?
- Do commit messages and PR descriptions match what the code actually does?

### Logic & Correctness
- Trace every code path manually - are there unreachable branches?
- Check every conditional - are the operators correct? (`>` vs `>=`, `===` vs `==`, `&&` vs `||`)
- Verify loop boundaries - off-by-one errors? Infinite loop possibilities?
- Check null/undefined handling - can any variable be null when not expected?
- Verify return types actually match what is returned in ALL code paths
- Are default values correct and safe?
- Are edge cases handled? (empty arrays, zero values, null, negative numbers, empty strings)

### Data Flow & State
- Trace data from input to output - does it transform correctly at each step?
- Are there race conditions or timing issues?
- Is mutable state modified unexpectedly?
- Are closures capturing the right variables? (stale closure issues)
- Are dependencies/props used correctly - no stale references?

### Copy-Paste & Consistency Errors
- Look for copy-pasted code where variable names weren't updated
- Check for inconsistent naming within the same scope
- Verify imports are actually used and correct
- Look for dead code that should have been removed

---

## Phase 3: Standard Review Checklist

### Code Quality
- DRY - is there duplicated logic?
- KISS - is there unnecessary complexity?
- Clear naming and structure
- Proper visibility (`protected` in packages, `private` in project-base)
- Correct typehints and docblocks (arrays must specify contents, callables must specify signatures)

### Performance
- N+1 query problem - queries inside loops, missing eager loading
- Unnecessary database calls or re-fetches
- Missing indexes for new queries
- Unnecessary re-renders (React) or re-computations
- Large objects in dependency arrays that should be serialized/memoized

### Security
- Access control on all layers (UI, API, business logic)
- No sensitive data exposure (logs, error messages, API responses)
- Input validation where needed
- SQL injection prevention (use Doctrine QueryBuilder, no raw SQL)
- XSS prevention (output escaping)
- CSRF protection

### Patterns
- Business logic in Entity (domain logic) or Facade (orchestration), not in Resolver/Loader
- Nullable types verified against actual database/API constraints
- Package-first development (logic in `/packages/`, not `/project-base/`)
- Proper use of Facades vs Repositories
- Following existing codebase patterns - verify by reading similar existing code

### Doctrine
- Correct lazy/eager loading configuration
- Proper cascade operations
- Migrations included for schema changes

### GraphQL
- Resolver return types match schema exactly
- Batch loaders used for collections (avoid N+1)
- Proper error handling in resolvers

### React / Storefront
- useEffect dependency arrays are correct and complete
- useEffectEvent used for callbacks that shouldn't trigger re-runs
- No stale closures in effects, callbacks, or event handlers
- Complex objects serialized before use in dependency arrays
- Proper cleanup in useEffect return functions
- startTransition used for non-urgent state updates where appropriate

### Testing
- Are new features covered by tests?
- Do tests actually test the right thing? (not just asserting true === true)
- Correct test base class used?
- `@inject` annotation instead of `getContainer()->get()`
- GraphQL test coverage for API changes
- Acceptance test coverage for UI changes
- Edge cases covered in tests?

---

## Severity Guide

| Level | Use when |
|-------|----------|
| **Critical** | Security vulnerabilities, data loss risk, broken functionality, incorrect logic, wrong behavior |
| **Warning** | Performance issues, missing tests, pattern violations, potential bugs under edge cases |
| **Suggestion** | Style improvements, minor optimizations, nice-to-haves |

## Output Format

```
## Critical (must fix)
- [file:line] Issue description, WHY it's wrong, and exact fix

## Warnings (should fix)
- [file:line] Issue description and suggestion

## Suggestions (consider)
- [file:line] Optional improvement idea

## Verified Correct
- Brief list of things that were double-checked and confirmed correct

## Summary
[Overall assessment - confidence level in the code's correctness]
```

## Rules

- **FRESH EYES**: Pretend you have never seen this code before. Re-read everything.
- **VERIFY, DON'T ASSUME**: If something looks right, prove it by checking the actual implementation/types/schema.
- **ZERO TOLERANCE**: No mistakes accepted. Flag anything that is even slightly suspicious.
- Be specific - reference exact files and line numbers
- Provide exact solutions, not just problems
- When reviewing diffs, also check surrounding context for integration issues
- Skip praise - get straight to actionable feedback
- If you find nothing wrong after deep review, explicitly state what you verified and why you're confident
