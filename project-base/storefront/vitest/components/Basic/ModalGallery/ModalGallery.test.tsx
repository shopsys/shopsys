import { act, fireEvent, render, screen, within } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { ModalGallery } from 'components/Basic/ModalGallery/ModalGallery';
import { TypeFileFragment } from 'graphql/requests/files/fragments/FileFragment.generated';
import { TypeImageFragment } from 'graphql/requests/images/fragments/ImageFragment.generated';
import { TypeVideoTokenFragment } from 'graphql/requests/products/fragments/VideoTokenFragment.generated';
import { describe, expect, test, vi } from 'vitest';

vi.mock('components/Basic/Icon/PlayIcon', () => ({
    PlayIcon: () => <svg data-testid="play-icon" />,
}));

vi.mock('components/Basic/Icon/SpinnerIcon', () => ({
    SpinnerIcon: () => <svg data-testid="spinner-icon" />,
}));

vi.mock('components/Basic/Image/Image', () => ({
    Image: ({
        'aria-hidden': ariaHidden,
        alt,
        className,
        hash,
        src,
    }: {
        'aria-hidden'?: boolean | 'true';
        alt: string;
        className?: string;
        hash?: string;
        src?: string;
    }) => (
        <span
            aria-hidden={ariaHidden}
            aria-label={alt || undefined}
            className={className}
            data-hash={hash}
            data-src={src}
            role="img"
        />
    ),
}));

