import { render } from '@testing-library/react';
import { CollapsibleText } from 'components/Basic/CollapsibleText/CollapsibleText';
import { createRef } from 'react';
import { describe, expect, test, vi } from 'vitest';

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({
        t: (key: string) => key,
    }),
}));

describe('CollapsibleText', () => {
    test('keeps the default readable width', () => {
        const { container } = render(<CollapsibleText scrollTargetRef={createRef()} text="Description" />);
        const textWrapper = container.querySelector('.user-text')?.parentElement;

        expect(textWrapper).toHaveClass('max-w-2xl');
    });

    test('allows a page to widen the text without retaining the default cap', () => {
        const { container } = render(
            <CollapsibleText scrollTargetRef={createRef()} text="Description" textClassName="max-w-5xl" />,
        );
        const textWrapper = container.querySelector('.user-text')?.parentElement;

        expect(textWrapper).toHaveClass('max-w-5xl');
        expect(textWrapper).not.toHaveClass('max-w-2xl');
    });
});
