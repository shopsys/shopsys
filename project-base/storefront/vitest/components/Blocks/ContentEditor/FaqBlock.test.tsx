import { render } from '@testing-library/react';
import { ContentRenderer } from 'components/Blocks/ContentEditor/ContentRenderer';
import { TIDs } from 'cypress/tids';
import { describe, expect, test } from 'vitest';

describe('FaqBlock', () => {
    test('renders the question in the summary and the answer as html', () => {
        const { container } = render(
            <ContentRenderer
                areProductsFetching={false}
                products={[]}
                document={{
                    blocks: [
                        {
                            id: 'faq-1',
                            type: 'faq',
                            data: {
                                question: 'How long is the warranty?',
                                answer: '<p>Two <strong>years</strong>.</p>',
                            },
                        },
                    ],
                }}
            />,
        );

        const faq = container.querySelector(`[data-tid="${TIDs.content_block_faq}"]`);

        expect(faq?.tagName).toBe('DETAILS');
        expect(faq?.querySelector('summary')?.textContent).toBe('How long is the warranty?');
        expect(faq?.querySelector('strong')?.textContent).toBe('years');
    });
});
