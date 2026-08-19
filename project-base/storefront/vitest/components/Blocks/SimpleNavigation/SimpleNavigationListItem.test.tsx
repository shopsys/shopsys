import { render, screen } from '@testing-library/react';
import { SimpleNavigationListItem } from 'components/Blocks/SimpleNavigation/SimpleNavigationListItem';
import type { ComponentProps } from 'react';
import { describe, expect, test, vi } from 'vitest';

vi.mock('components/Basic/ExtendedNextLink/ExtendedNextLink', () => ({
    ExtendedNextLink: ({ type: _type, ...props }: ComponentProps<'a'> & { type?: string }) => <a {...props} />,
}));

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({
        t: (key: string, options?: Record<string, string>) =>
            key.replace('{{ categoryName }}', options?.categoryName ?? ''),
    }),
}));

describe('SimpleNavigationListItem', () => {
    test('keeps the category card in place on hover', () => {
        render(
            <SimpleNavigationListItem
                listedItem={{
                    __typename: 'Category',
                    name: 'Cameras & Photo',
                    slug: '/electronics/cameras-photo',
                }}
            />,
        );

        const categoryLink = screen.getByRole('link', { name: 'Go to category Cameras & Photo' });

        expect(categoryLink).not.toHaveClass('pointer-fine:hover:-translate-y-0.5');
        expect(categoryLink).not.toHaveClass('motion-reduce:pointer-fine:hover:translate-y-0');
        expect(categoryLink).toHaveClass(
            'pointer-fine:hover:shadow-[0_12px_24px_-18px_rgb(37_40_61/40%),0_4px_10px_-8px_rgb(37_40_61/24%)]',
        );
    });
});
