import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { useRef } from 'react';
import { useFocusTrap } from 'utils/useFocusTrap';
import { describe, expect, test } from 'vitest';

type FocusTrapTestProps = {
    autoComplete?: string;
};

const FocusTrapTest: FC<FocusTrapTestProps> = ({ autoComplete }) => {
    const containerRef = useRef<HTMLDivElement>(null);

    useFocusTrap(containerRef);

    return (
        <>
            <div ref={containerRef}>
                <button type="button">First</button>
                <input aria-label="Input" autoComplete={autoComplete} />
                <button type="button">Last</button>
            </div>
            <button type="button">Outside</button>
        </>
    );
};

describe('useFocusTrap', () => {
    test('keeps regular focus changes inside the container', () => {
        render(<FocusTrapTest />);
        screen.getByRole('textbox', { name: 'Input' }).focus();

        screen.getByRole('button', { name: 'Outside' }).focus();

        expect(screen.getByRole('button', { name: 'First' })).toHaveFocus();
    });

    test('allows focus to leave an input with enabled autocomplete', () => {
        render(<FocusTrapTest autoComplete="given-name" />);
        screen.getByRole('textbox', { name: 'Input' }).focus();

        screen.getByRole('button', { name: 'Outside' }).focus();

        expect(screen.getByRole('button', { name: 'Outside' })).toHaveFocus();
    });

    test('keeps tab navigation inside the container', async () => {
        const user = userEvent.setup();
        render(<FocusTrapTest autoComplete="given-name" />);
        screen.getByRole('button', { name: 'Last' }).focus();

        await user.tab();

        expect(screen.getByRole('button', { name: 'First' })).toHaveFocus();
    });
});
