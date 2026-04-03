import { AnimateSlideDiv } from 'components/Basic/Animations/AnimateSlideDiv';
import { SpinnerIcon } from 'components/Basic/Icon/SpinnerIcon';
import { Image } from 'components/Basic/Image/Image';
import { AnimatePresence } from 'framer-motion';
import { TypeFileFragment } from 'graphql/requests/files/fragments/FileFragment.generated';
import { TypeImageFragment } from 'graphql/requests/images/fragments/ImageFragment.generated';
import { TypeVideoTokenFragment } from 'graphql/requests/products/fragments/VideoTokenFragment.generated';
import { createRef, forwardRef, RefObject, useEffect, useMemo, useRef, useState } from 'react';
import { RemoveScroll } from 'react-remove-scroll';
import { useSwipeable } from 'react-swipeable';
import { twJoin } from 'tailwind-merge';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';
import { useFocusTrap } from 'utils/useFocusTrap';
import { useKeypress } from 'utils/useKeyPress';
import { ModalGalleryCarousel } from './ModalGalleryCarousel';

type ModalGalleryProps = {
    items: (TypeVideoTokenFragment | TypeImageFragment | TypeFileFragment)[];
    initialIndex: number;
    galleryName: string;
    onCloseModal: () => void;
};

export const ModalGallery: FC<ModalGalleryProps> = ({ initialIndex, items, galleryName, onCloseModal }) => {
    const { t } = useTranslation();
    const modalRef = useRef<HTMLDivElement>(null);
    const nextButtonRef = useRef<HTMLButtonElement>(null);

    const [selectedIndex, setSelectedIndex] = useState(initialIndex);
    const [slideDirection, setSlideDirection] = useState<'left' | 'right'>('right');
    const itemsRefs = useMemo<Array<RefObject<HTMLLIElement | null>>>(
        () =>
            Array(items.length)
                .fill(null)
                .map(() => createRef()),
        [items.length],
    );
    const [isLoaded, setIsLoaded] = useState(false);

    const selectedGalleryItem = items[selectedIndex];

    const isImage = selectedGalleryItem.__typename === 'Image';
    const isVideo = selectedGalleryItem.__typename === 'VideoToken';
    const isFile = selectedGalleryItem.__typename === 'File';

    useEffect(() => {
        setIsLoaded(isVideo);
    }, [selectedIndex, isVideo]);

    const lastItemIndex = items.length - 1;

    const isCarouselDisplayed = items.length > 1 && (!isImage || isLoaded);

    const selectPreviousItem = () => {
        setSlideDirection('left');
        setSelectedIndex((currentSelectedIndex) =>
            currentSelectedIndex > 0 ? currentSelectedIndex - 1 : lastItemIndex,
        );
    };

    const selectNextItem = () => {
        setSlideDirection('right');
        setSelectedIndex((currentSelectedIndex) =>
            currentSelectedIndex < lastItemIndex ? currentSelectedIndex + 1 : 0,
        );
    };

    useEffect(() => {
        if (isCarouselDisplayed) {
            itemsRefs[selectedIndex].current?.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest',
                inline: 'start',
            });
        }
    }, [selectedIndex, itemsRefs, isCarouselDisplayed]);

    useEffect(() => {
        // Focus on next button when modal opens
        nextButtonRef.current?.focus();
    }, []);

    useKeypress('Escape', onCloseModal);
    useKeypress('ArrowRight', selectNextItem);
    useKeypress('ArrowLeft', selectPreviousItem);

    const handlers = useSwipeable({
        onSwipedLeft: selectNextItem,
        onSwipedRight: selectPreviousItem,
        trackMouse: true,
    });

    useFocusTrap(modalRef);

    return (
        <RemoveScroll>
            <div
                aria-label={t('Gallery', { ns: 'accessibility' })}
                aria-modal="true"
                className="fixed inset-0 z-maximum flex select-none flex-col bg-background-default p-2 focus-visible:outline-4 focus-visible:outline-background-accent focus-visible:-outline-offset-2"
                ref={modalRef}
                role="dialog"
            >
                <section
                    className="relative my-auto flex max-h-[80dvh] flex-1 items-center justify-center"
                    {...handlers}
                    aria-label={t('Gallery content', { ns: 'accessibility' })}
                >
                    {!isLoaded && (
                        <SpinnerIcon
                            aria-hidden="true"
                            className="absolute -z-above w-16 text-text-inverted opacity-50"
                        />
                    )}
                    <AnimatePresence initial={false}>
                        {isImage && (
                            <AnimateSlideDiv
                                className="block! relative size-full"
                                direction={slideDirection}
                                keyName={`image-${selectedIndex}`}
                            >
                                <Image
                                    key={selectedIndex}
                                    fill
                                    alt={selectedGalleryItem.name || `${galleryName}-${selectedIndex}`}
                                    className="max-h-full object-contain"
                                    draggable={false}
                                    src={selectedGalleryItem.url}
                                    onLoad={() => setIsLoaded(true)}
                                />
                            </AnimateSlideDiv>
                        )}
                    </AnimatePresence>

                    <AnimatePresence initial={false}>
                        {isVideo && (
                            <AnimateSlideDiv
                                className="block! relative size-full"
                                direction={slideDirection}
                                keyName={`video-${selectedIndex}`}
                            >
                                <iframe
                                    allowFullScreen
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                    className="aspect-video max-h-full w-full max-w-xl md:max-w-[1500px]"
                                    src={`https://www.youtube.com/embed/${selectedGalleryItem.token}?autoplay=1&mute=1`}
                                    title={selectedGalleryItem.description ?? t('Product Video')}
                                    aria-label={
                                        selectedGalleryItem.description ?? t('Product Video', { ns: 'accessibility' })
                                    }
                                />
                            </AnimateSlideDiv>
                        )}
                    </AnimatePresence>

                    <AnimatePresence initial={false}>
                        {isFile && (
                            <AnimateSlideDiv
                                className="block! relative size-full"
                                direction={slideDirection}
                                keyName={`file-${selectedIndex}`}
                            >
                                <Image
                                    key={selectedIndex}
                                    fill
                                    alt={selectedGalleryItem.anchorText || `${galleryName}-${selectedIndex}`}
                                    className="max-h-full object-contain"
                                    draggable={false}
                                    hash={selectedGalleryItem.url.split('?')[1]}
                                    src={selectedGalleryItem.url.split('?')[0]}
                                    onLoad={() => setIsLoaded(true)}
                                />
                            </AnimateSlideDiv>
                        )}
                    </AnimatePresence>
                </section>

                {isImage && selectedGalleryItem.name && (
                    <p className="mt-2 text-center text-text-inverted">{selectedGalleryItem.name}</p>
                )}

                <div
                    aria-label={t('Gallery navigation', { ns: 'accessibility' })}
                    className="mt-4 flex items-center justify-center gap-8"
                    role="toolbar"
                >
                    <ButtonArrow position="left" title={t('Previous')} onClick={selectPreviousItem} />
                    <ButtonArrow position="right" ref={nextButtonRef} title={t('Next')} onClick={selectNextItem} />
                </div>

                <div
                    aria-label={t('Gallery thumbnails', { ns: 'accessibility' })}
                    className="mt-4 flex items-center justify-center gap-2"
                    role="tablist"
                >
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
        </RemoveScroll>
    );
};

