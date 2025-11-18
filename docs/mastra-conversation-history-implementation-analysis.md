# Mastra Conversation History Implementation: Complete Analysis & Master Reference

**Date:** November 14, 2025
**Status:** Final Analysis
**Project:** Shopsys Platform - Mastra SQL Assistant Integration
**Context:** Investigation of proper conversation history management patterns in Mastra-based AI agents

---

## Table of Contents

1. [Executive Summary](#executive-summary)
2. [Background and Research Context](#background-and-research-context)
3. [Source Documents Analysis](#source-documents-analysis)
4. [How Mastra Actually Works](#how-mastra-actually-works)
5. [Current Implementation Analysis](#current-implementation-analysis)
6. [Understanding OpenAI API and Conversation Patterns](#understanding-openai-api-and-conversation-patterns)
7. [Prompt Caching Explained](#prompt-caching-explained)
8. [Correct Implementation Patterns](#correct-implementation-patterns)
9. [Answers to Specific Questions](#answers-to-specific-questions)
10. [Migration Guide](#migration-guide)
11. [Production Recommendations](#production-recommendations)
12. [References](#references)

---

## Executive Summary

### Key Findings

**Question:** When implementing continuous conversation with Mastra AI agents, do we need to send the entire conversation history with each request?

**Answer:** **NO** - when using Mastra Memory with `threadId`, you should only send the **latest message**. Mastra automatically retrieves conversation history from the database and assembles the complete context for the LLM.

### Critical Insights

1. **Duplicate Message Problem (Document 1):** ❌ **FALSE**
   - Original claim: Mixing full history + memory parameters creates duplicate database entries
   - Reality: Mastra has sophisticated message tracking (Sets) that prevents duplicate database storage
   - However, the recommendation to send only latest message remains valid for other reasons

2. **Message Tracking System (Document 2):** ✅ **VERIFIED**
   - Mastra uses `memoryMessages`, `newUserMessages`, `newResponseMessages` Sets
   - Only new messages are persisted via `drainUnsavedMessages()` filter
   - Memory-loaded messages are explicitly excluded from persistence

3. **Stateless Pattern (Document 3):** ✅ **CONFIRMED**
   - OpenAI Chat Completions API is stateless - requires full history every call
   - Mastra handles this automatically by loading from database + assembling messages
   - Vercel AI SDK v5 is used as the foundation
   - Responses API is default for OpenAI (supports reasoning models)

4. **Current Shopsys Implementation:** ❌ **NEEDS REFACTORING**
   - File: `packages/administration/templates/content/mastra/sql.html.twig`
   - Problem: Mixing client-side history array with Mastra Memory parameters
   - Impact: Redundant data transfer, two sources of truth, lost state on refresh
   - Solution: Remove client array, send only latest message, load history from DB for UI

5. **Prompt Caching:** ✅ **WORKS AUTOMATICALLY**
   - OpenAI: Automatic for prompts > 1,024 tokens, 50% cost savings, no configuration
   - Anthropic: Requires explicit `cacheControl` markers, 90% cost savings
   - Mastra-compatible in both cases

### Recommendations

**Immediate Action:**
- Refactor `sql.html.twig` to remove `conversationHistory` client array
- Send only latest user message to Mastra API
- Use `threadId` + `resourceId` for memory management
- Load historical messages from database for UI display on page load

**Architecture:**
- Pattern B (Mastra Memory) - server-managed state, database persistence
- NOT Pattern A (Client History) - client-managed arrays
- NOT Mixed Pattern (current implementation) - confusing, redundant

---

## Background and Research Context

### Original Problem Statement

The investigation began with fundamental questions about implementing conversational AI agents:

**User's Questions:**
1. When creating a continuous conversation loop UI with OpenAI API, do I need to send the entire conversation history with each loop iteration?
2. How does OpenAI prompt caching relate to conversation history management?
3. How should I structure my vanilla JavaScript admin interface to properly manage conversation state?
4. What is Mastra doing internally with the messages I send?

**Investigation Approach:**

Three separate research efforts were conducted:

1. **Document 1:** Initial Mastra documentation research (Claude Code with Mastra MCP)
2. **Document 2:** Source code investigation of Mastra GitHub repository
3. **Document 3:** Deep dive into Mastra conversation history and OpenAI API patterns

This document synthesizes all findings and provides definitive answers.

---

## Source Documents Analysis

### Document 1: Initial Hypothesis (Partially Incorrect)

**Location:** `docs/mastra-memory-and-conversation-history-guide.md`

**Main Claims:**

1. **Duplicate Message Problem (PRIMARY CLAIM):**
   - When you send full history + memory parameters, Mastra creates duplicate database entries
   - **Verdict:** ❌ FALSE (disproven by Document 2)

2. **Two Distinct Patterns:**
   - Pattern A: Client-Side History (no memory) - Valid
   - Pattern B: Mastra Memory (send only latest) - Recommended
   - **Verdict:** ✅ TRUE

3. **Three Memory Types:**
   - Conversation History, Semantic Recall, Working Memory
   - **Verdict:** ✅ TRUE

4. **API Evolution:**
   - Deprecated vs Modern API structures
   - **Verdict:** ✅ TRUE

**Overall Assessment:** Correct recommendations but incorrect reasoning about duplicates.

### Document 2: Source Code Investigation (Correct)

**Location:** `/Users/neon/shopsys/mastra_01/mastra/shopsys-docs/mastra-memory-investigation-findings.md`

**Critical Findings:**

**Message Source Tracking System:**

```typescript
export class MessageList {
  private memoryMessages = new Set<MastraDBMessage>();      // From database
  private newUserMessages = new Set<MastraDBMessage>();     // New user input
  private newResponseMessages = new Set<MastraDBMessage>(); // AI responses
}
```

**Selective Persistence Logic:**

```typescript
public drainUnsavedMessages(): MastraDBMessage[] {
  const messages = this.messages.filter(m =>
    this.newUserMessages.has(m) || this.newResponseMessages.has(m)
  );
  // memoryMessages EXPLICITLY EXCLUDED
  return messages;
}
```

**Key Insight:** Mastra's architecture PREVENTS duplicate storage through Set-based message tracking.

**Verdict:** ✅ CORRECT - Authoritative source code analysis

### Document 3: API Deep Dive (Correct)

**Location:** `/Users/neon/shopsys/mastra_01/mastra/shopsys-docs/mastra-conversation-history-and-prompt-caching-deep-dive.md`

**Key Findings:**

1. **Stateless Pattern Confirmed:**
   - YES, Mastra resends complete history with each OpenAI call
   - This is standard for Chat Completions API

2. **Vercel AI SDK v5:**
   - Foundation for Mastra's LLM interactions
   - Responses API default for OpenAI

3. **Prompt Caching:**
   - OpenAI: Automatic (>1024 tokens)
   - Anthropic: Explicit configuration
   - Both compatible with Mastra

**Verdict:** ✅ CORRECT - Comprehensive technical analysis

---

## How Mastra Actually Works

### Complete Message Flow

**Turn 1: First User Message**

```
USER: "Show me top 10 products"

CLIENT REQUEST:
  POST /mastra/api/agents/sqlAgent/generate
  {
    messages: [{ role: 'user', content: 'Show me top 10 products' }],
    threadId: 'admin_thread_superadmin_a3f2b8c1',
    resourceId: 'admin_resource_superadmin'
  }

MASTRA BACKEND:
  1. Resolve threadId → Check database
  2. Thread not found → Create new thread
  3. Load memory messages → [] (empty)
  4. Create MessageList:
     - memoryMessages: {}
     - newUserMessages: {Message1}
  5. Assemble LLM context: [System, Message1]
  6. Call OpenAI API with full context
  7. Receive response
  8. Save to database: [Message1, Response1]

RESPONSE: Generated SQL query
```

**Turn 2: Follow-up (Current Implementation - Problematic)**

```
USER: "How many orders last month?"

CLIENT REQUEST (CURRENT - WRONG):
  {
    messages: [
      { role: 'user', content: 'Show me top 10 products' },    // ❌ Already in DB
      { role: 'assistant', content: 'Here's a SQL query...' }, // ❌ Already in DB
      { role: 'user', content: 'How many orders last month?' } // ✅ New
    ],
    threadId: 'admin_thread_superadmin_a3f2b8c1'
  }

MASTRA BACKEND:
  1. Thread found in database
  2. Load memory: [Message1, Response1]
  3. Create MessageList:
     - memoryMessages: {Message1, Response1}
     - newUserMessages: {Message1, Response1, Message2}
  4. LLM context: [System, Message1, Response1, Message1, Response1, Message2]
     ⚠️ Duplicates in LLM context (wasteful, but caching helps)
  5. Save: drainUnsavedMessages() → [Message2, Response2]
     ✅ No duplicates in database (tracking system works)
```

**Turn 2: Correct Implementation**

```
CLIENT REQUEST (CORRECT):
  {
    messages: [
      { role: 'user', content: 'How many orders last month?' }  // ✅ Only new
    ],
    threadId: 'admin_thread_superadmin_a3f2b8c1'
  }

MASTRA BACKEND:
  1. Load memory: [Message1, Response1]
  2. Create MessageList:
     - memoryMessages: {Message1, Response1}
     - newUserMessages: {Message2}
  3. LLM context: [System, Message1, Response1, Message2]
     ✅ No duplicates, clean context
  4. Save: [Message2, Response2]
     ✅ Optimal
```

### Key Architecture Points

1. **MessageList Class:** Central message management with Set-based tracking
2. **Memory System:** Thread-based with configurable retrieval (`lastMessages`, `semanticRecall`)
3. **Storage Layer:** Pluggable (PostgreSQL, ChromaDB, Pinecone, etc.)
4. **LLM Execution:** Stateless pattern with full context assembly
5. **Deduplication:** Multiple levels prevent duplicate database entries

---

## Current Implementation Analysis

### File: `packages/administration/templates/content/mastra/sql.html.twig`

**Problems Identified:**

#### Problem 1: Client-Side History Management ❌

```javascript
let conversationHistory = [];  // Line 127
```

**Issue:** Duplicates Mastra's database-backed memory system

**Impact:**
- Two sources of truth
- State lost on refresh
- Potential inconsistencies

#### Problem 2: Sending Full History with Memory ❌

```javascript
conversationHistory.push({ role: 'user', content: message });

const res = await fetch('/mastra/api/agents/sqlAgent/generate', {
    body: JSON.stringify({
        messages: conversationHistory,  // ❌ Full array
        threadId: threadId,             // ✅ Memory enabled
        resourceId: resourceId
    })
});

conversationHistory.push({ role: 'assistant', content: responseText });
```

**Issue:** Mixing Pattern A with Pattern B

**Impact:**
- Redundant data transfer
- Larger payloads
- Potential LLM context confusion

#### Problem 3: No History Load on Page Load ❌

**Current:** Welcome message shown, history lost on refresh
**Expected:** Load previous messages from database

#### Problem 4: Manual Response Tracking ❌

**Issue:** Manually adding responses to client array when Mastra already persists them

**Impact:** Duplicate state management logic

---

## Understanding OpenAI API and Conversation Patterns

### OpenAI Chat Completions API: Stateless

**Fundamental Principle:** Each API call is independent.

**Direct API Usage (Python):**

```python
from openai import OpenAI
client = OpenAI()

history = []

# Turn 1
history.append({"role": "user", "content": "What is 2+2?"})
response = client.chat.completions.create(model="gpt-4o", messages=history)
history.append({"role": "assistant", "content": response.choices[0].message.content})

# Turn 2 - MUST resend full history
history.append({"role": "user", "content": "What about 3+3?"})
response = client.chat.completions.create(
    model="gpt-4o",
    messages=history  # Full history required
)
```

### Mastra's Solution: Database-Backed State

```
CLIENT → Send latest only
    ↓
MASTRA → Query DB for history
    ↓
MASTRA → Assemble full context
    ↓
OPENAI API → Process with full history
    ↓
MASTRA → Save response to DB
```

**Benefits:**
- ✅ Simple client code
- ✅ Multi-provider support
- ✅ Data ownership
- ✅ Automatic history management

### Alternative APIs

**OpenAI Assistants API:**
- Server-side state (OpenAI stores threads)
- Send only `thread_id`
- Vendor lock-in

**OpenAI Responses API:**
- Advanced features (reasoning, web search)
- Still requires full history
- Mastra uses this as default

---

## Prompt Caching Explained

### OpenAI Automatic Caching

**Activation:**
- Prompt > 1,024 tokens
- Model supports caching (GPT-4o, GPT-4o mini, o1)

**Mechanics:**
- Cache unit: 128-token increments
- TTL: 5-10 minutes (up to 1 hour)
- Cost: 50% discount on cached tokens
- Latency: Up to 80% reduction

**Example:**

```
Turn 3 (1,150 tokens total):
  Cacheable: 1,024 tokens @ 50% = $0.00128
  Full price: 126 tokens @ 100% = $0.000315
  Total: $0.001595
  vs Without cache: $0.002875
  Savings: 44.5%
```

**No Configuration Needed:** Works automatically with proper structure.

### Anthropic Explicit Caching

**Configuration Required:**

```typescript
const message = {
  role: 'system',
  content: [{
    type: 'text',
    text: 'Long system prompt...',
    providerOptions: {
      anthropic: {
        cacheControl: { type: 'ephemeral' }
      }
    }
  }]
};
```

**Savings:** 90% on cached tokens (better than OpenAI)

### Mastra Compatibility

**OpenAI:** ✅ Works automatically
**Anthropic:** ✅ Via `providerOptions`
**Monitoring:** `promptCacheHitTokens`, `promptCacheMissTokens` tracked

---

## Correct Implementation Patterns

### Pattern A: Client-Managed (No Memory)

**When:** Stateless, no persistence needed

```javascript
const conversationHistory = [];

async function sendMessage(message) {
  conversationHistory.push({ role: 'user', content: message });

  const response = await fetch('/api/agent', {
    body: JSON.stringify({
      messages: conversationHistory,  // Full array
      // NO threadId, NO resourceId
    })
  });

  const result = await response.json();
  conversationHistory.push({ role: 'assistant', content: result.text });
}
```

### Pattern B: Mastra-Managed (Recommended)

**When:** Production, persistence required

```javascript
// ✅ NO client-side history array

async function sendMessage(message) {
  const response = await fetch('/api/agent', {
    body: JSON.stringify({
      messages: [{ role: 'user', content: message }],  // ✅ Latest only
      threadId: threadId,
      resourceId: resourceId
    })
  });

  const result = await response.json();

  // Display in UI (Mastra saved to DB automatically)
  displayMessage('user', message);
  displayMessage('assistant', result.text);
}

// Load history on page load
async function loadHistory() {
  const res = await fetch(`/api/memory/threads/${threadId}/messages`);
  const { messages } = await res.json();
  messages.forEach(msg => displayMessage(msg.role, msg.content));
}
```

### Anti-Pattern: Mixed (Current Implementation)

**❌ DON'T DO THIS:**

```javascript
let conversationHistory = [];  // ❌

conversationHistory.push({ role: 'user', content: message });  // ❌

fetch('/api/agent', {
  messages: conversationHistory,  // ❌ Full array
  threadId: threadId,             // ✅ Memory enabled
  resourceId: resourceId
});

conversationHistory.push({ role: 'assistant', content: response });  // ❌
```

**Problems:** Two sources of truth, redundant data, lost state on refresh

---

## Answers to Specific Questions

### Q1: "Musím posílat celou historii do Mastra API agentovi?"

**A: NE**, když používáš `threadId`.

**Správně:**
```javascript
// Pošli JEN novou zprávu
messages: [{ role: 'user', content: 'nová otázka' }]
threadId: 'conversation-abc'
```

**Mastra automaticky:**
1. Načte historii z DB podle `threadId`
2. Sestaví: `[systém, historie, nová zpráva]`
3. Pošle do OpenAI
4. Uloží odpověď zpět

### Q2: "Já definuju kolik messages má brát v potaz?"

**A: ANO, přesně!**

```typescript
memory: {
  options: {
    lastMessages: 20  // ← Načti posledních 20
  }
}
```

Nebo dynamicky:
```javascript
fetch('/api/agent', {
  messages: [{ role: 'user', content: 'otázka' }],
  threadId: 'xyz',
  memory: {
    options: { lastMessages: 10 }  // Override
  }
});
```

### Q3: "Tato odpověď [Document 1] je úplně správná?"

**A: Tvoje pochybnosti byly SPRÁVNÉ!**

- **Document 1 tvrdil:** Duplicity v DB ❌ Nepravda
- **Document 2 dokázal:** Message tracking brání duplicitám ✅
- **Realita:** Posílat jen latest je správně, ale z jiných důvodů (bandwidth, architektura)

### Q4: "Jak OpenAI API funguje? Co cachování?"

**A: OpenAI API je STATELESS**

```python
# MUSÍŠ posílat celou historii pokaždé
history = []
history.append({"role": "user", "content": "Co je 2+2?"})
response = client.chat.completions.create(messages=history)

history.append({"role": "assistant", "content": "4"})
history.append({"role": "user", "content": "A 3+3?"})
response = client.chat.completions.create(messages=history)  # Celá historie
```

**Prompt Caching (automatické):**
- Když prompt > 1024 tokenů
- 50% úspora na cachovaných tokenech
- 5-10 min TTL
- **Žádná konfigurace**

**Mastra:** Dělá tohle za tebe - ty pošleš jen latest, Mastra sestaví full history.

### Q5: "Je API které nemusím posílat historii?"

**A: ANO - Assistants API**

```python
# Server-side state (OpenAI drží historii)
thread = client.beta.threads.create()

client.beta.threads.messages.create(
    thread_id=thread.id,  # ← Jen thread ID
    content="Hello"
)

# Turn 2
client.beta.threads.messages.create(
    thread_id=thread.id,  # ← Stejný thread
    content="How are you?"  # ← Jen nová zpráva
)
```

**Ale:** Vendor lock-in (jen OpenAI)

**Mastra:** Dává ti stejnou výhodu BEZ lock-in (funguje s OpenAI, Anthropic, atd.)

### Q6: "Mám si sestavovat conversation history array?"

**A: NE, neměl bys.**

**Správně:**
```javascript
// ✅ BEZ client array
fetch('/api/agent', {
  messages: [{ role: 'user', content: 'nová zpráva' }],
  threadId: 'xyz'
});
// Mastra uloží automaticky
```

**Špatně:**
```javascript
// ❌ S client array
conversationHistory.push(...);
fetch('/api/agent', {
  messages: conversationHistory,
  threadId: 'xyz'
});
```

**Pro UI:** Načti historii z DB při page load, ne z client array.

---

## Migration Guide

### Step 1: Remove Client Array

```javascript
// REMOVE:
let conversationHistory = [];
```

### Step 2: Update sendMessage()

```javascript
// BEFORE:
conversationHistory.push({ role: 'user', content: message });
fetch('/api', { messages: conversationHistory, threadId, resourceId });
conversationHistory.push({ role: 'assistant', content: response });

// AFTER:
fetch('/api', {
  messages: [{ role: 'user', content: message }],  // ✅ Latest only
  threadId,
  resourceId
});
// No array management needed
```

### Step 3: Add History Loading

```javascript
async function loadConversationHistory() {
  try {
    const res = await fetch(`/mastra/api/memory/threads/${threadId}/messages`);
    if (res.ok) {
      const messages = await res.json();
      messages.forEach(msg => addMessage(msg.role, msg.content));
    }
  } catch (err) {
    console.error('Failed to load history:', err);
  }
}

window.addEventListener('DOMContentLoaded', loadConversationHistory);
```

### Step 4: Backend Endpoint (TODO)

```php
/**
 * @Route("/mastra/api/memory/threads/{threadId}/messages")
 */
public function getThreadMessages(string $threadId): JsonResponse
{
    // TODO: Implement Mastra Memory API client
    // $messages = $mastraClient->getMessages(['threadId' => $threadId]);

    return new JsonResponse(['messages' => []]);
}
```

---

## Production Recommendations

### 1. Memory Configuration

```typescript
const memory = new Memory({
  options: {
    lastMessages: 20,
    semanticRecall: {
      enabled: true,
      topK: 3,
      messageRange: 50,
      scope: 'resource'
    },
    workingMemory: {
      enabled: true,
      scope: 'resource'
    }
  }
});
```

### 2. Thread ID Generation

```php
protected function getOrCreateThreadId(Request $request): string
{
    $session = $request->getSession();
    $threadId = $session->get(self::SESSION_THREAD_KEY);

    if ($threadId === null) {
        $user = $this->getUser();
        $userId = $user ? $user->getUserIdentifier() : 'anonymous';

        $threadId = sprintf(
            'admin_thread_%s_%d_%s',
            $userId,
            time(),
            bin2hex(random_bytes(8))
        );

        $session->set(self::SESSION_THREAD_KEY, $threadId);
    }

    return $threadId;
}
```

### 3. Error Handling

```javascript
async function sendMessage() {
    try {
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 60000);

        const res = await fetch('/api', {
            body: JSON.stringify({
                messages: [{ role: 'user', content: message }],
                threadId,
                resourceId
            }),
            signal: controller.signal
        });

        clearTimeout(timeoutId);

        if (!res.ok) {
            throw new Error(`Server error (${res.status})`);
        }

        const json = await res.json();
        // Process response...

    } catch (err) {
        if (err.name === 'AbortError') {
            addMessage('assistant', 'Request timed out', 'error');
        } else {
            addMessage('assistant', `Error: ${err.message}`, 'error');
        }
    }
}
```

### 4. Observability

```javascript
const json = await res.json();

console.log('Mastra metrics:', {
    usage: json.usage,
    cacheEfficiency: json.usage?.promptCacheHitTokens
        ? (json.usage.promptCacheHitTokens / json.usage.inputTokens * 100).toFixed(1) + '%'
        : 'N/A'
});
```

### 5. Security

```javascript
function sanitizeMessage(message) {
    message = message.trim();

    const MAX_LENGTH = 2000;
    if (message.length > MAX_LENGTH) {
        message = message.substring(0, MAX_LENGTH);
    }

    return message;
}
```

---

## References

### Source Documents

1. `/Users/neon/shopsys/shopsys/docs/mastra-memory-and-conversation-history-guide.md`
2. `/Users/neon/shopsys/mastra_01/mastra/shopsys-docs/mastra-memory-investigation-findings.md`
3. `/Users/neon/shopsys/mastra_01/mastra/shopsys-docs/mastra-conversation-history-and-prompt-caching-deep-dive.md`

### Mastra Source Code

**Repository:** https://github.com/mastra-ai/mastra

**Key Files:**
- `packages/core/src/agent/utils/message-list/index.ts` - MessageList class
- `packages/core/src/agent/save-queue/index.ts` - Persistence logic
- `packages/core/src/stream/aisdk/v5/execute.ts` - LLM execution

### External Resources

**OpenAI:**
- Chat Completions API: https://platform.openai.com/docs/guides/chat
- Prompt Caching: https://platform.openai.com/docs/guides/prompt-caching
- Assistants API: https://platform.openai.com/docs/assistants/overview

**Vercel AI SDK:** https://ai-sdk.dev

**Mastra:** https://mastra.ai/docs

---

## Conclusion

### Summary

**Your Suspicions Were Correct:**
- Document 1's duplicate message claim was false
- But the recommendation (send only latest) remains valid
- Your current implementation needs refactoring

**Key Takeaways:**

1. **NO duplicates in database** - Mastra's tracking prevents this
2. **YES send only latest message** - For bandwidth, architecture, consistency
3. **OpenAI API is stateless** - Requires full history, Mastra handles it
4. **Prompt caching works automatically** - No configuration needed
5. **Remove client history array** - Use database as single source of truth

**Next Steps:**

1. Refactor `sql.html.twig` (remove `conversationHistory` array)
2. Implement backend endpoint for history retrieval
3. Test migration thoroughly
4. Monitor cache performance and token usage

---

**Document Version:** 1.0
**Last Updated:** November 14, 2025
**Author:** Shopsys Platform Team
**Status:** Complete Analysis
**Related Files:**
- `packages/administration/templates/content/mastra/sql.html.twig`
- `packages/framework/src/Controller/Admin/MastraController.php`
