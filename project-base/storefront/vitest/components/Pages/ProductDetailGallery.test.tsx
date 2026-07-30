import { act, fireEvent, render, screen, within } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { ProductDetailGallery } from 'components/Pages/ProductDetail/ProductDetailGallery';
import { TypeImageFragment } from 'graphql/requests/images/fragments/ImageFragment.generated';
import { TypeVideoTokenFragment } from 'graphql/requests/products/fragments/VideoTokenFragment.generated';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const sessionStoreMocks = vi.hoisted(() => ({
    closePortalContent: vi.fn(),
    storeCurrentFocus: vi.fn(),
    updatePortalContent: vi.fn(),
}));

vi.mock('components/Basic/Icon/ArrowSecondaryIcon', () => ({
    ArrowSecondaryIcon: () => <svg data-testid="arrow-icon" />,
}));

vi.mock('components/Basic/Icon/PlayIcon', () => ({
    PlayIcon: () => <svg data-testid="play-icon" />,
}));

vi.mock('components/Basic/Image/Image', () => ({
    Image: ({ alt, priority, src, tid }: { alt: string; priority?: boolean; src?: string; tid?: string }) => (
        <span data-alt={alt} data-priority={priority ? 'true' : 'false'} data-src={src} data-tid={tid} />
    ),
}));

vi.mock('components/Blocks/Product/ProductFlags', () => ({
    ProductFlags: () => null,
}));

vi.mock('next/dynamic', () => ({
    default: () => () => null,
}));

vi.mock('store/useSessionStore', () => ({
    useSessionStore: (selector: (state: typeof sessionStoreMocks) => unknown) => selector(sessionStoreMocks),
}));

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({
        t: (key: string, options?: Record<string, string | number>) =>
            Object.entries(options ?? {}).reduce(
                (translatedKey, [optionKey, optionValue]) =>
                    translatedKey
                        .replaceAll(`{{ ${optionKey} }}`, String(optionValue))
                        .replaceAll(`{{${optionKey}}}`, String(optionValue)),
                key,
            ),
    }),
}));

const images: TypeImageFragment[] = [
    { __typename: 'Image', name: 'Front view', url: '/front.jpg' },
    { __typename: 'Image', name: 'Side view', url: '/side.jpg' },
    { __typename: 'Image', name: 'Back view', url: '/back.jpg' },
];

const video: TypeVideoTokenFragment = {
    __typename: 'VideoToken',
    description: 'Product video',
    token: 'youtube-token',
};

const manyImages: TypeImageFragment[] = Array.from({ length: 7 }, (_, index) => ({
    __typename: 'Image',
    name: `Gallery image ${index + 1}`,
    url: `/gallery-image-${index + 1}.jpg`,
}));

const renderGallery = (galleryImages = images, videoIds: TypeVideoTokenFragment[] = []) =>
    render(
        <ProductDetailGallery
            flags={[]}
            images={galleryImages}
            percentageDiscount={null}
            productName="Test product"
            videoIds={videoIds}
        />,
    );