type FloatingButtonProps = {
    onClick: () => void;
    title?: string;
    className?: string;
    children?: React.ReactNode;
};

const FloatingButton = forwardRef<HTMLButtonElement, FloatingButtonProps>(
    ({ className, children, onClick, ...buttonProps }, ref) => (
        <button
            ref={ref}
            tabIndex={0}
            type="button"
            className={twMergeCustom(
                'inline-flex cursor-pointer items-center justify-center rounded-full bg-background-accent-less p-2 text-text-default transition-all hover:text-text-accent',
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
    ),
);

FloatingButton.displayName = 'FloatingButton';

const ButtonArrow = forwardRef<HTMLButtonElement, FloatingButtonProps & { position: 'left' | 'right' }>(
    ({ position, title, ...floatingButtonProps }, ref) => {
        const isLeft = position === 'left';

        return (
            <FloatingButton
                className={twJoin('', isLeft ? 'left-2' : 'right-2')}
                ref={ref}
                title={title}
                {...floatingButtonProps}
            >
                <svg
                    aria-hidden="true"
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
    },
);

ButtonArrow.displayName = 'ButtonArrow';

const ButtonClose: FC<FloatingButtonProps> = ({ title, ...floatingButtonProps }) => (
    <FloatingButton className="absolute top-2 right-2" {...floatingButtonProps} title={title}>
        <svg
            aria-hidden="true"
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
