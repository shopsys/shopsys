import { ModalGalleryCarousel } from './ModalGalleryCarousel';
import { SpinnerIcon } from 'components/Basic/Icon/SpinnerIcon';
import { Image } from 'components/Basic/Image/Image';
import { TypeFileFragment } from 'graphql/requests/files/fragments/FileFragment.generated';
import { TypeImageFragment } from 'graphql/requests/images/fragments/ImageFragment.generated';
import { TypeVideoTokenFragment } from 'graphql/requests/products/fragments/VideoTokenFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import { RefObject, createRef, useEffect, useState } from 'react';
import { useSwipeable } from 'react-swipeable';
import { twJoin } from 'tailwind-merge';
import { twMergeCustom } from 'utils/twMerge';
import { useKeypress } from 'utils/useKeyPress';

type ModalGalleryProps = {
    items: (TypeVideoTokenFragment | TypeImageFragment | TypeFileFragment)[];
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
    const isFile = selectedGalleryItem.__typename === 'File';

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
        <div className="fixed inset-0 z-maximum flex select-none flex-col bg-background p-2" onClick={onCloseModal}>
            <div className="flex w-full flex-1 flex-col justify-center">
                <div className="relative my-auto flex max-h-[80dvh] flex-1 items-center justify-center" {...handlers}>
                    <SpinnerIcon className="absolute -z-above w-16 text-textInverted opacity-50" />

                    {isImage && (
                        <Image
                            key={selectedIndex}
                            fill
                            alt={selectedGalleryItem.name || `${galleryName}-${selectedIndex}`}
                            className="max-h-full object-contain"
                            draggable={false}
                            sizes="(max-width: 768px) 100vw, 1200px"
                            src={selectedGalleryItem.url}
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

                    {isFile && (
                        <Image
                            key={selectedIndex}
                            fill
                            alt={selectedGalleryItem.anchorText || `${galleryName}-${selectedIndex}`}
                            className="max-h-full object-contain"
                            draggable={false}
                            hash={selectedGalleryItem.url.split('?')[1]}
                            sizes="(max-width: 768px) 100vw, 1200px"
                            src={selectedGalleryItem.url.split('?')[0]}
                            onLoad={() => setIsLoaded(true)}
                        />
                    )}
                </div>

                {isImage && selectedGalleryItem.name && (
                    <div className="mt-2 text-center text-textInverted">{selectedGalleryItem.name}</div>
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
            'inline-flex items-center justify-center rounded-full bg-backgroundAccentLess p-2 text-text transition-all hover:cursor-pointer hover:text-textAccent',
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
                className={twJoin('h-8 w-8', isLeft && 'rotate-180')}
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
