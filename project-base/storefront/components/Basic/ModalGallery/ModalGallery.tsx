import { MediaCarouselItem, MediaCarouselTrackHandle } from 'components/Basic/MediaCarousel/MediaCarouselTrack';
import { useEffect, useRef, useState } from 'react';
import { RemoveScroll } from 'react-remove-scroll';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { clamp } from 'utils/numbers/clamp';
import { useFocusTrap } from 'utils/useFocusTrap';
import { useKeypress } from 'utils/useKeyPress';

import { ModalGalleryCarousel } from './ModalGalleryCarousel';
import { ModalGalleryCloseButton, ModalGalleryNavigation } from './ModalGalleryControls';
import { ModalGalleryTrack } from './ModalGalleryTrack';

type ModalGalleryProps = {
    items: MediaCarouselItem[];
    initialIndex: number;
    galleryName: string;
    onCloseModal: () => void;
};

const NON_BREAKING_SPACE = '\u00A0';

export const ModalGallery: FC<ModalGalleryProps> = ({ initialIndex, items, galleryName, onCloseModal }) => {
    const { t } = useTranslation();
    const lastItemIndex = items.length - 1;
    const normalizedInitialIndex = items.length > 0 ? clamp(initialIndex, 0, lastItemIndex) : 0;
    const [selectedIndex, setSelectedIndex] = useState(normalizedInitialIndex);
    const modalRef = useRef<HTMLDivElement>(null);
    const carouselTrackRef = useRef<MediaCarouselTrackHandle>(null);
    const closeButtonRef = useRef<HTMLButtonElement>(null);

    const selectedGalleryItem = items[selectedIndex];
    const hasMultipleItems = items.length > 1;
    const isSelectedItemImage = selectedGalleryItem?.__typename === 'Image';

    const selectItem = (index: number) => {
        carouselTrackRef.current?.scrollToIndex(index);
    };

    const selectPreviousItem = () => {
        if (hasMultipleItems) {
            selectItem(selectedIndex > 0 ? selectedIndex - 1 : lastItemIndex);
        }
    };

    const selectNextItem = () => {
        if (hasMultipleItems) {
            selectItem(selectedIndex < lastItemIndex ? selectedIndex + 1 : 0);
        }
    };

    useEffect(() => {
        closeButtonRef.current?.focus();
    }, []);

    useKeypress('Escape', onCloseModal);
    useKeypress('ArrowRight', selectNextItem);
    useKeypress('ArrowLeft', selectPreviousItem);
    useFocusTrap(modalRef);

    if (selectedGalleryItem === undefined) {
        return null;
    }

    const selectedItemCaption = isSelectedItemImage ? selectedGalleryItem.name : null;

    return (
        <RemoveScroll>
            <div
                aria-label={t('Gallery', { ns: 'accessibility' })}
                aria-modal="true"
                className="fixed inset-0 z-maximum grid h-screen select-none grid-cols-[minmax(0,1fr)] grid-rows-[auto_minmax(0,1fr)_auto] overflow-hidden bg-background-default p-2 outline-hidden supports-[height:100dvh]:h-dvh sm:p-4"
                ref={modalRef}
                role="dialog"
            >
                <header className="z-above flex h-12 items-center justify-end">
                    <ModalGalleryCloseButton ref={closeButtonRef} onClose={onCloseModal} />
                </header>

                <section className="relative flex min-h-0 min-w-0 items-center justify-center">
                    <ModalGalleryTrack
                        galleryName={galleryName}
                        initialIndex={normalizedInitialIndex}
                        items={items}
                        ref={carouselTrackRef}
                        selectedIndex={selectedIndex}
                        onSelectedIndexChange={setSelectedIndex}
                    />

                    {hasMultipleItems && (
                        <ModalGalleryNavigation onNext={selectNextItem} onPrevious={selectPreviousItem} />
                    )}
                </section>

                <footer className="flex min-w-0 flex-col items-center gap-3 pt-3">
                    <p className="h-5 max-w-full truncate text-center text-sm text-text-less">
                        {selectedItemCaption ?? NON_BREAKING_SPACE}
                    </p>

                    {hasMultipleItems && (
                        <ModalGalleryCarousel
                            galleryName={galleryName}
                            items={items}
                            selectedIndex={selectedIndex}
                            onSelectItem={selectItem}
                        />
                    )}
                </footer>
            </div>
        </RemoveScroll>
    );
};
