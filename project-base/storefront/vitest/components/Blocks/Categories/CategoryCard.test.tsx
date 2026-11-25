import { render, screen } from '@testing-library/react';
import { CategoryCard } from 'components/Blocks/Categories/CategoryCard';
import { DomainConfigProvider } from 'components/providers/DomainConfigProvider';
import { expect, test } from 'vitest';
import { defaultTestDomainConfig } from 'vitest/helpers/mockPublicConfig';

test.each([
    ['catalog', 2],
    ['homepage', 3],
] as const)('renders the %s category name as a level %i heading', (variant, level) => {
    render(
        <DomainConfigProvider domainConfig={defaultTestDomainConfig}>
            <CategoryCard category={{ name: 'Books', slug: '/books' }} variant={variant} />
        </DomainConfigProvider>,
    );

    expect(screen.getByRole('heading', { name: 'Books', level })).toBeInTheDocument();
});
