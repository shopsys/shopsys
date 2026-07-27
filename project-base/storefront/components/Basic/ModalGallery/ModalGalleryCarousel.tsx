import { PlayIcon } from 'components/Basic/Icon/PlayIcon';
import { Image } from 'components/Basic/Image/Image';
import { MediaCarouselItem } from 'components/Basic/MediaCarousel/MediaCarouselTrack';
import { YouTubeThumbnail } from 'components/Basic/YouTubeThumbnail/YouTubeThumbnail';
import { createRef, RefObject, useEffect, useMemo } from 'react';
import { twJoin } from 'tailwind-merge';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type ModalGalleryCarouselProps = {
    items: MediaCarouselItem[];
    selectedIndex: number;
    galleryName: string;
    onSelectItem: (index: number) => void;
};

export const ModalGalleryCarousel: FC<ModalGalleryCarouselProps> = ({
    items,
    selectedIndex,
    galleryName,
    onSelectItem,
}) => {
    const { t } = useTranslation();
    const itemsRefs = useMemo<Array<RefObject<HTMLLIElement | null>>>(
        () =>
            Array(items.length)
                .fill(null)
                .map(() => createRef()),
        [items.length],
    );

    useEffect(() => {
        itemsRefs[selectedIndex]?.current?.scrollIntoView?.({
            behavior: 'smooth',
            block: 'nearest',
            inline: 'start',
        });
    }, [itemsRefs, selectedIndex]);

    return (
        <div aria-label={t('Gallery thumbnails', { ns: 'accessibility' })} className="w-full min-w-0" role="tablist">
            <ul className="hide-scrollbar mx-auto grid w-fit max-w-full snap-x snap-mandatory auto-cols-[80px] grid-flow-col overflow-x-auto overscroll-x-contain">
                {items.map((galleryItem, index) => {
                    const isImage = galleryItem.__typename === 'Image';
                    const isVideo = galleryItem.__typename === 'VideoToken';
                    const isFile = galleryItem.__typename === 'File';
                    const galleryItemKey = isVideo ? galleryItem.token : galleryItem.url;

                    return (
                        <li key={galleryItemKey} ref={itemsRefs[index]}>
                            <button
                                aria-label={t('Select image from gallery', { ns: 'accessibility' })}
                                aria-selected={index === selectedIndex}
                                className={twJoin(
                                    'flex size-20 snap-center items-center justify-center rounded-lg border-2 border-transparent bg-background-more p-1 transition-[border-color,opacity] hover:border-border-default hover:opacity-100',
                                    index === selectedIndex ? 'border-border-accent' : 'cursor-pointer opacity-60',
                                )}
                                role="tab"
                                tabIndex={0}
                                title={t('Select image')}
                                type="button"
                                onClick={(e) => {
                                    e.stopPropagation();
                                    onSelectItem(index);
                                }}
                            >
                                {isImage && (
                                    <Image
                                        alt={galleryItem.name || `${galleryName}-${index}`}
                                        className="max-h-full w-auto object-contain mix-blend-multiply"
                                        draggable={false}
                                        height={80}
                                        src={galleryItem.url}
                                        width={80}
                                    />
                                )}

                                {isVideo && (
                                    <span className="relative inline-flex">
                                        <YouTubeThumbnail
                                            alt={galleryItem.description ?? t('Product Video')}
                                            className="max-h-20 w-auto"
                                            draggable={false}
                                            height={80}
                                            videoId={galleryItem.token}
                                            width={80}
                                        />

                                        <PlayIcon className="absolute top-1/2 left-1/2 size-8 -translate-x-1/2 -translate-y-1/2 rounded-full bg-background-accent text-text-inverted" />
                                    </span>
                                )}

                                {isFile && (
                                    <Image
                                        alt={galleryItem.anchorText || `${galleryName}-${index}`}
                                        className="max-h-full w-auto object-contain"
                                        draggable={false}
                                        hash={galleryItem.url.split('?')[1]}
                                        height={80}
                                        src={galleryItem.url.split('?')[0]}
                                        width={80}
                                    />
                                )}
                            </button>
                        </li>
                    );
                })}
            </ul>
        </div>
    );
};
