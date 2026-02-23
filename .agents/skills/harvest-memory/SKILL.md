---
name: harvest-memory
description: Extracts key learnings from a session and persists them using Memory MCP tools.
---

# Harvest Memory

You are tasked with reviewing the FULL conversation transcript now in context and extract:

- Topic / objective of the session
- Approaches/strategies attempted
- Dead ends / what went wrong
- Prompts or actions that led to good outcomes
- Architecture discoveries about the app (esp. "storefront") and any other systems
- Future improvements / refactors / optimizations

Then **use the Memory MCP tools** to persist this into the knowledge-graph memory (do NOT handcraft the structure yourself):

1. Ensure entities exist:
    - A session entity (name it with a stable ID, e.g., `Session_<ISO date>_<short topic>`) of type `"conversation"`.
    - The relevant project entity (e.g., `"StorefrontApp"`) of type `"project"`, creating it if missing.
      Use `create_entities`.

2. Relate entities:
    - Link the session → project with an active-voice relation (e.g., `"concerns"`).
      Use `create_relations`.

3. Add observations:
    - For the session entity, add granular observations for topic, approaches, dead ends, successes, and any notable prompts.
    - For the project entity, add architecture findings and future improvements.
      Use `add_observations`.

Each observation must be a single fact (atomic). If you’re unsure which entity to attach something to, prefer the project entity for architecture/improvement facts and the session entity for process/history facts.

Execute the memory tool calls now so the data is actually stored.

Ultrathink.
