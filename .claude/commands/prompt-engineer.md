# Prompt Engineer Command

## Description
Converts user content into a well-structured prompt using the Universal Prompt Structure Template. Analyzes session context and formats content for optimal AI interaction.

## Usage
```bash
/prompt-engineer [content]
```

## Arguments
- `content` (required): The detailed content/topic to convert into a structured prompt

## Implementation

When invoked with content, I will:

1. **Analyze the provided content** - Identify domain (technical, business, creative), complexity level, and core requirements
2. **Search current session** - Look for relevant context, documents, or previous discussions that add value
3. **Apply Universal Prompt Template** - Structure using appropriate template sections based on content analysis
4. **Output complete structured prompt** - Ready-to-use prompt following the template format

## Template Sections Applied
- **Always Include**: Task Context, Tone Context, Detailed Task Description, Immediate Request, Thinking Instructions, Output Formatting
- **Include When Relevant**: Background Data, Examples, Conversation History, Prefilled Response

## Example Usage
```bash
/prompt-engineer "Create a customer service chatbot for our e-commerce platform that handles order inquiries"
```

## Output Format
The command outputs a complete structured prompt following this pattern:

```markdown
# Structured Prompt for: [Topic]

## 1. Task Context
[AI role and purpose based on content analysis]

## 2. Tone Context
[Communication style matching user's approach]

## 3. Background Data (if relevant)
[Session context and relevant documents]

## 4. Detailed Task Description & Rules
[Specific requirements and constraints]

## 5. Examples (if applicable)
[Demonstration of expected interactions]

## 6. Immediate Task Description
<question>{{CONTENT_RESTATED}}</question>

## 7. Thinking Step by Step
[Systematic processing instructions]

## 8. Output Formatting
[Desired response structure]
```

## Benefits
- **Context-Aware**: Incorporates relevant session history and documentation
- **Tone-Matched**: Adapts to user's communication style (direct, technical, professional)
- **Complete**: Includes all necessary prompt engineering elements
- **Reusable**: Generated prompts work across different AI systems
- **Systematic**: Follows proven prompt structure template