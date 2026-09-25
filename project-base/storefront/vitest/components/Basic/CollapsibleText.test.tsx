import { render } from '@testing-library/react';
import { CollapsibleText } from 'components/Basic/CollapsibleText/CollapsibleText';
import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest';

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({
        t: (key: string) => key,
    }),
}));

describe('CollapsibleText', () => {
    beforeEach(() => {
        vi.stubGlobal(
            'ResizeObserver',
            class ResizeObserver {
                observe() {}
                unobserve() {}
                disconnect() {}
            },
        );
    });

    afterEach(() => {
        vi.unstubAllGlobals();
    });

    test('keeps the default readable width', () => {
        const { container } = render(<CollapsibleText text="Description" />);
        const textWrapper = container.querySelector('.user-text')?.parentElement;

        expect(textWrapper).toHaveClass('max-w-2xl');
    });

    test('allows a page to widen the text without retaining the default cap', () => {
        const { container } = render(<CollapsibleText text="Description" textClassName="max-w-5xl" />);
        const textWrapper = container.querySelector('.user-text')?.parentElement;

        expect(textWrapper).toHaveClass('max-w-5xl');
        expect(textWrapper).not.toHaveClass('max-w-2xl');
    });
});
