import { render, screen } from '@testing-library/react';
import { IconButton } from 'components/Forms/Button/IconButton';
import { describe, expect, test, vi } from 'vitest';

const TestIcon: SvgFC = (props) => <svg {...props} />;

describe('IconButton', () => {
    test('does not submit parent forms by default', () => {
        render(<IconButton Icon={TestIcon} title="Close popup" onClick={vi.fn()} />);

        expect(screen.getByRole('button', { name: 'Close popup' })).toHaveAttribute('type', 'button');
    });
});
