import { PlayIcon } from 'components/Basic/Icon/PlayIcon';
import { Image } from 'components/Basic/Image/Image';
import { YouTubeThumbnail } from 'components/Basic/YouTubeThumbnail/YouTubeThumbnail';
import { TIDs } from 'cypress/tids';
import { useEffect, useRef } from 'react';
import { twJoin } from 'tailwind-merge';
import useTranslation from 'utils/i18n/useTranslationWrapper';

import { ProductDetailGalleryItem } from './ProductDetailGallery.types';

type ProductDetailGalleryThumbnailsProps = {
    galleryItems: ProductDetailGalleryItem[];
    selectedIndex: number;
    onOpenGallery: (initialIndex: number) => void;
};

const VISIBLE_THUMBNAIL_COUNT = 5;

export const ProductDetailGalleryThumbnails: FC<ProductDetailGalleryThumbnailsProps> = ({
    galleryItems,
    selectedIndex,
    onOpenGallery,
}) => {
    const { t } = useTranslation();
    const thumbnailRefs = useRef<Array<HTMLButtonElement | null>>([]);
    const hiddenGalleryItemsCount = galleryItems.length - VISIBLE_THUMBNAIL_COUNT;

    useEffect(() => {
        thumbnailRefs.current[selectedIndex]?.scrollIntoView?.({
            behavior: 'smooth',
            block: 'nearest',
            inline: 'nearest',
        });
    }, [selectedIndex]);

    return (
        <ul
            aria-label={t('Gallery thumbnails', { ns: 'accessibility' })}
            className="hide-scrollbar mx-auto flex w-fit max-w-full gap-2 overflow-x-auto overscroll-x-contain"
        >
            {galleryItems.slice(0, VISIBLE_THUMBNAIL_COUNT).map((galleryItem, index) => {
                const isImage = galleryItem.__typename === 'Image';
                const isVideo = galleryItem.__typename === 'VideoToken';
                const galleryItemKey = isImage ? galleryItem.url : galleryItem.token;
                const isSelected = index === selectedIndex;

                return (
                    <li key={`${galleryItem.__typename}-${galleryItemKey}-${index}`} className="shrink-0">
                        <button
                            aria-current={isSelected ? 'true' : undefined}
                            aria-label={t('Open item {{ current }} of {{ total }} in gallery', {
                                ns: 'accessibility',
                                current: index + 1,
                                total: galleryItems.length,
                            })}
                            className={twJoin(
                                'relative flex size-12 shrink-0 cursor-pointer items-center justify-center overflow-hidden rounded-lg border-2 border-transparent bg-background-more outline-hidden transition-colors hover:border-border-default focus-visible:outline-2 focus-visible:outline-border-default focus-visible:-outline-offset-2 sm:size-16',
                                isSelected && 'border-border-accent',
                            )}
                            ref={(element) => {
                                thumbnailRefs.current[index] = element;
                            }}
                            tabIndex={0}
                            title={t('View product image')}
                            type="button"
                            onClick={() => onOpenGallery(index)}
                        >
                            {isImage && (
                                <Image
                                    alt=""
                                    className="size-full object-contain object-center p-1 mix-blend-multiply"
                                    height={64}
                                    src={galleryItem.url}
                                    tid={TIDs.product_gallery_image}
                                    width={64}
                                />
                            )}

                            {isVideo && (
                                <>
                                    <YouTubeThumbnail
                                        alt=""
                                        className="size-full object-contain object-center p-1 mix-blend-multiply"
                                        height={64}
                                        tid={TIDs.product_gallery_video}
                                        videoId={galleryItem.token}
                                        width={64}
                                    />
                                    <span className="absolute inset-0 flex items-center justify-center">
                                        <PlayIcon className="size-4 rounded-full bg-background-accent text-text-inverted sm:size-8" />
                                    </span>
                                </>
                            )}
                        </button>
                    </li>
                );
            })}

            {hiddenGalleryItemsCount > 0 && (
                <li className="shrink-0">
                    <button
                        aria-label={t('Open {{ count }} more items in gallery', {
                            ns: 'accessibility',
                            count: hiddenGalleryItemsCount,
                        })}
                        className="flex size-12 cursor-pointer items-center justify-center rounded-lg bg-background-more px-1 font-semibold text-link-default outline-hidden transition-colors hover:text-link-hovered focus-visible:outline-2 focus-visible:outline-border-default focus-visible:-outline-offset-2 sm:size-16"
                        title={t('More')}
                        type="button"
                        onClick={() => onOpenGallery(VISIBLE_THUMBNAIL_COUNT)}
                    >
                        +{hiddenGalleryItemsCount}
                    </button>
                </li>
            )}
        </ul>
    );
};
