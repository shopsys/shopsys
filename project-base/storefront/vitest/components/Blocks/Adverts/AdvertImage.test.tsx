import { render } from '@testing-library/react';
import { AdvertImage } from 'components/Blocks/Adverts/AdvertImage';
import type { TypeAdvertsFragment_AdvertImage } from 'graphql/requests/adverts/fragments/AdvertsFragment.generated';
import { type ComponentProps, createElement } from 'react';
import { describe, expect, test, vi } from 'vitest';

vi.mock('components/Basic/Image/Image', () => ({
    Image: ({ fill, ...props }: ComponentProps<'img'> & { fill?: boolean }) => createElement('img', props),
}));

vi.mock('next/image', () => ({
    getImageProps: ({ src, sizes }: { src: string; sizes: string }) => ({
        props: {
            sizes,
            src,
            srcSet: `${src}?width=1024 1024w, ${src}?width=1440 1440w`,
        },
    }),
}));

const advert = {
    __typename: 'AdvertImage',
    categories: [],
    link: null,
    mainImage: {
        __typename: 'Image',
        name: 'Promotional offer',
        url: '/desktop.jpg',
    },
    mainImageMobile: {
        __typename: 'Image',
        name: 'Promotional offer',
        url: '/mobile.jpg',
    },
    name: 'Summer promotion',
    positionName: 'footer',
    type: 'image',
    uuid: '8e6a3287-e691-457a-baf4-6ca6a67e10ac',
} satisfies TypeAdvertsFragment_AdvertImage;

describe('AdvertImage', () => {
    test('renders responsive sources as one semantic image', () => {
        const { container } = render(<AdvertImage advert={advert} />);

        const picture = container.querySelector('picture');
        const source = picture?.querySelector('source');
        const images = picture?.querySelectorAll('img');

        expect(picture).toBeInTheDocument();
        expect(source).toHaveAttribute('media', '(min-width: 769px)');
        expect(source).toHaveAttribute('srcset', expect.stringContaining('/desktop.jpg'));
        expect(images).toHaveLength(1);
        expect(images?.[0]).toHaveAttribute('alt', 'Promotional offer');
        expect(images?.[0]).toHaveAttribute('src', '/mobile.jpg');
    });

    test.each([
        ['header', 'lg:max-w-[1520px]', '(min-width: 1560px) 1520px, calc(100vw - 40px)'],
        ['cartPreview', 'lg:max-w-[1280px]', '(min-width: 1320px) 1280px, 100vw'],
    ])('uses the expected desktop width for the %s position', (positionName, maxWidthClass, sizes) => {
        const { container } = render(<AdvertImage advert={{ ...advert, positionName }} />);

        expect(container.firstElementChild).toHaveClass(maxWidthClass);
        expect(container.querySelector('source')).toHaveAttribute('sizes', sizes);
    });
});
