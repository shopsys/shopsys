import { render, screen } from '@testing-library/react';
import { SearchInput } from 'components/Forms/TextInput/SearchInput';
import { describe, expect, test, vi } from 'vitest';

vi.mock('next-translate/useTranslation', () => ({
    __esModule: true,
    default: () => ({
        t: (key: string) => key,
    }),
}));

const defaultProps = {
    ariaLabelForSearchButton: 'Search',
    label: 'Search products',
    shouldShowSpinnerInInput: false,
    value: '',
    onChange: vi.fn(),
    onClear: vi.fn(),
};

describe('SearchInput', () => {
    test('uses default search input id', () => {
        render(<SearchInput {...defaultProps} />);

        expect(screen.getByRole('searchbox', { name: 'Search products' })).toHaveAttribute('id', 'search-input');
    });

    test('uses custom search input id', () => {
        render(<SearchInput {...defaultProps} inputId="sticky-search-input" />);

        expect(screen.getByRole('searchbox', { name: 'Search products' })).toHaveAttribute('id', 'sticky-search-input');
    });
});
