import { render, screen } from '@testing-library/react';
import { Banner } from 'components/Blocks/Banners/Banner';
import type { TypeSliderItemFragment } from 'graphql/requests/sliderItems/fragments/SliderItemFragment.generated';
import { describe, expect, test, vi } from 'vitest';

vi.mock('components/Blocks/Banners/BannerImage', () => ({
    BannerImage: ({ desktopAlt, mobileAlt }: { desktopAlt: string; mobileAlt: string }) => (
        <span aria-label={mobileAlt} data-desktop-alt={desktopAlt} role="img" />
    ),
}));

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({ t: (key: string) => key }),
}));

const banner = {
    __typename: 'SliderItem',
    uuid: '8e6a3287-e691-457a-baf4-6ca6a67e10ac',
    name: 'Shopsys Platform',
    link: 'https://www.shopsys.com',
    routeName: null,
    description: 'Build scalable B2C and B2B stores on an open commerce platform designed for complex projects.',
    rgbBackgroundColor: '#ffffff',
    opacity: 100,
    webMainImage: {
        __typename: 'Image',
        name: 'E-commerce order preparation in a modern warehouse',
        url: '/desktop.jpg',
    },
    mobileMainImage: {
        __typename: 'Image',
        name: 'E-commerce order preparation in a modern warehouse',
        url: '/mobile.jpg',
    },
} satisfies TypeSliderItemFragment;

describe('Banner', () => {
    test('uses a concise image alternative that identifies the promotion', () => {
        render(<Banner banner={banner} isFirst={false} order={0} />);

        const image = screen.getByRole('img', { name: 'Promotional banner: Shopsys Platform' });
        const mobileAlt = image.getAttribute('aria-label');
        expect(image).toHaveAttribute('data-desktop-alt', 'Promotional banner: Shopsys Platform');
        expect(mobileAlt?.length).toBeLessThan(100);
        expect(mobileAlt).not.toContain(banner.description);
    });
});
