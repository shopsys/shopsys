import { screen } from '@testing-library/react';
import { FooterExtras } from 'components/Layout/Footer/FooterExtras';
import { describe, expect, test, vi } from 'vitest';
import { renderWithTooltipProvider } from 'vitest/helpers/renderWithTooltipProvider';

vi.mock('graphql/requests/transports/queries/TransportsImage.generated', () => ({
    useTransportsImage: () => [
        {
            data: {
                transports: [{ name: 'DPD', mainImage: { name: 'dpd-logo', url: '/dpd.png' } }],
            },
            fetching: false,
        },
    ],
}));

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({ t: (key: string) => key }),
}));

describe('FooterExtras', () => {
    test('uses the transport name as the accessible image label', () => {
        renderWithTooltipProvider(<FooterExtras />);

        expect(screen.getByRole('img', { name: 'DPD' })).toBeInTheDocument();
    });

    test('uses distinct demo destinations for social links', () => {
        renderWithTooltipProvider(<FooterExtras />);

        const instagramLink = screen.getByRole('link', { name: 'Go to Instagram' });
        const facebookLink = screen.getByRole('link', { name: 'Go to Facebook' });
        const youtubeLink = screen.getByRole('link', { name: 'Go to Youtube' });
        const destinations = [instagramLink, facebookLink, youtubeLink].map((link) => link.getAttribute('href'));

        expect(instagramLink).toHaveAttribute('href', 'https://example.com/demo-shop/instagram');
        expect(facebookLink).toHaveAttribute('href', 'https://example.com/demo-shop/facebook');
        expect(youtubeLink).toHaveAttribute('href', 'https://example.com/demo-shop/youtube');
        expect(new Set(destinations).size).toBe(3);
    });
});
