import { act, render, screen } from '@testing-library/react';
import { SkeletonCrossfade } from 'components/Blocks/Skeleton/SkeletonCrossfade';
import { type ReactNode, useRef } from 'react';
import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest';

// an exit that never completes: the presence keeps rendering the last skeleton it was given
vi.mock('framer-motion', () => ({
    useReducedMotion: () => false,
    AnimatePresence: ({ children }: { children: ReactNode }) => {
        const lastChildrenRef = useRef(children);

        if (children) {
            lastChildrenRef.current = children;
        }

        return children || lastChildrenRef.current;
    },
    m: {
        div: ({ children }: { children: ReactNode }) => <div>{children}</div>,
    },
}));

const renderCrossfade = (isSkeletonVisible: boolean) => (
    <SkeletonCrossfade isSkeletonVisible={isSkeletonVisible} skeleton={<span>Loading placeholder</span>}>
        <button type="button">Loaded content</button>
    </SkeletonCrossfade>
);

describe('SkeletonCrossfade', () => {
    beforeEach(() => {
        vi.useFakeTimers();
    });

    afterEach(() => {
        vi.useRealTimers();
    });

    test('drops a skeleton whose exit did not finish', () => {
        const { rerender } = render(renderCrossfade(false));
        rerender(renderCrossfade(true));
        rerender(renderCrossfade(false));

        expect(screen.getByText('Loading placeholder')).toBeInTheDocument();
        expect(screen.getByRole('button', { name: 'Loaded content' })).toBeInTheDocument();

        act(() => {
            vi.advanceTimersByTime(500);
        });

        expect(screen.queryByText('Loading placeholder')).not.toBeInTheDocument();
        expect(screen.getByRole('button', { name: 'Loaded content' })).toBeInTheDocument();
    });

    test('keeps the skeleton when another load starts before the timeout', () => {
        const { rerender } = render(renderCrossfade(true));
        rerender(renderCrossfade(false));
        act(() => {
            vi.advanceTimersByTime(300);
        });
        rerender(renderCrossfade(true));
        act(() => {
            vi.advanceTimersByTime(500);
        });

        expect(screen.getByText('Loading placeholder')).toBeInTheDocument();
        expect(screen.queryByRole('button', { name: 'Loaded content' })).not.toBeInTheDocument();
    });
});
