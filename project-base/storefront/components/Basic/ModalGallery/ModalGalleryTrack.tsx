import { SpinnerIcon } from 'components/Basic/Icon/SpinnerIcon';
import { Image } from 'components/Basic/Image/Image';
import {
    MediaCarouselItem,
    MediaCarouselTrack,
    MediaCarouselTrackHandle,
} from 'components/Basic/MediaCarousel/MediaCarouselTrack';
import { ModalGalleryVideo } from 'components/Basic/ModalGallery/ModalGalleryVideo';
import { forwardRef, useState } from 'react';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type ModalGalleryTrackProps = {
    items: MediaCarouselItem[];
    initialIndex: number;
    selectedIndex: number;
    galleryName: string;
    onSelectedIndexChange: (index: number) => void;
};

export const ModalGalleryTrack = forwardRef<MediaCarouselTrackHandle, ModalGalleryTrackProps>(
    ({ items, initialIndex, selectedIndex, galleryName, onSelectedIndexChange }, ref) => {
        const { t } = useTranslation();
        const [loadedMediaIndexes, setLoadedMediaIndexes] = useState<Set<number>>(() => new Set());

        const markMediaAsLoaded = (index: number) => {
            setLoadedMediaIndexes((currentLoadedMediaIndexes) => {
                if (currentLoadedMediaIndexes.has(index)) {
                    return currentLoadedMediaIndexes;
                }

                const nextLoadedMediaIndexes = new Set(currentLoadedMediaIndexes);
                nextLoadedMediaIndexes.add(index);

                return nextLoadedMediaIndexes;
            });
        };

        return (
            <MediaCarouselTrack
                ariaLabel={t('Gallery content', { ns: 'accessibility' })}
                className="h-full"
                initialIndex={initialIndex}
                itemClassName="h-full"
                items={items}
                ref={ref}
                selectedIndex={selectedIndex}
                onSelectedIndexChange={onSelectedIndexChange}
                renderItem={(galleryItem, index, isLoaded, isSelected, isTrackScrolling) => {
                    const isImage = galleryItem.__typename === 'Image';
                    const isVideo = galleryItem.__typename === 'VideoToken';
                    const isFile = galleryItem.__typename === 'File';
                    const isMediaLoaded = isVideo || loadedMediaIndexes.has(index);

                    return (
                        <div className="relative flex size-full items-center justify-center">
                            {!isLoaded && (
                                <span
                                    aria-hidden="true"
                                    className="size-full max-h-[80dvh] max-w-375 rounded-lg bg-skeleton-default motion-safe:animate-pulse"
                                />
                            )}

                            {isLoaded && !isMediaLoaded && (
                                <SpinnerIcon
                                    aria-hidden="true"
                                    className="absolute z-above size-12 text-icon opacity-50"
                                />
                            )}

                            {isLoaded && isImage && (
                                <Image
                                    fill
                                    alt={galleryItem.name || `${galleryName}-${index}`}
                                    className="max-h-full object-contain mix-blend-multiply"
                                    draggable={false}
                                    hash={galleryItem.url.split('?')[1]}
                                    sizes="100vw"
                                    src={galleryItem.url.split('?')[0]}
                                    onLoad={() => markMediaAsLoaded(index)}
                                />
                            )}

                            {isLoaded && isVideo && (
                                <ModalGalleryVideo
                                    description={galleryItem.description}
                                    isSelected={isSelected}
                                    isTrackScrolling={isTrackScrolling}
                                    videoId={galleryItem.token}
                                />
                            )}

                            {isLoaded && isFile && (
                                <Image
                                    fill
                                    alt={galleryItem.anchorText || `${galleryName}-${index}`}
                                    className="max-h-full object-contain mix-blend-multiply"
                                    draggable={false}
                                    hash={galleryItem.url.split('?')[1]}
                                    sizes="100vw"
                                    src={galleryItem.url.split('?')[0]}
                                    onLoad={() => markMediaAsLoaded(index)}
                                />
                            )}
                        </div>
                    );
                }}
            />
        );
    },
);

ModalGalleryTrack.displayName = 'ModalGalleryTrack';
