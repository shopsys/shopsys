import { PlayIcon } from 'components/Basic/Icon/PlayIcon';
import { Image } from 'components/Basic/Image/Image';
import { YouTubeThumbnail } from 'components/Basic/YouTubeThumbnail/YouTubeThumbnail';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { generateProductImageAlt } from 'utils/productAltText';

import { ProductDetailGalleryItem } from './ProductDetailGallery.types';

type ProductDetailGallerySlideProps = {
    galleryItem: ProductDetailGalleryItem;
    index: number;
    isLoaded: boolean;
    isSelected: boolean;
    productName: string;
    categoryName?: string;
    hasGalleryMovedRef: React.MutableRefObject<boolean>;
    pointerStartRef: React.MutableRefObject<{ x: number; y: number } | null>;
    onOpenGallery: (initialIndex: number) => void;
};

const SWIPE_THRESHOLD = 20;

export const ProductDetailGallerySlide: FC<ProductDetailGallerySlideProps> = ({
    galleryItem,
    index,
    isLoaded,
    isSelected,
    productName,
    categoryName,
    hasGalleryMovedRef,
    pointerStartRef,
    onOpenGallery,
}) => {
    const { t } = useTranslation();
    const isImage = galleryItem.__typename === 'Image';
    const isVideo = galleryItem.__typename === 'VideoToken';

    const handlePointerMove = (event: React.PointerEvent) => {
        const pointerStart = pointerStartRef.current;

        if (
            pointerStart !== null &&
            Math.hypot(event.clientX - pointerStart.x, event.clientY - pointerStart.y) >= SWIPE_THRESHOLD
        ) {
            hasGalleryMovedRef.current = true;
        }
    };

    return (
        <button
            aria-label={t('Open image gallery of {{ productName }}', {
                ns: 'accessibility',
                productName,
            })}
            className="relative flex w-full cursor-pointer items-center justify-center rounded-lg"
            data-focus-style="ring"
            tabIndex={isSelected ? 0 : -1}
            title={t('View product image')}
            type="button"
            onClick={(event) => {
                if (event.detail === 0 || !hasGalleryMovedRef.current) {
                    onOpenGallery(index);
                }
            }}
            onPointerCancel={() => {
                pointerStartRef.current = null;
            }}
            onPointerDown={(event) => {
                pointerStartRef.current = { x: event.clientX, y: event.clientY };
                hasGalleryMovedRef.current = false;
            }}
            onPointerMove={handlePointerMove}
            onPointerUp={() => {
                pointerStartRef.current = null;
            }}
        >
            {!isLoaded && (
                <span
                    aria-hidden="true"
                    className="vl:size-125 h-80 w-full rounded-lg bg-skeleton-default motion-safe:animate-pulse lg:h-125"
                />
            )}

            {isLoaded && isImage && (
                <Image
                    priority={index === 0}
                    alt={galleryItem.name || generateProductImageAlt(productName, categoryName)}
                    className="vl:size-125 h-80 w-full object-contain mix-blend-multiply lg:h-125"
                    draggable={false}
                    height={500}
                    sizes="(max-width: 1023px) 100vw, 500px"
                    src={galleryItem.url}
                    width={500}
                />
            )}

            {isLoaded && isVideo && (
                <>
                    <YouTubeThumbnail
                        alt={galleryItem.description ?? t('Product Video')}
                        className="vl:size-125 h-80 w-full object-contain lg:h-125"
                        draggable={false}
                        height={500}
                        sizes="(max-width: 1023px) 100vw, 500px"
                        videoId={galleryItem.token}
                        width={500}
                    />
                    <span className="absolute inset-0 flex items-center justify-center">
                        <PlayIcon className="size-16 rounded-full bg-background-accent text-text-inverted" />
                    </span>
                </>
            )}
        </button>
    );
};
