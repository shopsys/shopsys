import React from 'react';
import { createRoot } from 'react-dom/client';
import { CopilotKit, useCopilotChatInternal, useRenderToolCall } from '@copilotkit/react-core';
import { AssistantMessage as CopilotAssistantMessage, CopilotChat } from '@copilotkit/react-ui';
import '@copilotkit/react-ui/styles.css';

const rootElement = document.getElementById('mastra-sql-copilot-root');
const h = React.createElement;

const EXECUTION_CUES_PATTERN = /\b(approve|approval|execute|run|confirm)\b/i;

const toTextContent = (content) => {
    if (typeof content === 'string') {
        return content;
    }

    if (!Array.isArray(content)) {
        return '';
    }

    return content
        .map((part) => {
            if (typeof part === 'string') {
                return part;
            }

            if (part?.type === 'text' && typeof part.text === 'string') {
                return part.text;
            }

            return '';
        })
        .join('\n')
        .trim();
};

const extractSqlBlock = (messageContent) => {
    if (typeof messageContent !== 'string') {
        return null;
    }

    const sqlMatch = messageContent.match(/```sql\s*([\s\S]*?)```/i);

    return sqlMatch?.[1]?.trim() || null;
};

const buildUserMessage = (content) => {
    const hasRandomUuid = typeof crypto !== 'undefined' && typeof crypto.randomUUID === 'function';

    return {
        id: hasRandomUuid ? crypto.randomUUID() : `manual-${Date.now()}`,
        role: 'user',
        content,
    };
};

const formatJson = (value) => JSON.stringify(value, null, 2);

const formatMaybeJson = (value) => {
    if (typeof value !== 'string') {
        return formatJson(value);
    }

    try {
        return formatJson(JSON.parse(value));
    } catch {
        return value;
    }
};

const ToolCallBlock = ({ name, status, args, result }) => {
    const formattedArgs = formatMaybeJson(args || {});
    const hasResult = result !== undefined;
    const stateLabel = status ? ` (${status})` : '';

    const payload = {};
    if (args && Object.keys(args).length > 0) {
        payload.input = args;
    }
    if (result !== undefined) {
        payload.output = result;
    }

    return h(
        'div',
        { className: 'tool-call-wrapper' },
        h(
            'details',
            { className: 'tool-call' },
            h('summary', null, `${name}${stateLabel}`),
            h(
                'div',
                { className: 'tool-call-body' },
                h(
                    'pre',
                    { className: 'tool-call-json' },
                    h('code', null, hasResult ? formatJson(payload) : formattedArgs),
                ),
            ),
        ),
    );
};

const ToolRenderers = () => {
    useRenderToolCall({
        name: '*',
        description: 'Render all tool calls from Mastra runtime.',
        parameters: [],
        render: ({ name, status, args, result }) => h(ToolCallBlock, { name, status, args, result }),
    });

    return null;
};

const ClassicInput = ({ inProgress, onSend, chatReady }) => {
    const [text, setText] = React.useState('');
    const canSend = chatReady !== false && !inProgress && text.trim().length > 0;

    const submit = React.useCallback(async () => {
        const value = text.trim();
        if (value.length === 0 || !canSend) {
            return;
        }

        await onSend(value);
        setText('');
    }, [canSend, onSend, text]);

    const onKeyDown = React.useCallback((event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            submit();
        }
    }, [submit]);

    return h(
        'div',
        { className: 'mastra-sql-classic-input' },
        h(
            'label',
            { className: 'form-label', htmlFor: 'mastra-sql-chat-input' },
            'Your question',
        ),
        h('input', {
            id: 'mastra-sql-chat-input',
            className: 'form-control',
            type: 'text',
            value: text,
            placeholder: 'Ask about products, orders, customers...',
            disabled: inProgress || chatReady === false,
            onInput: (event) => setText(event.target.value),
            onKeyDown,
        }),
        h(
            'button',
            {
                type: 'button',
                className: 'btn btn-primary',
                disabled: !canSend,
                onClick: submit,
            },
            inProgress ? 'Sending...' : 'Send',
        ),
    );
};

const AssistantMessageWithActions = (props) => {
    const { sendMessage } = useCopilotChatInternal();
    const [isSubmitting, setIsSubmitting] = React.useState(false);
    const messageContent = toTextContent(props.message?.content);
    const sqlBlock = React.useMemo(
        () => extractSqlBlock(messageContent),
        [messageContent],
    );

    const canShowActions = Boolean(
        props.isCurrentMessage
        && sqlBlock
        && EXECUTION_CUES_PATTERN.test(messageContent)
        && !props.isGenerating
        && !isSubmitting,
    );

    const sendQuickReply = React.useCallback(
        async (content) => {
            if (isSubmitting) {
                return;
            }

            setIsSubmitting(true);

            try {
                await sendMessage(buildUserMessage(content));
            } finally {
                setIsSubmitting(false);
            }
        },
        [isSubmitting, sendMessage],
    );

    return h(
        React.Fragment,
        null,
        h(CopilotAssistantMessage, props),
        canShowActions
            ? h(
                'div',
                { className: 'sql-approval-buttons' },
                h(
                    'button',
                    {
                        type: 'button',
                        className: 'btn btn-success btn-sm me-2',
                        onClick: () => sendQuickReply('Execute this SQL query as shown.'),
                    },
                    'Execute',
                ),
                h(
                    'button',
                    {
                        type: 'button',
                        className: 'btn btn-secondary btn-sm',
                        onClick: () => sendQuickReply('Do not execute this SQL query. Please revise it.'),
                    },
                    'Cancel',
                ),
            )
            : null,
    );
};

if (rootElement !== null) {
    const runtimeUrl = rootElement.dataset.runtimeUrl || '/mastra/chat';
    const agent = rootElement.dataset.agent || 'sqlAgent';
    const threadId = rootElement.dataset.threadId || undefined;
    const resourceId = rootElement.dataset.resourceId || undefined;

    const app = h(
        CopilotKit,
        {
            runtimeUrl,
            agent,
            threadId,
            properties: resourceId !== undefined ? { resourceId } : undefined,
        },
        h(
            React.Fragment,
            null,
            h(ToolRenderers),
            h(CopilotChat, {
                className: 'mastra-sql-copilot-chat',
                AssistantMessage: AssistantMessageWithActions,
                Input: ClassicInput,
                suggestions: 'manual',
                labels: {
                    title: '',
                    initial: [
                        'Welcome to SQL Assistant',
                        'Ask questions about your Shopsys database in natural language.',
                        'Examples:',
                        '- Show me top 10 best-selling products',
                        '- How many orders were placed last month?',
                        '- List categories with the most products',
                        '- What are the most popular brands?',
                        'All queries will be shown for review before execution.',
                    ].join('\n'),
                },
            }),
        ),
    );

    createRoot(rootElement).render(app);
}
