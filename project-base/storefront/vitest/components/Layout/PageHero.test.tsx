import { render, screen } from '@testing-library/react';
import { PageHero, type PageHeroDescriptionRole } from 'components/Layout/PageHero/PageHero';
import { describe, expect, test } from 'vitest';

const TestIcon = () => <svg />;

describe('PageHero', () => {
    test('renders a static description without live region semantics', () => {
        render(<PageHero description="Static page description" icon={TestIcon} title="Page title" />);

        const description = screen.getByText('Static page description');
        expect(description).not.toHaveAttribute('role');
        expect(description).not.toHaveAttribute('aria-live');
        expect(description).not.toHaveAttribute('aria-atomic');
    });

    test.each<PageHeroDescriptionRole>([
        'status',
        'alert',
    ])('renders a dynamic description with the requested %s role', (descriptionRole) => {
        render(
            <PageHero
                description="Dynamic page description"
                descriptionRole={descriptionRole}
                icon={TestIcon}
                title="Page title"
            />,
        );

        expect(screen.getByRole(descriptionRole)).toHaveTextContent('Dynamic page description');
    });
});
