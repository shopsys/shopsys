# Research Codebase

You are tasked with conducting comprehensive research across the codebase to answer user questions by spawning parallel sub-agents and synthesizing their findings.

## Initial Setup:

When this command is invoked, respond with:
```
I'm ready to research the codebase. Please provide your research question or area of interest, and I'll analyze it thoroughly by exploring relevant components and connections.
```

Then wait for the user's research query.

## Steps to follow after receiving the research query:

1. **Read any directly mentioned files first:**
    - If the user mentions specific files (tickets, docs, JSON), read them FULLY first
    - **IMPORTANT**: Use the Read tool WITHOUT limit/offset parameters to read entire files
    - **CRITICAL**: Read these files yourself in the main context before spawning any sub-tasks
    - This ensures you have full context before decomposing the research

2. **Analyze and decompose the research question:**
    - Break down the user's query into composable research areas
    - Take time to ultrathink about the underlying patterns, connections, and architectural implications the user might be seeking
    - Consider Shopsys architecture implications:
      *Follow Package-First architecture (CLAUDE.md ## Monorepo Architecture)*
      - Framework packages contain core implementations
      - Project-base for configuration and rare extensions
      - Extension relationships: Project extends Package base classes (when needed)
      - Multi-domain and translation support
      - GraphQL API and storefront integration
    - Identify specific components, patterns, or concepts to investigate
    - Create a research plan using TodoWrite to track all subtasks
    - Consider which monorepo layers and directories are relevant

3. **Spawn parallel sub-agent tasks for comprehensive research:**
    - Create multiple Task agents to research different aspects concurrently
    - We now have specialized agents that know how to do specific research tasks:

   **For documentation research:**
    - Use the **docs-researcher** agent to find relevant architectural guidance, patterns, and best practices from the docs/ folder
    - This agent understands the documentation structure and can provide context for development decisions
    - Especially useful for understanding conventions, configuration examples, and implementation approaches

   **For codebase research:**
    - Use the **codebase-locator** agent to find WHERE files and components live
    - Use the **codebase-analyzer** agent to understand HOW specific code works
    - Use the **codebase-pattern-finder** agent if you need examples of similar implementations

   **For pattern validation research:**
    - ALWAYS use **codebase-pattern-finder** to find 3-5 concrete examples of similar implementations
    - Focus on extracting exact namespace imports, method signatures, option configurations
    - Validate form type options, security attribute patterns, interface names
    - Get file:line references for all patterns to verify against framework conventions

    These agents understand the Shopsys architecture (see CLAUDE.md for Package-First details):
    - They prioritize packages/framework layers for core implementations
    - They check project-base only for configuration and rare extensions
    - They recognize extension patterns (project extends package)
    - They understand GraphQL, storefront, and multi-domain patterns

   **For Jira tickets (if relevant):**
    - Use **mcp__atlassian__getJiraIssue** to get full details of a specific ticket
    - Use **mcp__atlassian__searchJiraIssuesUsingJql** to find related tickets or historical context

   **For web research (only if user explicitly asks):**
    - Use the **web-search-researcher** agent for external documentation and resources
    - IF you use web-research agents, instruct them to return LINKS with their findings, and please INCLUDE those links in your final report


   The key is to use these agents intelligently:
    - Start with locator agents to find what exists
    - Then use analyzer agents on the most promising findings
    - Run multiple agents in parallel when they're searching for different things
    - Each agent knows its job - just tell it what you're looking for
    - Don't write detailed prompts about HOW to search - the agents already know

4. **Wait for all sub-agents to complete and synthesize findings:**
    - IMPORTANT: Wait for ALL sub-agent tasks to complete before proceeding
    - Compile all sub-agent results focusing on live codebase findings
    - Connect findings across different components and layers (project-base vs packages)
    - Include specific file paths and line numbers for reference
    - Highlight monorepo patterns, inheritance chains, and architectural decisions
    - Note relationships between framework base classes and project extensions
    - Answer the user's specific questions with concrete evidence

5. **Gather metadata for the research document:**
    - Collect git metadata directly:
      - Current commit: `git rev-parse HEAD`
      - Current branch: `git branch --show-current`
      - Repository info: `git remote get-url origin`
    - Generate filename: `docs/research/YYYY-MM-DD-topic-description.md`
        - Format: `YYYY-MM-DD-topic-description.md` where:
            - YYYY-MM-DD is today's date
            - topic-description is a brief kebab-case description of the research
        - Examples:
            - `2025-01-15-product-variant-management.md`
            - `2025-01-15-graphql-resolver-patterns.md`

6. **Generate research document:**
    - Use the metadata gathered in step 5
    - Structure the document with YAML frontmatter followed by content:
      ```markdown
      ---
      date: [Current date and time with timezone in ISO format]
      git_commit: [Current commit hash]
      branch: [Current branch name]
      repository: "shopsys/shopsys"
      topic: "[User's Question/Topic]"
      tags: [research, codebase, relevant-component-names]
      ---
 
      # Research: [User's Question/Topic]
 
      **Date**: [Current date and time with timezone]
      **Git Commit**: [Current commit hash]
      **Branch**: [Current branch name]
      **Repository**: shopsys/shopsys
 
      ## Research Question
      [Original user query]
 
      ## Summary
      [High-level findings answering the user's question]
 
      ## Detailed Findings
 
      ### Framework Layer (packages/framework/)
      - Finding with reference (`file.php:line`)
      - Base class implementations and patterns
      - Core framework functionality
 
      ### Project Layer (project-base/app/)
      - Custom implementations and extensions
      - Project-specific business logic
      - Inheritance relationships with framework
 
      ### Frontend API Layer (project-base/app/src/FrontendApi/)
      - GraphQL resolvers and mutations
      - API integration patterns
 
      ### Storefront Layer (project-base/storefront/)
      - React components and TypeScript patterns
      - Frontend implementation details
 
      ## Code References
      - `packages/framework/src/Model/Product/ProductFacade.php:45` - Base facade implementation
      - `project-base/app/src/Model/Product/ProductFacade.php:12` - Project facade extension
      - `project-base/storefront/components/Product/ProductDetail.tsx:23` - Frontend component
 
      ## Architecture Insights
      [Monorepo patterns, inheritance chains, and design decisions discovered]
      - Inheritance pattern: Project extends Framework base classes
      - Multi-domain support implementation
      - GraphQL integration between backend and storefront
 
      ## Related Research
      [Links to other research documents in docs/research/]
 
      ## Open Questions
      [Any areas that need further investigation]
      ```

7. **Present findings to user:**
    - Save the research document to `docs/research/`
    - Present a concise summary of findings to the user
    - Include key file references for easy navigation
    - Highlight monorepo patterns and inheritance relationships discovered
    - Ask if they have follow-up questions or need clarification

## Important notes:
- Always use parallel Task agents to maximize efficiency and minimize context usage
- Always run fresh codebase research - never rely solely on existing research documents
- Focus on finding concrete file paths and line numbers for developer reference
- Research documents should be self-contained with all necessary context
- Each sub-agent prompt should be specific and focused on read-only operations
- Consider cross-component connections and monorepo architectural patterns
- Include temporal context (when the research was conducted)
- Keep the main agent focused on synthesis, not deep file reading
- Encourage sub-agents to find examples and usage patterns, not just definitions
- **File reading**: Always read mentioned files FULLY (no limit/offset) before spawning sub-tasks
- **Critical ordering**: Follow the numbered steps exactly
    - ALWAYS read mentioned files first before spawning sub-tasks (step 1)
    - ALWAYS wait for all sub-agents to complete before synthesizing (step 4)
    - ALWAYS gather metadata before writing the document (step 5 before step 6)
    - NEVER write the research document with placeholder values
- **Shopsys Architecture awareness** (see CLAUDE.md):
    - Prioritize framework packages for core implementations and patterns
    - Check project-base for configuration and rare extensions
    - Look for extension patterns (project extends package)
    - Include GraphQL/Frontend API and storefront patterns
    - Note multi-domain and translation support patterns
- **Frontmatter consistency**:
    - Always include frontmatter at the beginning of research documents
    - Keep frontmatter fields consistent across all research documents
    - Use snake_case for multi-word field names (e.g., `git_commit`)
    - Tags should be relevant to the research topic and Shopsys components studied
