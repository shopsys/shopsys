# Implementation Plan

You are tasked with creating detailed implementation plans through an interactive, iterative process. You should be skeptical, thorough, and work collaboratively with the user to produce high-quality technical specifications.

## Initial Response

When this command is invoked:

1. **Check if parameters were provided**:
    - If a task description or context was provided as a parameter, skip the default message
    - Begin the research process immediately

2. **If no parameters provided**, respond with:
```
I'll help you create a detailed implementation plan. Let me start by understanding what we're building.

Please provide:
1. The task/feature description
2. Any relevant context, constraints, or specific requirements
3. Jira ticket key (e.g., PROJ-123) - I'll fetch details using MCP
4. Links to related implementations in the codebase

I'll analyze this information and work with you to create a comprehensive plan.

Tip: You can also invoke this command with a description directly: `/create-plan Add seller description management for multi-domain support`
```

Then wait for the user's input.

## Process Steps

### Step 1: Context Gathering & Initial Analysis

1. **Read all mentioned files immediately and FULLY**:
    - Related implementation plans
    - Any configuration files mentioned
    - **IMPORTANT**: Use the Read tool WITHOUT limit/offset parameters to read entire files
    - **CRITICAL**: DO NOT spawn sub-tasks before reading these files yourself in the main context
    - **NEVER** read files partially - if a file is mentioned, read it completely

2. **If a Jira ticket is mentioned, fetch its details first**:
    - Use **mcp__atlassian__getJiraIssue** with the ticket key to get full details
    - Extract acceptance criteria and requirements from the Jira issue description
    - Note any linked issues or dependencies

