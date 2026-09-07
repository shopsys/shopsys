import { fireEvent, render, screen, waitFor } from '@testing-library/react';
import { Banner } from 'components/Blocks/Banners/Banner';
import type { TypeSliderItemFragment } from 'graphql/requests/sliderItems/fragments/SliderItemFragment.generated';
import { describe, expect, test, vi } from 'vitest';

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
        name: 'Warehouse worker packing an order',
        url: '/mobile.jpg',
    },
} satisfies TypeSliderItemFragment;

describe('Banner', () => {
    test('uses the ALT of the displayed image and updates it after resizing', async () => {
        window.innerWidth = 1024;
        render(<Banner banner={banner} isFirst={false} order={0} />);

        const image = screen.getByRole('img', { name: banner.webMainImage.name });

        window.innerWidth = 768;
        fireEvent(window, new Event('resize'));

        await waitFor(() => expect(image).toHaveAttribute('alt', banner.mobileMainImage.name));

        window.innerWidth = 769;
        fireEvent(window, new Event('resize'));

        await waitFor(() => expect(image).toHaveAttribute('alt', banner.webMainImage.name));
    });

    test('uses the banner name when the selected variant has no ALT', () => {
        window.innerWidth = 1024;
        render(
            <Banner
                banner={{ ...banner, webMainImage: { ...banner.webMainImage, name: '   ' } }}
                isFirst={false}
                order={0}
            />,
        );

        expect(screen.getByRole('img')).toHaveAttribute('alt', banner.name);
    });
});