vi.mock('react-remove-scroll', () => ({
    RemoveScroll: ({ children }: { children: React.ReactNode }) => children,
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

const file: TypeFileFragment = {
    __typename: 'File',
    anchorText: 'Complaint attachment',
    extension: 'jpg',
    filesize: 1_024,
    url: '/complaint.jpg?token=hash',
    viewUrl: null,
};

const getVideoLoadingSpinner = (iframe: HTMLIFrameElement | null) =>
    iframe?.parentElement?.querySelector('[data-testid="spinner-icon"]');

describe('ModalGallery', () => {
    test('opens directly on the requested image and focuses the close control', () => {
        const { container } = render(
            <ModalGallery galleryName="Test gallery" initialIndex={2} items={images} onCloseModal={vi.fn()} />,
        );

        const dialog = screen.getByRole('dialog', { name: 'Gallery' });
        const galleryTrack = screen.getByRole('list', { name: 'Gallery content' });

        expect(dialog).toHaveClass(
            'fixed',
            'inset-0',
            'h-screen',
            'grid-cols-[minmax(0,1fr)]',
            'overflow-hidden',
            'supports-[height:100dvh]:h-dvh',
        );
        expect(dialog.querySelector('section')).toHaveClass('min-w-0');
        expect(dialog.querySelector('footer')).toHaveClass('min-w-0');
        expect(within(galleryTrack).getByRole('img', { name: 'Back view' })).toHaveAttribute('data-src', '/back.jpg');
        expect(screen.queryByLabelText('Test gallery, slide 3 of 3')).not.toBeInTheDocument();
        expect(screen.getByRole('button', { name: 'Close' })).toHaveFocus();
        expect(screen.getByRole('toolbar', { name: 'Gallery navigation' })).toHaveClass('hidden', 'vl:block');
        const thumbnailTablist = screen.getByRole('tablist', { name: 'Gallery thumbnails' });
        expect(thumbnailTablist).toHaveClass('w-full', 'min-w-0');
        expect(within(thumbnailTablist).getByRole('list')).toHaveClass('mx-auto', 'w-fit', 'max-w-full');
        expect(container.querySelector('[aria-hidden="true"][style="width: 72px;"]')).not.toBeInTheDocument();

        const thumbnailTabs = screen.getAllByRole('tab', { name: 'Select image from gallery' });
        expect(thumbnailTabs[2]).toHaveAttribute('aria-selected', 'true');
        expect(thumbnailTabs[2]).toHaveClass('border-border-accent');
        expect(thumbnailTabs[0]).toHaveAttribute('aria-selected', 'false');
        expect(thumbnailTabs[0]).toHaveClass('opacity-60');
    });

    test('loads and selects the nearest image after native track scrolling', () => {
        render(<ModalGallery galleryName="Test gallery" initialIndex={0} items={images} onCloseModal={vi.fn()} />);
        const galleryTrack = screen.getByRole('list', { name: 'Gallery content' });
        Object.defineProperty(galleryTrack, 'clientWidth', { configurable: true, value: 500 });

        expect(within(galleryTrack).queryByRole('img', { name: 'Back view' })).not.toBeInTheDocument();

        galleryTrack.scrollLeft = 1_000;
        fireEvent.scroll(galleryTrack);

        expect(within(galleryTrack).getByRole('img', { name: 'Back view' })).toHaveAttribute('data-src', '/back.jpg');
    });

    test('scrolls exactly one item when the next control is clicked', async () => {
        const user = userEvent.setup();
        render(<ModalGallery galleryName="Test gallery" initialIndex={0} items={images} onCloseModal={vi.fn()} />);
        const galleryTrack = screen.getByRole('list', { name: 'Gallery content' });
        const scrollTo = vi.fn();
        Object.defineProperty(galleryTrack, 'clientWidth', { configurable: true, value: 500 });
        Object.defineProperty(galleryTrack, 'scrollTo', { configurable: true, value: scrollTo });

        await user.click(screen.getByRole('button', { name: 'Next' }));

        expect(scrollTo).toHaveBeenCalledWith({ behavior: 'smooth', left: 500 });
        expect(within(galleryTrack).getByRole('img', { name: 'Side view' })).toBeInTheDocument();
    });

    test('renders the video iframe only while the video is selected', async () => {
        const user = userEvent.setup();
        const { container } = render(
            <ModalGallery
                galleryName="Test gallery"
                initialIndex={1}
                items={[images[0], video, images[1]]}
                onCloseModal={vi.fn()}
            />,
        );

        const iframe = container.querySelector('iframe');
        const thumbnail = container.querySelector(
            '[data-src="https://img.youtube.com/vi/youtube-token/maxresdefault.jpg"]',
        );

        expect(iframe).toHaveAttribute('src', 'https://www.youtube.com/embed/youtube-token?autoplay=1&mute=1');
        expect(iframe).toHaveClass('pointer-events-none', 'opacity-0');
        expect(iframe).toHaveAttribute('tabindex', '-1');
        expect(thumbnail).toHaveClass('opacity-100');
        expect(getVideoLoadingSpinner(iframe)).toBeInTheDocument();

        fireEvent.load(iframe!);

        expect(iframe).toHaveClass('opacity-100');
        expect(iframe).toHaveAttribute('tabindex', '0');
        expect(thumbnail).toHaveClass('opacity-0');
        expect(getVideoLoadingSpinner(iframe)).not.toBeInTheDocument();

        await user.click(screen.getByRole('button', { name: 'Next' }));

        expect(container.querySelector('iframe')).not.toBeInTheDocument();

        await user.click(screen.getByRole('button', { name: 'Previous' }));

        expect(container.querySelector('iframe')).toHaveClass('pointer-events-none', 'opacity-0');
        expect(thumbnail).toHaveClass('opacity-100');
        expect(getVideoLoadingSpinner(container.querySelector('iframe'))).toBeInTheDocument();
    });

    test('keeps the video thumbnail visible until the iframe is loaded and the swipe settles', () => {
        vi.useFakeTimers();

        try {
            const { container } = render(
                <ModalGallery
                    galleryName="Test gallery"
                    initialIndex={2}
                    items={[images[0], video, images[1]]}
                    onCloseModal={vi.fn()}
                />,
            );
            const galleryTrack = screen.getByRole('list', { name: 'Gallery content' });
            Object.defineProperty(galleryTrack, 'clientWidth', { configurable: true, value: 500 });

            galleryTrack.scrollLeft = 1_000;
            fireEvent.scroll(galleryTrack);
            galleryTrack.scrollLeft = 540;
            fireEvent.scroll(galleryTrack);

            const iframe = container.querySelector('iframe');
            const thumbnail = container.querySelector(
                '[data-src="https://img.youtube.com/vi/youtube-token/maxresdefault.jpg"]',
            );

            expect(iframe).toHaveAttribute('src', 'https://www.youtube.com/embed/youtube-token?autoplay=1&mute=1');
            expect(iframe).toHaveClass('opacity-0');
            expect(thumbnail).toHaveClass('opacity-100');
            expect(getVideoLoadingSpinner(iframe)).toBeInTheDocument();

            fireEvent.load(iframe!);

            expect(iframe).toHaveClass('opacity-0');
            expect(thumbnail).toHaveClass('opacity-100');
            expect(getVideoLoadingSpinner(iframe)).not.toBeInTheDocument();

            act(() => {
                vi.advanceTimersByTime(100);
            });

            expect(galleryTrack.scrollLeft).toBe(500);
            expect(iframe).toHaveClass('opacity-100');
            expect(thumbnail).toHaveClass('opacity-0');
        } finally {
            vi.useRealTimers();
        }
    });

    test('renders complaint files and closes from the shared control button', async () => {
        const user = userEvent.setup();
        const onCloseModal = vi.fn();
        render(
            <ModalGallery
                galleryName="Complaint gallery"
                initialIndex={0}
                items={[file]}
                onCloseModal={onCloseModal}
            />,
        );

        const galleryTrack = screen.getByRole('list', { name: 'Gallery content' });
        expect(within(galleryTrack).getByRole('img', { name: 'Complaint attachment' })).toHaveAttribute(
            'data-hash',
            'token=hash',
        );

        await user.click(screen.getByRole('button', { name: 'Close' }));

        expect(onCloseModal).toHaveBeenCalledTimes(1);
    });
});
