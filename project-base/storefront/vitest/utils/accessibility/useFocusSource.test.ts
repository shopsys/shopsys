import { fireEvent, renderHook } from '@testing-library/react';
import { useFocusSource } from 'utils/accessibility/useFocusSource';
import { afterEach, describe, expect, test } from 'vitest';

const getFocusSource = () => document.documentElement.dataset.focusSource;

describe('useFocusSource', () => {
    afterEach(() => {
        delete document.documentElement.dataset.focusSource;
    });

    test('switches to keyboard on Tab and back to pointer on a click', () => {
        renderHook(() => useFocusSource());

        fireEvent.keyDown(window, { key: 'Tab' });
        expect(getFocusSource()).toBe('keyboard');

        fireEvent.pointerDown(window);
        expect(getFocusSource()).toBe('pointer');
    });

    test.each(['a', 'Enter', 'ArrowDown'])('keeps pointer when %s is typed after a click', (key) => {
        renderHook(() => useFocusSource());

        fireEvent.pointerDown(window);
        fireEvent.keyDown(window, { key });

        expect(getFocusSource()).toBe('pointer');
    });

    test('stops listening after unmount', () => {
        const { unmount } = renderHook(() => useFocusSource());

        unmount();
        fireEvent.keyDown(window, { key: 'Tab' });

        expect(getFocusSource()).toBeUndefined();
    });
});
