import { ModalGalleryCarousel } from './ModalGalleryCarousel';
import { SpinnerIcon } from 'components/Basic/Icon/IconsSvg';
import { Image } from 'components/Basic/Image/Image';
import { ImageFragment } from 'graphql/requests/images/fragments/ImageFragment.generated';
import { VideoTokenFragment } from 'graphql/requests/products/fragments/VideoTokenFragment.generated';
import { twMergeCustom } from 'helpers/twMerge';
import { useKeypress } from 'hooks/useKeyPress';
import useTranslation from 'next-translate/useTranslation';
import { RefObject, createRef, useEffect, useState } from 'react';
import { useSwipeable } from 'react-swipeable';
import { twJoin } from 'tailwind-merge';

type ModalGalleryProps = {
    items: (VideoTokenFragment | ImageFragment)[];
    initialIndex: number;
    galleryName: string;
    onCloseModal: () => void;
};

export const ModalGallery: FC<ModalGalleryProps> = ({ initialIndex, items, galleryName, onCloseModal }) => {
    const { t } = useTranslation();

    const [selectedIndex, setSelectedIndex] = useState(initialIndex);
    const itemsRefs: Array<RefObject<HTMLLIElement>> = Array(items.length)
        .fill(null)
        .map(() => createRef());
    const [isLoaded, setIsLoaded] = useState(false);

    const selectedGalleryItem = items[selectedIndex];

    const isImage = selectedGalleryItem.__typename === 'Image';
    const isVideo = selectedGalleryItem.__typename === 'VideoToken';

    const lastItemIndex = items.length - 1;

    const isCarouselDisplayed = items.length > 1 && (!isImage || isLoaded);

    const selectPreviousItem = () =>
        setSelectedIndex((currentSelectedIndex) =>
            currentSelectedIndex > 0 ? currentSelectedIndex - 1 : lastItemIndex,
        );

    const selectNextItem = () =>
        setSelectedIndex((currentSelectedIndex) =>
            currentSelectedIndex < lastItemIndex ? currentSelectedIndex + 1 : 0,
        );

    useEffect(() => {
        if (isCarouselDisplayed) {
            itemsRefs[selectedIndex].current?.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest',
                inline: 'start',
            });
        }
    }, [selectedIndex, itemsRefs, isCarouselDisplayed]);

    useKeypress('Escape', onCloseModal);
    useKeypress('ArrowRight', selectNextItem);
    useKeypress('ArrowLeft', selectPreviousItem);

    const handlers = useSwipeable({
        onSwipedLeft: selectNextItem,
        onSwipedRight: selectPreviousItem,
        trackMouse: true,
    });

    return (
        <div className="fixed inset-0 z-aboveOverlay flex select-none flex-col bg-dark p-2" onClick={onCloseModal}>
            <div className="flex w-full flex-1 flex-col justify-center">
                <div className="relative my-auto flex max-h-[80dvh] flex-1 items-center justify-center" {...handlers}>
                    <SpinnerIcon className="absolute -z-above w-16 text-white opacity-50" />

                    {isImage && (
                        <Image
                            key={selectedIndex}
                            alt={selectedGalleryItem.name || `${galleryName}-${selectedIndex}`}
                            className="max-h-full object-contain"
                            draggable={false}
                            height={1200}
                            sizes="(max-width: 768px) 100vw, 1200px"
                            src={selectedGalleryItem.url}
                            width={1200}
                            onLoad={() => setIsLoaded(true)}
                        />
                    )}

                    {isVideo && (
                        <iframe
                            allowFullScreen
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            className="aspect-video max-h-full w-full max-w-xl md:max-w-[1500px]"
                            src={`https://www.youtube.com/embed/${selectedGalleryItem.token}?autoplay=1&mute=1`}
                            title={selectedGalleryItem.description}
                        />
                    )}
                </div>

                {isImage && selectedGalleryItem.name && (
                    <div className="mt-2 text-center text-greyDarker">{selectedGalleryItem.name}</div>
                )}

                <div className="mt-4 flex items-center justify-center gap-8">
                    <ButtonArrow position="left" title={t('Previous')} onClick={selectPreviousItem} />
                    <ButtonArrow position="right" title={t('Next')} onClick={selectNextItem} />
                </div>

                <div className="mt-4 flex items-center justify-center gap-2">
                    {isCarouselDisplayed && (
                        <ModalGalleryCarousel
                            galleryName={galleryName}
                            items={items}
                            itemsRefs={itemsRefs}
                            selectedIndex={selectedIndex}
                            onSelectItem={setSelectedIndex}
                        />
                    )}
                </div>

                <ButtonClose title={t('Close')} onClick={onCloseModal} />
            </div>
        </div>
    );
};

type FloatingButtonProps = { onClick: () => void; title: string };

const FloatingButton: FC<FloatingButtonProps> = ({ className, children, onClick, ...buttonProps }) => (
    <button
        type="button"
        className={twMergeCustom(
            'inline-flex items-center justify-center rounded-full bg-white p-2 text-black opacity-20 transition-opacity hover:opacity-100',
            className,
        )}
        onClick={(e) => {
            e.stopPropagation();
            onClick();
        }}
        {...buttonProps}
    >
        {children}
    </button>
);

const ButtonArrow: FC<FloatingButtonProps & { position: 'left' | 'right' }> = ({
    position,
    ...floatingButtonProps
}) => {
    const isLeft = position === 'left';

    return (
        <FloatingButton className={twJoin('', isLeft ? 'left-2' : 'right-2')} {...floatingButtonProps}>
            <svg
                className={twJoin('text-red-500 h-8 w-8', isLeft && 'rotate-180')}
                fill="none"
                stroke="currentColor"
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth="2"
                viewBox="0 0 24 24"
            >
                <polyline points="9 18 15 12 9 6" />
            </svg>
        </FloatingButton>
    );
};

const ButtonClose: FC<FloatingButtonProps> = (floatingButtonProps) => (
    <FloatingButton className="absolute right-2 top-2" {...floatingButtonProps}>
        <svg
            className="h-6 w-6"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg"
        >
            <path d="M6 18L18 6M6 6l12 12" strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" />
        </svg>
    </FloatingButton>
);
