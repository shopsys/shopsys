# Mastra Memory and Conversation History — Complete Guide

This document provides comprehensive guidance on properly using Mastra's memory system versus client-side conversation history management, based on research of Mastra documentation, GitHub issues, and real-world usage patterns.

---

## Table of Contents

1. [Executive Summary](#executive-summary)
2. [The Duplicate Message Problem](#the-duplicate-message-problem)
3. [Two Distinct Patterns](#two-distinct-patterns)
4. [Understanding Mastra's Memory System](#understanding-mastras-memory-system)
5. [threadId and resourceId Explained](#threadid-and-resourceid-explained)
6. [Correct Implementation Patterns](#correct-implementation-patterns)
7. [Fixing Our Current Implementation](#fixing-our-current-implementation)
8. [API Usage Patterns](#api-usage-patterns)
9. [Best Practices](#best-practices)
10. [References](#references)

---

## Executive Summary

### Key Findings

**Question**: Do we pass full conversation history to Mastra agents?

**Answer**: It depends on whether you're using Mastra's memory system:
- ✅ **With Memory (Recommended)**: Send ONLY the latest message
- ❌ **Without Memory (Not Recommended)**: Send full conversation history array

**Critical Issue**: Our current implementation **mixes both patterns** and will cause **duplicate messages**.

### What We're Doing Wrong

```javascript
// ❌ WRONG - Mixing memory with full history
const res = await fetch('/mastra/api/agents/weatherAgent/generate', {
    body: JSON.stringify({
        messages: conversationHistory,  // Full array
        threadId: threadId,             // Memory enabled
        resourceId: resourceId          // Memory enabled
    })
});
```

**Problem**: Mastra loads history from database (via threadId) AND we're sending full history → duplicates!

### What We Should Do

```javascript
// ✅ CORRECT - Use memory, send only latest message
const res = await fetch('/mastra/api/agents/weatherAgent/generate', {
    body: JSON.stringify({
        messages: [{ role: 'user', content: message }],  // Latest only
        memory: {
            thread: threadId,
            resource: resourceId
        }
    })
});
```

---

## The Duplicate Message Problem

### How Duplicates Happen

When you enable Mastra memory (threadId + resourceId) but also send full conversation history:

```
┌──────────────────────────────────────────────────────────────┐
│ Step 1: Client Sends Request                                 │
├──────────────────────────────────────────────────────────────┤
│ messages: [                                                  │
│   { role: "user", content: "Hello" },           // Message A │
│   { role: "assistant", content: "Hi!" },        // Message B │
│   { role: "user", content: "How are you?" }     // Message C │
│ ]                                                            │
│ threadId: "thread-123"                                       │
│ resourceId: "user-456"                                       │
└──────────────────────────────────────────────────────────────┘
                            ↓
┌──────────────────────────────────────────────────────────────┐
│ Step 2: Mastra Receives Request                              │
├──────────────────────────────────────────────────────────────┤
│ 1. Sees threadId → loads from database:                     │
│    [Message A, Message B]                                    │
│                                                              │
│ 2. Receives from client:                                     │
│    [Message A, Message B, Message C]                         │
│                                                              │
│ 3. Combines both sources:                                    │
│    [A (db), B (db), A (client), B (client), C (client)]     │
│                                                              │
│ Result: DUPLICATES! ❌                                       │
└──────────────────────────────────────────────────────────────┘
```

### Real-World Impact

**Symptoms**:
- Agent sees duplicate messages in context
- Responses become confused or repetitive
- Context window fills up faster (wasted tokens)
- Poor quality conversations

**Example**:
```
User: "What's the weather in London?"
Agent: "London is sunny, 18°C"

User: "And Paris?"
Agent: "You already asked about London (sunny, 18°C).
       You already asked about London (sunny, 18°C).  ← Duplicate!
       Paris is cloudy, 15°C."
```

---

## Two Distinct Patterns

### Pattern A: Client-Side History (No Memory)

**Use Case**: Simple, stateless interactions where persistence isn't needed.

**Implementation**:
```javascript
// Client manages full conversation history
let conversationHistory = [];

async function sendMessage(message) {
    // Add to local array
    conversationHistory.push({ role: 'user', content: message });

    // Send full history
    const res = await fetch('/mastra/api/agents/weatherAgent/generate', {
        method: 'POST',
        body: JSON.stringify({
            messages: conversationHistory  // ← Full array
            // No threadId or resourceId
        })
    });

    const response = await res.json();
    conversationHistory.push({ role: 'assistant', content: response.text });
}
```

**Characteristics**:
- ✅ Simple to understand
- ✅ Full control over history
- ❌ Lost on page refresh
- ❌ No persistence across sessions
- ❌ No semantic search
- ❌ No cross-thread memory
- ❌ Client manages all state

---

### Pattern B: Mastra Memory (Recommended)

**Use Case**: Production applications requiring conversation persistence, semantic search, and cross-session context.

**Implementation**:
```javascript
// NO conversation history array needed!

async function sendMessage(message) {
    // Send ONLY the latest message
    const res = await fetch('/mastra/api/agents/weatherAgent/generate', {
        method: 'POST',
        body: JSON.stringify({
            messages: [{ role: 'user', content: message }],  // ← Latest only
            memory: {
                thread: threadId,    // Conversation ID
                resource: resourceId  // User/entity ID
            }
        })
    });

    const response = await res.json();
    // Mastra automatically saves to database - no client storage needed!
}
```

**Characteristics**:
- ✅ Automatic persistence to database
- ✅ Works across page refreshes
- ✅ Works across sessions
- ✅ Semantic search enabled
- ✅ Cross-thread memory (resource-scoped)
- ✅ Simpler client code
- ✅ Server manages state

---

## Understanding Mastra's Memory System

### Three Types of Memory

Mastra's memory system provides three distinct capabilities:

#### 1. Conversation History (Thread-Scoped)

**What it does**: Automatically loads recent messages from the current conversation thread.

**Configuration**:
```typescript
// Mastra agent configuration
export const weatherAgent = new Agent({
    name: 'weatherAgent',
    memory: new Memory({
        options: {
            lastMessages: 10,  // Load last 10 messages (default)
        }
    }),
    // ...
});
```

**Example**:
```
┌─────────────────────────────────────────────────────┐
│ Thread: "support-ticket-123"                        │
├─────────────────────────────────────────────────────┤
│ Stored in database:                                 │
│ [A, B, C, D, E, F, G, H, I, J, K, L, M]            │
│                                                     │
│ When agent.generate() called with threadId:         │
│ Mastra loads: [D, E, F, G, H, I, J, K, L, M]       │
│               └─── last 10 messages ───┘           │
└─────────────────────────────────────────────────────┘
```

---

#### 2. Semantic Recall (Intelligent Context)

**What it does**: Uses vector search to find semantically relevant past messages, even from earlier in the conversation.

**Configuration**:
```typescript
export const weatherAgent = new Agent({
    name: 'weatherAgent',
    memory: new Memory({
        options: {
            lastMessages: 5,
            semanticRecall: {
                enabled: true,
                topK: 3,           // Retrieve 3 most relevant messages
                messageRange: 5,    // From last 5 messages
            }
        }
    }),
    // ...
});
```

**Example**:
```
Database conversation history (100 messages):
[msg1, msg2, ..., msg50: "I love Italian food", ..., msg100]

User asks: "What's my favorite cuisine?"

Semantic search finds: msg50 ("I love Italian food")
Even though it's 50 messages ago!

Agent context:
- Last 5 messages (recent history)
- msg50 (semantically relevant)
```

---

#### 3. Working Memory (Resource-Scoped)

**What it does**: Maintains persistent facts and preferences about a user/entity across ALL their conversation threads.

**Configuration**:
```typescript
export const weatherAgent = new Agent({
    name: 'weatherAgent',
    memory: new Memory({
        options: {
            workingMemory: {
                enabled: true,
                template: `
# User Profile
## Preferences
- Temperature unit: Celsius
- Favorite cities: London, Paris

## Context
- Last accessed: {date}
                `
            }
        }
    }),
    // ...
});
```

**Example**:
```
┌──────────────────────────────────────────────────────┐
│ Resource: "user_alice"                               │
├──────────────────────────────────────────────────────┤
│ Thread 1 (Shopping):                                 │
│   User: "I prefer Celsius"                           │
│   → Saved to resource working memory                 │
├──────────────────────────────────────────────────────┤
│ Thread 2 (Support) - DIFFERENT conversation:         │
│   User: "What's the weather?"                        │
│   Agent: "In London, it's 18°C" ← Remembers Celsius! │
│   (Even though this is a different thread)           │
└──────────────────────────────────────────────────────┘
```

### How Memory Scoping Works

```
resourceId: "user_alice"
├── threadId: "shopping_2025_01"
│   ├── Message 1: "Show me shoes"
│   ├── Message 2: "Size 8 please"
│   └── Message 3: "I prefer Celsius"  → Saved to resource working memory
│
├── threadId: "support_ticket_456"
│   ├── Message 1: "I need help"
│   └── Message 2: "What's the weather?" → Agent knows user prefers Celsius
│
└── Working Memory (shared across threads):
    - Preferred temperature: Celsius
    - Shoe size: 8
    - Last active: 2025-01-12
```

---

## threadId and resourceId Explained

### threadId

**Purpose**: Identifies a specific conversation or session.

**Characteristics**:
- Must be **globally unique** across all resources
- Represents a **single conversation thread**
- Isolates conversation history
- Changes when starting a new conversation

**Examples**:
```typescript
// Good examples
threadId: "support_ticket_123"
threadId: "chat_session_456"
threadId: "admin_thread_superadmin_a3f2b8c1"
threadId: "user_789_conversation_2025_01_12"

// Bad examples
threadId: "thread1"  // ❌ Too generic, might collide
threadId: "123"      // ❌ Not descriptive, might collide
```

**Generation Pattern** (from our implementation):
```php
// PHP Controller
protected function getOrCreateThreadId(Request $request): string
{
    $session = $request->getSession();
    $threadId = $session->get(self::SESSION_THREAD_KEY);

    if ($threadId === null) {
        $user = $this->getUser();
        $userId = $user ? $user->getUserIdentifier() : 'anonymous';
        // Format: admin_thread_{userId}_{randomHex}
        $threadId = sprintf('admin_thread_%s_%s', $userId, bin2hex(random_bytes(8)));
        $session->set(self::SESSION_THREAD_KEY, $threadId);
    }

    return $threadId;
}
```

---

### resourceId

**Purpose**: Identifies the user or entity that owns conversation threads.

**Characteristics**:
- Represents a **user, customer, or organizational entity**
- **Constant across all conversations** for the same user
- Enables resource-scoped working memory
- Groups all threads for a user

**Examples**:
```typescript
// Good examples
resourceId: "user_123"
resourceId: "org_456"
resourceId: "customer_789"
resourceId: "admin_resource_superadmin"

// Bad examples
resourceId: "session123"     // ❌ Should be user, not session
resourceId: "thread456"      // ❌ Confusing with threadId
resourceId: "anonymous"      // ⚠️ OK for testing, but limits memory features
```

**Generation Pattern** (from our implementation):
```php
// PHP Controller
protected function getResourceId(): string
{
    $user = $this->getUser();
    $userId = $user ? $user->getUserIdentifier() : 'anonymous';

    // Format: admin_resource_{userId}
    return sprintf('admin_resource_%s', $userId);
}
```

---

### How They Work Together

```typescript
// User "alice" starts a support conversation
await agent.generate("I need help", {
    memory: {
        thread: "support_ticket_001",  // This specific support case
        resource: "user_alice"         // Alice as a user
    }
});

// Later, alice starts a shopping conversation
await agent.generate("Show me products", {
    memory: {
        thread: "shopping_session_789",  // Different conversation
        resource: "user_alice"           // Same user
    }
});

// With resource-scoped working memory:
// - Both threads can access alice's preferences
// - Each thread has isolated conversation history
// - Semantic search works across both threads for alice
```

### Memory Scoping Table

| Scope | What It Controls | Example |
|-------|------------------|---------|
| **Thread-scoped** | Conversation history for specific thread | Messages in "support_ticket_001" |
| **Resource-scoped** | Working memory across all user's threads | User preferences, facts |
| **Global** | Semantic search across resource's threads | Finding relevant past conversations |

---

## Correct Implementation Patterns

### Pattern 1: Using Mastra Memory (Production)

**Server-side API Route**:
```typescript
// app/api/chat/route.ts
import { mastra } from '@/mastra';

export async function POST(req: Request) {
    const { message, threadId, resourceId } = await req.json();

    const agent = mastra.getAgent('weatherAgent');

    // Send ONLY the latest message
    const stream = await agent.stream(
        [{ role: 'user', content: message }],  // ← Single message
        {
            memory: {
                thread: threadId,
                resource: resourceId
            }
        }
    );

    return stream.toDataStreamResponse();
}
```

**Client-side**:
```javascript
// Generate unique IDs once
const threadId = `thread_${userId}_${Date.now()}`;
const resourceId = `user_${userId}`;

async function sendMessage(message) {
    const res = await fetch('/api/chat', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            message: message,  // Single message
            threadId: threadId,
            resourceId: resourceId
        })
    });

    // Handle response
}
```

---

### Pattern 2: Using MastraClient SDK

**Client-side with SDK**:
```typescript
import { MastraClient } from '@mastra/client-js';

const client = new MastraClient({
    baseUrl: process.env.MASTRA_API_URL || 'http://localhost:4111'
});

async function sendMessage(message: string) {
    const agent = client.getAgent('weatherAgent');

    const stream = await agent.stream({
        messages: [{ role: 'user', content: message }],  // Latest only
        memory: {
            thread: threadId,
            resource: resourceId
        }
    });

    stream.processDataStream({
        onTextPart: (text) => {
            console.log(text);
        }
    });
}
```

---

### Pattern 3: With AI SDK v5 (useChat Hook)

**Problem**: `useChat` sends full message history by default.

**Solution**: Use `experimental_prepareRequestBody` to extract only the latest message.

**Client-side**:
```typescript
import { useChat } from '@ai-sdk/react';

const { messages, input, handleInputChange, handleSubmit } = useChat({
    api: '/api/chat',
    experimental_prepareRequestBody: (request) => {
        // Extract only the last message
        const lastMessage = request.messages.at(-1);

        return {
            message: lastMessage,
            threadId: threadId,
            resourceId: resourceId,
        };
    },
});
```

**Server-side**:
```typescript
export async function POST(req: Request) {
    const { message, threadId, resourceId } = await req.json();

    const agent = mastra.getAgent('weatherAgent');

    const stream = await agent.stream(
        [message],  // Single message from client
        {
            memory: {
                thread: threadId,
                resource: resourceId
            },
            format: 'aisdk'  // AI SDK compatible format
        }
    );

    return stream.toUIMessageStreamResponse();
}
```

---

### Pattern 4: Loading History on Page Load

If you want to display previous messages when the page loads:

**Client-side**:
```javascript
async function loadConversationHistory() {
    try {
        // Option 1: Use Mastra's memory API
        const res = await fetch(`/mastra/api/memory/threads/${threadId}/messages`);
        if (res.ok) {
            const messages = await res.json();
            messages.forEach(msg => {
                displayMessage(msg.role, msg.content);
            });
        }
    } catch (err) {
        console.error('Failed to load history:', err);
    }
}

// Call on page load
window.addEventListener('DOMContentLoaded', loadConversationHistory);
```

**Alternative using Storage API**:
```typescript
// Using MastraClient
const client = new MastraClient({ baseUrl: '/mastra/api' });

async function loadHistory() {
    const messages = await client.storage.getMessages({ threadId });
    messages.forEach(msg => displayMessage(msg.role, msg.content));
}
```

---

## Fixing Our Current Implementation

### Current Implementation Issues

**File**: `packages/administration/templates/content/mastra/index.html.twig`

**Problems**:
1. ❌ Maintains `conversationHistory` array on client
2. ❌ Sends full history to Mastra
3. ❌ Also sends `threadId` and `resourceId` (memory enabled)
4. ❌ Will cause duplicate messages

**Current code** (lines 743-791):
```javascript
let conversationHistory = [];  // ❌ Don't need this

async function sendMessage() {
    const message = input.value.trim();

    addMessage('user', message);
    conversationHistory.push({ role: 'user', content: message });  // ❌

    const res = await fetch('/mastra/api/agents/weatherAgent/generate', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            messages: conversationHistory,  // ❌ Full history
            threadId: threadId,             // ✅ Memory enabled
            resourceId: resourceId          // ✅ Memory enabled
        })
    });

    const json = await res.json();
    addMessage('assistant', json.text);
    conversationHistory.push({ role: 'assistant', content: json.text });  // ❌
}
```

---

### Fixed Implementation

**File**: `packages/administration/templates/content/mastra/index.html.twig`

Replace the entire `<script>` section with:

```twig
<script>
    (function () {
        const sendBtn = document.getElementById('send-btn');
        const input = document.getElementById('message-input');
        const messagesList = document.getElementById('messages-list');
        const chatContainer = document.getElementById('chat-container');
        const threadId = '{{ threadId|e('js') }}';
        const resourceId = '{{ resourceId|e('js') }}';

        // ✅ NO conversationHistory array needed!

        function addMessage(role, content, className = '') {
            const div = document.createElement('div');
            div.className = `message message-${className || role}`;
            div.textContent = content;
            messagesList.appendChild(div);
            chatContainer.scrollTop = chatContainer.scrollHeight;
            return div;
        }

        async function sendMessage() {
            const message = input.value.trim();
            if (!message) return;

            input.value = '';
            sendBtn.disabled = true;

            // Add to UI only (no client history management)
            addMessage('user', message);

            const loadingEl = addMessage('assistant', '{{ 'Loading...'|trans }}', 'loading');

            try {
                const res = await fetch('/mastra/api/agents/weatherAgent/generate', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        // ✅ Send ONLY the latest message
                        messages: [{ role: 'user', content: message }],
                        // ✅ Use modern memory API
                        memory: {
                            thread: threadId,
                            resource: resourceId
                        }
                    })
                });

                loadingEl.remove();

                if (!res.ok) {
                    const text = await res.text().catch(() => '');
                    throw new Error(`HTTP ${res.status}: ${text}`);
                }

                const json = await res.json();
                const responseText = json?.text ?? JSON.stringify(json, null, 2);

                // Add to UI only (Mastra saves to DB automatically)
                addMessage('assistant', responseText);

            } catch (err) {
                loadingEl.remove();
                const errorMessage = '{{ 'Error'|trans }}: ' + (err?.message || err);
                addMessage('assistant', errorMessage, 'error');
            } finally {
                sendBtn.disabled = false;
                input.focus();
            }
        }

        sendBtn.addEventListener('click', sendMessage);
        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') sendMessage();
        });

        // ✅ Optional: Load conversation history on page load
        async function loadConversationHistory() {
            try {
                const res = await fetch(`/mastra/api/memory/threads/${threadId}/messages`);
                if (res.ok) {
                    const messages = await res.json();
                    if (messages && messages.length > 0) {
                        // Clear welcome message
                        messagesList.innerHTML = '';
                        // Display historical messages
                        messages.forEach(msg => {
                            addMessage(msg.role, msg.content);
                        });
                    }
                }
            } catch (err) {
                console.error('Failed to load conversation history:', err);
            }
        }

        // Load history when page loads
        loadConversationHistory();
    })();
</script>
```

---

### Changes Summary

| What Changed | Before | After | Reason |
|--------------|--------|-------|--------|
| **conversationHistory array** | Maintained on client | Removed | Mastra manages in DB |
| **messages parameter** | Full array | `[latestMessage]` | Prevent duplicates |
| **Memory API format** | `threadId`, `resourceId` | `memory: { thread, resource }` | Modern API |
| **History persistence** | Client-side state | Database | Survives refresh |
| **On page load** | Empty state | Load from DB | Show previous messages |

---

### Verification Steps

After implementing the fix:

1. **Clear Symfony cache**:
```bash
mutagen-compose exec php-fpm php bin/console cache:clear
```

2. **Test conversation flow**:
   - Navigate to http://127.0.0.1:8000/admin/superadmin/mastra/dashboard
   - Send: "What's the weather in London?"
   - Agent responds with weather data
   - Send: "And in Paris?" (no city context provided)
   - Agent should remember you're asking about weather

3. **Test persistence**:
   - Have a conversation (3-4 messages)
   - Refresh the page
   - Previous messages should load from database
   - Continue conversation - context preserved

4. **Test new conversation**:
   - Click "New Conversation" button
   - New threadId generated
   - Previous conversation not visible
   - Start fresh conversation

5. **Check database**:
```bash
# If using LibSQL
sqlite3 mastra-service/.mastra/mastra.db "SELECT * FROM messages;"

# If using PostgreSQL
docker exec shopsys-framework-postgres psql -U root -d shopsys -c "SELECT * FROM mastra.messages;"
```

---

## API Usage Patterns

### Pattern Comparison Table

| Scenario | Pattern | Messages Sent | Memory Config | Behavior |
|----------|---------|---------------|---------------|----------|
| **No persistence needed** | Client history | Full array | None | Lost on refresh |
| **Production chat** | Mastra memory | Latest only | `{ thread, resource }` | Persisted, semantic search |
| **useChat (AI SDK)** | Extract latest | Latest only | `{ thread, resource }` | Framework compatible |
| **Multi-agent** | Mastra memory | Latest only | Shared `resource` | Cross-agent memory |

---

### Deprecated vs Modern API

**Deprecated** (still works but not recommended):
```javascript
fetch('/mastra/api/agents/weatherAgent/generate', {
    body: JSON.stringify({
        messages: [{ role: 'user', content: 'Hello' }],
        threadId: 'thread-123',      // ⚠️ Deprecated top-level
        resourceId: 'user-456'        // ⚠️ Deprecated top-level
    })
});
```

**Modern** (recommended):
```javascript
fetch('/mastra/api/agents/weatherAgent/generate', {
    body: JSON.stringify({
        messages: [{ role: 'user', content: 'Hello' }],
        memory: {
            thread: 'thread-123',     // ✅ Modern nested structure
            resource: 'user-456'      // ✅ Modern nested structure
        }
    })
});
```

---

### API Parameter Reference

**Agent.generate() / Agent.stream() Parameters**:

```typescript
interface AgentCallOptions {
    // Messages to send
    messages: string | CoreMessage[];

    // Memory configuration (modern API)
    memory?: {
        thread: string;              // Required: conversation identifier
        resource: string;            // Required: user/entity identifier
        options?: {
            lastMessages?: number;   // How many recent messages to load
            semanticRecall?: {
                enabled: boolean;
                topK: number;        // Top K similar messages
                messageRange: number;
            };
        };
    };

    // Deprecated (still works)
    threadId?: string;               // ⚠️ Use memory.thread instead
    resourceId?: string;             // ⚠️ Use memory.resource instead

    // Other options
    format?: 'mastra' | 'aisdk';
    maxSteps?: number;
    modelSettings?: {
        temperature?: number;
        maxTokens?: number;
    };
}
```

---

## Best Practices

### 1. Always Use Memory in Production

```typescript
// ❌ Bad: No persistence
await agent.generate(messages);

// ✅ Good: Persistent conversations
await agent.generate(messages, {
    memory: {
        thread: threadId,
        resource: resourceId
    }
});
```

---

### 2. Generate Unique Thread IDs

```typescript
// ❌ Bad: Collisions likely
const threadId = "thread1";

// ✅ Good: Unique per conversation
const threadId = `thread_${userId}_${Date.now()}_${randomId}`;

// ✅ Better: Use UUIDs
import { v4 as uuidv4 } from 'uuid';
const threadId = `thread_${userId}_${uuidv4()}`;
```

---

### 3. Keep Resource IDs Constant

```typescript
// ❌ Bad: Changes per session
const resourceId = `session_${sessionId}`;

// ✅ Good: Constant per user
const resourceId = `user_${userId}`;
```

---

### 4. Client State for UI, Memory for Persistence

```typescript
// UI state (React/Vue/etc.)
const [messages, setMessages] = useState([]);

// Add message optimistically for instant UI
setMessages([...messages, userMessage]);

// Send to Mastra (which persists to DB)
await agent.generate([userMessage], {
    memory: { thread, resource }
});

// On error, remove optimistic message
```

---

### 5. Load History on Page Mount

```typescript
// React example
useEffect(() => {
    async function loadHistory() {
        const history = await client.storage.getMessages({ threadId });
        setMessages(history);
    }
    loadHistory();
}, [threadId]);
```

---

### 6. Handle New Conversations Properly

```typescript
// Clear old thread, generate new one
function startNewConversation() {
    // Generate fresh thread ID
    const newThreadId = `thread_${userId}_${Date.now()}`;

    // Keep same resource ID (same user)
    const resourceId = `user_${userId}`;

    // Clear UI
    setMessages([]);

    // Update state
    setThreadId(newThreadId);
}
```

---

### 7. Configure Memory Appropriately

```typescript
// Development: Start simple
new Memory({
    options: {
        lastMessages: 10  // Just basic history
    }
})

// Production: Enable all features
new Memory({
    options: {
        lastMessages: 20,
        semanticRecall: {
            enabled: true,
            topK: 5,
            messageRange: 10
        },
        workingMemory: {
            enabled: true,
            template: `User profile and preferences...`
        }
    }
})
```

---

### 8. Error Handling

```typescript
async function sendMessage(message: string) {
    try {
        const result = await agent.generate([{ role: 'user', content: message }], {
            memory: {
                thread: threadId,
                resource: resourceId
            }
        });

        return result.text;
    } catch (error) {
        // Check for specific memory errors
        if (error.message.includes('resourceId')) {
            console.error('Missing resourceId - memory disabled');
            // Fallback to no-memory mode
        } else if (error.message.includes('threadId')) {
            console.error('Missing threadId - memory disabled');
        } else {
            console.error('Agent error:', error);
        }

        throw error;
    }
}
```

---

### 9. Monitor Database Growth

```typescript
// Periodic cleanup of old threads
async function cleanupOldThreads() {
    const cutoffDate = new Date();
    cutoffDate.setDate(cutoffDate.getDate() - 90); // 90 days ago

    await storage.deleteThreadsOlderThan(cutoffDate);
}
```

---

### 10. Testing Memory System

```typescript
// Test file
describe('Mastra Memory', () => {
    it('should persist messages across calls', async () => {
        const threadId = 'test-thread-1';
        const resourceId = 'test-user-1';

        // First message
        await agent.generate('Hello', {
            memory: { thread: threadId, resource: resourceId }
        });

        // Second message - should have context
        const response = await agent.generate('What did I just say?', {
            memory: { thread: threadId, resource: resourceId }
        });

        expect(response.text).toContain('Hello');
    });

    it('should isolate different threads', async () => {
        const resourceId = 'test-user-1';

        await agent.generate('Secret message', {
            memory: { thread: 'thread-1', resource: resourceId }
        });

        const response = await agent.generate('What did I say?', {
            memory: { thread: 'thread-2', resource: resourceId }
        });

        // Should NOT know about other thread
        expect(response.text).not.toContain('Secret message');
    });
});
```

---

## References

### Official Documentation

- **Mastra Memory Overview**: https://mastra.ai/docs/memory/overview
- **Threads and Resources**: https://mastra.ai/docs/memory/threads-and-resources
- **Conversation History**: https://mastra.ai/docs/memory/conversation-history
- **Working Memory**: https://mastra.ai/docs/memory/working-memory
- **Semantic Recall**: https://mastra.ai/docs/memory/semantic-recall
- **Agent Memory**: https://mastra.ai/docs/agents/agent-memory
- **Storage Reference**: https://mastra.ai/reference/storage/libsql

### API Reference

- **Agent.generate()**: https://mastra.ai/reference/agents/generate
- **Agent.stream()**: https://mastra.ai/reference/agents/stream
- **Memory Class**: https://mastra.ai/reference/memory/memory-class
- **Storage API**: https://mastra.ai/reference/storage/postgresql

### Examples and Guides

- **AI SDK v5 Integration**: https://mastra.ai/examples/agents/ai-sdk-v5-integration
- **Memory Todo Agent**: https://github.com/mastra-ai/mastra/tree/main/examples/memory-todo-agent
- **Weather Agent Example**: https://github.com/mastra-ai/mastra/tree/main/examples/weather-agent

### GitHub Issues

- **Duplicate Messages Bug**: https://github.com/mastra-ai/mastra/issues/9370
- **Memory Configuration**: https://github.com/mastra-ai/mastra/discussions

### Related Documentation

- **Shopsys Integration Plan**: `docs/mastra-integration-plan.md`
- **Shopsys Package-First Architecture**: `CLAUDE.md`
- **Admin Controller**: `packages/framework/src/Controller/Admin/MastraController.php`
- **Admin Template**: `packages/administration/templates/content/mastra/index.html.twig`

---

## Quick Reference Card

### ✅ DO

```javascript
// Send only latest message
messages: [{ role: 'user', content: message }]

// Use modern memory API
memory: {
    thread: threadId,
    resource: resourceId
}

// Generate unique thread IDs
const threadId = `thread_${userId}_${Date.now()}`;

// Keep resource ID constant per user
const resourceId = `user_${userId}`;

// Load history on page mount
await client.storage.getMessages({ threadId });
```

### ❌ DON'T

```javascript
// Don't send full history with memory enabled
messages: conversationHistory,  // ❌
memory: { thread, resource }    // ❌ Conflict!

// Don't use deprecated API
threadId: '...',                // ⚠️ Deprecated
resourceId: '...'               // ⚠️ Deprecated

// Don't maintain client-side history when using memory
conversationHistory.push(...)   // ❌ Unnecessary

// Don't use generic IDs
threadId: 'thread1'             // ❌ Collision risk
resourceId: 'session123'        // ❌ Should be user, not session
```

---

## Glossary

**threadId**: Unique identifier for a specific conversation thread. Changes when starting a new conversation.

**resourceId**: Identifier for the user or entity owning threads. Constant across all conversations for the same user.

**Conversation History**: Recent messages from the current thread automatically loaded by Mastra memory.

**Semantic Recall**: Vector search to find relevant past messages based on semantic similarity.

**Working Memory**: Persistent facts and preferences about a user/entity, shared across all their threads.

**Thread-scoped**: Data isolated to a specific conversation thread.

**Resource-scoped**: Data shared across all threads for a user/entity.

**Memory API**: Mastra's system for managing conversation persistence, history, and context.

**Duplicate Messages**: Bug that occurs when sending full history while memory is enabled, causing messages to appear twice in agent context.

---

**Document Version**: 1.0
**Last Updated**: 2025-01-12
**Author**: Shopsys Platform Team
**Related**: `docs/mastra-integration-plan.md`