3. **Spawn initial research tasks to gather context**:
   Before asking the user any questions, use specialized agents to research in parallel:

    - Use the **docs-researcher** agent to find relevant architectural guidance and implementation patterns from documentation
    - Use the **codebase-locator** agent to find all files related to the task
    - Use the **codebase-analyzer** agent to understand how the current implementation works

   These agents understand Shopsys architecture and will:
    *Follow Package-First architecture (CLAUDE.md ## Monorepo Architecture)*
    *Apply development principles (CLAUDE.md ## Core Development Principles)*
    - Find relevant source files in the monorepo
    - Identify extension patterns (project extends package base classes)
    - Trace data flow through the codebase
    - Return detailed explanations with file:line references
    - Understand GraphQL/Frontend API and storefront patterns
    - Provide documentation context and best practices

4. **Read all files identified by research tasks**:
    - After research tasks complete, read ALL files they identified as relevant
    - Read them FULLY into the main context
    - This ensures you have complete understanding before proceeding

5. **Analyze and verify understanding**:
    - Cross-reference the task requirements with actual code
    - Identify any discrepancies or misunderstandings
    - Note assumptions that need verification
    - Determine true scope based on codebase reality

6. **Present informed understanding and focused questions**:
   ```
   Based on the task description and my research of the codebase, I understand we need to [accurate summary].

   I've found that:
   - [Current implementation detail with file:line reference]
   - [Relevant pattern or constraint discovered]
   - [Potential complexity or edge case identified]

   Questions that my research couldn't answer:
   - [Specific technical question that requires human judgment]
   - [Business logic clarification]
   - [Design preference that affects implementation]
   ```

   Only ask questions that you genuinely cannot answer through code investigation.

### Step 2: Research & Discovery

After getting initial clarifications:

1. **If the user corrects any misunderstanding**:
    - DO NOT just accept the correction
    - Spawn new research tasks to verify the correct information
    - Read the specific files/directories they mention
    - Only proceed once you've verified the facts yourself

2. **Create a research todo list** using TodoWrite to track exploration tasks

3. **Spawn parallel sub-tasks for comprehensive research**:
    - Create multiple Task agents to research different aspects concurrently
    - Use the right agent for each type of research:

   **For deeper investigation:**
    - **docs-researcher** - To find documentation patterns and architectural guidance related to the feature
    - **codebase-locator** - To find more specific files (e.g., "find all files that handle [specific component]")
    - **codebase-analyzer** - To understand implementation details (e.g., "analyze how [system] works")
    - **codebase-pattern-finder** - To find similar features we can model after

   **For historical context and related work:**
    - Use git log to find relevant commits and changes
    - Use **mcp__atlassian__searchJiraIssuesUsingJql** to find related Jira issues (e.g., search with JQL: `project = YOUR_PROJECT AND text ~ "seller" AND text ~ "domain"`)
    - Use **mcp__atlassian__getJiraIssue** to get full details of any referenced tickets
    - Look for similar patterns in existing features

   Each agent understands Shopsys monorepo patterns and can:
    - Navigate between project-base and packages/framework layers
    - Identify inheritance relationships (Project → Framework → Base)
    - Find patterns across backend, GraphQL API, and storefront
    - Locate both framework base classes and project customizations
    - Return specific file:line references for monorepo context
    - Find tests in project-base/app/tests/ structure

3. **Wait for ALL sub-tasks to complete** before proceeding

4. **Present findings and design options**:
   ```
   Based on my research, here's what I found:

   **Current State:**
   - [Key discovery about existing code]
   - [Pattern or convention to follow]

   **Design Options:**
   1. [Option A] - [pros/cons]
   2. [Option B] - [pros/cons]

   **Open Questions:**
   - [Technical uncertainty]
   - [Design decision needed]

   Which approach aligns best with your vision?
   ```

### Step 3: Plan Structure Development

Once aligned on approach:

1. **Create initial plan outline**:
   ```
   Here's my proposed plan structure:

   ## Overview
   [1-2 sentence summary]

   ## Implementation Phases:
   1. [Phase name] - [what it accomplishes]
   2. [Phase name] - [what it accomplishes]
   3. [Phase name] - [what it accomplishes]

   Does this phasing make sense? Should I adjust the order or granularity?
   ```

2. **Get feedback on structure** before writing details

### Step 4: Pattern Verification

Before writing the detailed plan:

1. **Validate implementation patterns** by spawning additional research tasks:
   - Use **codebase-pattern-finder** to find 3-5 concrete examples of each component type you plan to implement
   - For controllers: Verify security attribute usage (CanView, CanEdit parameter names)
   - For forms: Validate form type options, namespace imports
   - For fixtures: Check interface names (DependentFixtureInterface vs incorrect names)
   - For entities/services: Verify inheritance patterns and method signatures
   - Get file:line references for all patterns

2. **Update plan approach** based on validated patterns:
   - Incorporate exact namespace imports discovered
   - Use verified method signatures and parameter names
   - Follow established configuration patterns
   - This prevents implementation errors by validating patterns before detailed planning

### Step 5: Detailed Plan Writing

After structure approval and pattern verification:

1. **Write the plan** to `docs/plan/{descriptive_name}.md`
2. **Use this template structure**:

```markdown
# [Feature/Task Name] Implementation Plan

## Overview

[Brief description of what we're implementing and why]

## Current State Analysis

[What exists now, what's missing, key constraints discovered]

## Desired End State

[A Specification of the desired end state after this plan is complete, and how to verify it]

### Key Discoveries:
- [Important finding with file:line reference]
- [Pattern to follow]
- [Constraint to work within]

## What We're NOT Doing

[Explicitly list out-of-scope items to prevent scope creep]

## Implementation Approach

[High-level strategy and reasoning]

## Phase 1: [Descriptive Name]

### Overview
[What this phase accomplishes]

### Changes Required:

#### 1. [Component/File Group]
**File**: `path/to/file.ext`
**Changes**: [Summary of changes]

<code>
// Specific code to add/modify
</code>

### Success Criteria:

#### Automated Verification:
- [ ] Entity changes generate migration correctly: `docker compose exec php-fpm php phing db-migrations-generate`
- [ ] Unit tests pass: `docker compose exec php-fpm php phing tests-unit`
- [ ] Code standards pass: `docker compose exec php-fpm php phing standards-fix`
- [ ] Storefront types check: `docker compose exec storefront pnpm run typecheck` (if applicable)
- [ ] GraphQL schema synced: `make generate-schema` (if applicable)

#### Manual Verification:
- [ ] Feature works as expected when tested via UI
- [ ] Performance is acceptable under load
- [ ] Edge case handling verified manually
- [ ] No regressions in related features

---

## Phase 2: [Descriptive Name]

[Similar structure with both automated and manual success criteria...]

---

## Testing Strategy

### Unit Tests:
- [What to test]
- [Key edge cases]

### Manual Tests:
- [Manual verification scenarios]

### Manual Testing Steps:
1. [Specific step to verify feature]
2. [Another verification step]
3. [Edge case to test manually]

## Performance Considerations

[Any performance implications or optimizations needed]

## Migration Notes

[If applicable, how to handle existing data/systems]

## References

- Jira ticket: [TICKET-KEY] - fetched via mcp__atlassian__getJiraIssue
- Similar implementation: `[file:line]`
```

### Step 6: Sync and Review

1. **Present the completed plan**:
   - The plan is ready for review in the docs/plans/ directory

2. **Present the draft plan location**:
```
I've created the initial implementation plan at:
`docs/plan/[filename].md`

Please review it and let me know:
- Are the phases properly scoped?
- Are the success criteria specific enough?
- Any technical details that need adjustment?
- Missing edge cases or considerations?
   ```

3. **Iterate based on feedback** - be ready to:
   - Add missing phases
   - Adjust technical approach
   - Clarify success criteria (both automated and manual)
   - Add/remove scope items
   - Update the plan file directly when making changes

4. **Continue refining** until the user is satisfied

## Important Guidelines

1. **Be Skeptical**:
   - Question vague requirements
   - Identify potential issues early
   - Ask "why" and "what about"
   - Don't assume - verify with code

2. **Be Interactive**:
   - Don't write the full plan in one shot
   - Get buy-in at each major step
   - Allow course corrections
   - Work collaboratively

3. **Be Thorough**:
   - Read all context files COMPLETELY before planning
   - Research actual code patterns using parallel sub-tasks
   - Include specific file paths and line numbers
   - Write measurable success criteria with clear automated vs manual distinction
   - automated steps should use Docker-wrapped commands: `docker compose exec php-fpm php phing` for backend operations
   - storefront operations use: `docker compose exec storefront pnpm run [command]`

4. **Be Practical**:
   - Focus on incremental, testable changes
   - Consider migration and rollback
   - Think about edge cases
   - Include "what we're NOT doing"

5. **Track Progress**:
   - Use TodoWrite to track planning tasks
   - Update todos as you complete research
   - Mark planning tasks complete when done

6. **No Open Questions in Final Plan**:
   - If you encounter open questions during planning, STOP
   - Research or ask for clarification immediately
   - Do NOT write the plan with unresolved questions
   - The implementation plan must be complete and actionable
   - Every decision must be made before finalizing the plan

7. **Pattern Validation Before Writing**:
   - ALWAYS validate implementation patterns using pattern-finder research before detailed planning
   - Verify all component types you plan to implement have correct examples
   - This prevents implementation errors by catching pattern issues early

## Success Criteria Guidelines

**Always separate success criteria into two categories:**

1. **Automated Verification** (can be run by execution agents):
   - Commands that can be run: `make test`, `npm run lint`, etc.
   - Specific files that should exist
   - Code compilation/type checking
   - Automated test suites

2. **Manual Verification** (requires human testing):
   - UI/UX functionality
   - Performance under real conditions
   - Edge cases that are hard to automate
   - User acceptance criteria

**Format example:**
```markdown
### Success Criteria:

#### Automated Verification:
- [ ] Entity changes generate migration correctly: `docker compose exec php-fpm php phing db-migrations-generate`
- [ ] Unit tests pass: `docker compose exec php-fpm php phing tests-unit`
- [ ] Code standards pass: `docker compose exec php-fpm php phing standards-fix`
- [ ] Storefront types check: `docker compose exec storefront pnpm run typecheck` (if applicable)
- [ ] GraphQL schema synced: `make generate-schema` (if applicable)

#### Manual Verification:
- [ ] New feature appears correctly in the UI
- [ ] Performance is acceptable with 1000+ items
- [ ] Error messages are user-friendly
- [ ] Feature works correctly on mobile devices
```

## Common Patterns

*Follow Package-First architecture (CLAUDE.md ## Monorepo Architecture)*
*Apply development principles (CLAUDE.md ## Core Development Principles)*
*Use proper commands (CLAUDE.md ## Essential Development Commands)*
*Note visibility/typing rules (CLAUDE.md ### project-base/packages folder rules)*

### For Database Changes:
- Create/modify Doctrine entities in framework packages (`packages/framework/src/Model/`)
- Generate migrations with `docker compose exec php-fpm php phing db-migrations-generate`
- Update Repository methods in framework packages
- Add business logic to framework Facade classes
- Update forms and controllers in framework packages
- Only extend in project-base if customization is necessary (rare)

### For New Features:
- Research existing patterns in `packages/framework/` and other packages
- Implement core functionality in framework packages:
  - Entity, EntityDomain, EntityTranslation (if multi-domain/translatable)
  - EntityData and EntityDataFactory for form handling
  - Repository and Facade classes
  - FormType in framework package
  - Controller actions in framework package
- Only create project-base extensions if customization is needed
- Consider GraphQL resolvers in `project-base/app/src/FrontendApi/` if API needed
- Consider storefront components in `project-base/storefront/` if UI needed

### For Refactoring:
- Document current behavior
- Plan incremental changes
- Maintain backwards compatibility
- Include migration strategy

## Sub-task Spawning Best Practices

When spawning research sub-tasks:

1. **Spawn multiple tasks in parallel** for efficiency
2. **Each task should be focused** on a specific area
3. **Provide detailed instructions** including:
    - Exactly what to search for
    - Which directories to focus on
    - What information to extract
    - Expected output format
4. **Be EXTREMELY specific about directories**:
    - Focus on Shopsys monorepo structure:
      - Framework base classes: `packages/framework/src/Model/`, `packages/framework/src/Controller/Admin/`
      - Project customizations: `project-base/app/src/Model/`, `project-base/app/src/Controller/`
      - Frontend API: `project-base/app/src/FrontendApi/Resolver/`, `project-base/app/src/FrontendApi/Mutation/`
      - Storefront: `project-base/storefront/components/`, `project-base/storefront/pages/`
    - For admin features: `packages/framework/src/Controller/Admin/`, `packages/administration/templates/`
    - For GraphQL: `project-base/app/src/FrontendApi/`, `packages/frontend-api/src/`
    - Include the full monorepo path context in your prompts
5. **Specify read-only tools** to use
6. **Request specific file:line references** in responses
7. **Wait for all tasks to complete** before synthesizing
8. **Verify sub-task results**:
    - If a sub-task returns unexpected results, spawn follow-up tasks
    - Cross-check findings against the actual codebase
    - Don't accept results that seem incorrect

Example of spawning multiple tasks:
```python
# Spawn these tasks concurrently:
tasks = [
    Task("Research database schema", db_research_prompt),
    Task("Find API patterns", api_research_prompt),
    Task("Investigate UI components", ui_research_prompt),
    Task("Check test patterns", test_research_prompt)
]
```

## Docker Command Context

**IMPORTANT**: All Shopsys development happens in Docker containers. When planning implementations:

### Container-Specific Commands:
- **Backend/PHP**: `docker compose exec php-fpm php phing [command]`
- **Storefront**: `docker compose exec storefront pnpm run [command]`
- **Database**: Direct PostgreSQL access via docker exec when needed

### Commands That Run on Host (NOT in containers):
- Git operations: `git status`, `git commit`, `git push`
- Make commands: `make check-fix`, `make generate-schema`
- System commands: `ls`, `grep`, file operations

## Example Interaction Flow

```
User: /create-plan
Assistant: I'll help you create a detailed implementation plan...

User: We need to implement PROD-456 - Add product variant description management
Assistant: Let me fetch the Jira ticket details first...

[Uses mcp__atlassian__getJiraIssue to get PROD-456 details]

Based on the Jira ticket, I see we need to add product variant descriptions with multi-domain support. Let me research the current product and variant implementation in both the framework and project layers...

[Researches existing patterns in packages/framework/src/Model/Product/ and project-base/app/src/Model/Product/]

Based on my research, I found:
- ProductFacade in project-base extends BaseProductFacade from framework
- Product variants use ProductVariant entity with domain support
- Need to extend existing patterns for variant descriptions

Before I start planning, I have some questions about the scope...

[Interactive process continues with monorepo-aware context...]
```