describe('ProductDetailGallery', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    test('opens the fullscreen gallery on the clicked thumbnail without changing the main image', async () => {
        const user = userEvent.setup();
        renderGallery();
        const mainImageButton = screen.getByRole('button', { name: 'Open image gallery of Test product' });
        const firstThumbnailButton = screen.getByRole('button', { name: 'Open item 1 of 3 in gallery' });
        const thirdThumbnailButton = screen.getByRole('button', { name: 'Open item 3 of 3 in gallery' });

        expect(mainImageButton.querySelector('[data-src="/front.jpg"]')).toHaveAttribute('data-priority', 'true');
        expect(firstThumbnailButton).toHaveAttribute('aria-current', 'true');
        expect(screen.getByLabelText('Test product, slide 1 of 3')).toHaveTextContent('1 / 3');

        await user.click(thirdThumbnailButton);

        expect(sessionStoreMocks.storeCurrentFocus).toHaveBeenCalledTimes(1);
        expect(sessionStoreMocks.updatePortalContent).toHaveBeenCalledTimes(1);

        const modalGalleryElement = sessionStoreMocks.updatePortalContent.mock.calls[0][0];
        expect(modalGalleryElement.props.initialIndex).toBe(2);
        expect(modalGalleryElement.props.items).toEqual(images);
        expect(mainImageButton.querySelector('[data-src="/front.jpg"]')).toBeInTheDocument();
        expect(firstThumbnailButton).toHaveAttribute('aria-current', 'true');
        expect(thirdThumbnailButton).not.toHaveAttribute('aria-current');
    });

    test('opens the fullscreen gallery on the selected item', async () => {
        const user = userEvent.setup();
        renderGallery(images, [video]);
        const galleryTrack = screen.getByRole('list', { name: 'Product media gallery' });
        Object.defineProperty(galleryTrack, 'clientWidth', { configurable: true, value: 500 });

        galleryTrack.scrollLeft = 1_000;
        fireEvent.scroll(galleryTrack);
        await user.click(screen.getByRole('button', { name: 'Open image gallery of Test product' }));

        expect(sessionStoreMocks.storeCurrentFocus).toHaveBeenCalledTimes(1);
        expect(sessionStoreMocks.updatePortalContent).toHaveBeenCalledTimes(1);

        const modalGalleryElement = sessionStoreMocks.updatePortalContent.mock.calls[0][0];
        expect(modalGalleryElement.props.initialIndex).toBe(2);
        expect(modalGalleryElement.props.items).toEqual([images[0], video, images[1], images[2]]);
    });

    test('loads and selects the next main image only after the native gallery track is scrolled', () => {
        renderGallery();
        const galleryTrack = screen.getByRole('list', { name: 'Product media gallery' });
        Object.defineProperty(galleryTrack, 'clientWidth', { configurable: true, value: 500 });

        expect(galleryTrack.querySelector('[data-src="/front.jpg"]')).toBeInTheDocument();
        expect(galleryTrack.querySelector('[data-src="/side.jpg"]')).not.toBeInTheDocument();

        galleryTrack.scrollLeft = 500;
        fireEvent.scroll(galleryTrack);

        const selectedMainImageButton = screen.getByRole('button', {
            name: 'Open image gallery of Test product',
        });
        expect(selectedMainImageButton.querySelector('[data-src="/side.jpg"]')).toBeInTheDocument();
        expect(galleryTrack.querySelector('[data-src="/side.jpg"]')).toBeInTheDocument();
        expect(sessionStoreMocks.updatePortalContent).not.toHaveBeenCalled();
    });

    test('shows the position counter while scrolling and hides it after three seconds', () => {
        vi.useFakeTimers();

        try {
            renderGallery();
            const galleryTrack = screen.getByRole('list', { name: 'Product media gallery' });
            const positionCounter = screen.getByLabelText('Test product, slide 1 of 3');
            Object.defineProperty(galleryTrack, 'clientWidth', { configurable: true, value: 500 });

            expect(positionCounter).toHaveClass('opacity-0');
            expect(positionCounter).not.toHaveClass('opacity-100');

            galleryTrack.scrollLeft = 10;
            fireEvent.scroll(galleryTrack);

            expect(positionCounter).toHaveClass('opacity-100');

            act(() => {
                vi.advanceTimersByTime(3000);
            });

            expect(positionCounter).toHaveClass('opacity-0');
            expect(positionCounter).not.toHaveClass('opacity-100');

            galleryTrack.scrollLeft = 20;
            fireEvent.scroll(galleryTrack);

            expect(positionCounter).toHaveClass('opacity-100');

            act(() => {
                vi.advanceTimersByTime(2000);
            });

            galleryTrack.scrollLeft = 30;
            fireEvent.scroll(galleryTrack);

            act(() => {
                vi.advanceTimersByTime(2000);
            });

            expect(positionCounter).toHaveClass('opacity-100');

            act(() => {
                vi.advanceTimersByTime(1000);
            });

            expect(positionCounter).toHaveClass('opacity-0');
        } finally {
            vi.useRealTimers();
        }
    });

    test('renders a video thumbnail in the inline carousel instead of an iframe', async () => {
        const user = userEvent.setup();
        const { container } = renderGallery(images, [video]);

        await user.click(screen.getByRole('button', { name: 'Next' }));

        const mainImageButton = screen.getByRole('button', { name: 'Open image gallery of Test product' });
        expect(mainImageButton.querySelector('[data-src]')).toHaveAttribute(
            'data-src',
            'https://img.youtube.com/vi/youtube-token/maxresdefault.jpg',
        );
        expect(within(mainImageButton).getByTestId('play-icon')).toBeInTheDocument();
        expect(container.querySelector('iframe')).not.toBeInTheDocument();
    });

    test('opens the fullscreen gallery on the first hidden item from the more button', async () => {
        const user = userEvent.setup();
        renderGallery(manyImages);
        const thumbnailButtons = screen.getAllByRole('button', { name: /Open item \d of 7 in gallery/ });
        const moreButton = screen.getByRole('button', { name: 'Open 2 more items in gallery' });

        expect(thumbnailButtons).toHaveLength(5);
        expect(moreButton).toHaveTextContent('+2');

        await user.click(moreButton);

        expect(sessionStoreMocks.storeCurrentFocus).toHaveBeenCalledTimes(1);
        expect(sessionStoreMocks.updatePortalContent).toHaveBeenCalledTimes(1);

        const modalGalleryElement = sessionStoreMocks.updatePortalContent.mock.calls[0][0];
        expect(modalGalleryElement.props.initialIndex).toBe(5);
        expect(modalGalleryElement.props.items).toEqual(manyImages);
    });

    test('renders gallery media as decorative inside descriptively labelled buttons', () => {
        const { container } = renderGallery(images, [video]);

        const galleryButtons = [
            screen.getByRole('button', { name: 'Open image gallery of Test product' }),
            ...screen.getAllByRole('button', { name: /Open item \d of 4 in gallery/ }),
        ];

        expect(galleryButtons).toHaveLength(5);
        expect(container.querySelectorAll('[data-alt=""]')).toHaveLength(4);
        expect(container.querySelectorAll('[data-alt="Front view"]')).toHaveLength(1);
    });
});
