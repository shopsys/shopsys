import { act, fireEvent, render, screen } from '@testing-library/react';
import {
    COMPARISON_UNDO_TOAST_ID,
    ProductComparisonUndoAction,
} from 'components/Pages/ProductComparison/ProductComparisonUndoAction';
import { toast } from 'react-toastify';
import { beforeEach, describe, expect, test, vi } from 'vitest';

vi.mock('utils/i18n/useTranslationWrapper', () => ({ default: () => ({ t: (key: string) => key }) }));
vi.mock('react-toastify', () => ({ toast: { dismiss: vi.fn(), pause: vi.fn(), play: vi.fn(), update: vi.fn() } }));

describe('comparison Undo action', () => {
    beforeEach(() => vi.clearAllMocks());

    test('keeps Undo available after failure and closes only after a successful retry', async () => {
        let finish: (success: boolean) => void = () => {};
        const undoRequest = vi.fn(
            () =>
                new Promise<boolean>((resolve) => {
                    finish = resolve;
                }),
        );
        render(<ProductComparisonUndoAction onUndo={undoRequest} />);
        const undo = screen.getByRole('button', { name: 'Undo' });
        fireEvent.click(undo);
        expect(undo).toBeDisabled();
        expect(toast.dismiss).not.toHaveBeenCalled();
        expect(toast.update).toHaveBeenCalledWith(COMPARISON_UNDO_TOAST_ID, { autoClose: false });
        await act(async () => finish(false));
        expect(undo).toBeEnabled();
        fireEvent.click(undo);
        await act(async () => finish(true));
        expect(undoRequest).toHaveBeenCalledTimes(2);
        expect(toast.dismiss).toHaveBeenCalledWith(COMPARISON_UNDO_TOAST_ID);
    });

    test('an older request cannot close the toast for a newer removal', async () => {
        let finish: (success: boolean) => void = () => {};
        const { rerender } = render(
            <ProductComparisonUndoAction
                key="first"
                onUndo={() =>
                    new Promise((resolve) => {
                        finish = resolve;
                    })
                }
            />,
        );
        fireEvent.click(screen.getByRole('button', { name: 'Undo' }));
        rerender(<ProductComparisonUndoAction key="second" onUndo={async () => true} />);
        await act(async () => finish(true));
        expect(toast.dismiss).not.toHaveBeenCalled();
    });

    test('pauses dismissal while the action has keyboard focus', () => {
        render(<ProductComparisonUndoAction onUndo={async () => true} />);
        fireEvent.focus(screen.getByRole('button', { name: 'Undo' }));
        expect(toast.pause).toHaveBeenCalledWith({ id: COMPARISON_UNDO_TOAST_ID });
    });
});
