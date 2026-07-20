import { Image } from 'components/Basic/Image/Image';
import { MediaCarouselPagination } from 'components/Basic/MediaCarousel/MediaCarouselPagination';
import { MediaCarouselTrack, MediaCarouselTrackHandle } from 'components/Basic/MediaCarousel/MediaCarouselTrack';
import { TypeImageFragment } from 'graphql/requests/images/fragments/ImageFragment.generated';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { useProductListItemImagesQuery } from 'graphql/requests/products/queries/ProductListItemImagesQuery.generated';
import { forwardRef, useCallback, useEffect, useImperativeHandle, useMemo, useRef, useState } from 'react';
import { twJoin } from 'tailwind-merge';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type ProductListItemGalleryProps = {
    imageAlt: string;
    imageCount: number;
    imageSize: number;
    product: TypeListedProductFragment;
};

export type ProductListItemGalleryHandle = {
    prepareGallery: () => void;
    selectNextImage: () => void;
    selectPreviousImage: () => void;
};

const GALLERY_HOVER_DELAY_MS = 250;
const SWIPE_THRESHOLD_PX = 20;

export const ProductListItemGallery = forwardRef<ProductListItemGalleryHandle, ProductListItemGalleryProps>(
    ({ imageAlt, imageCount, imageSize, product }, ref) => {
        const { t } = useTranslation();
        const carouselTrackRef = useRef<MediaCarouselTrackHandle>(null);
        const galleryHoverTimeoutRef = useRef<ReturnType<typeof setTimeout> | null>(null);
        const hasGalleryBeenRequestedRef = useRef(false);
        const hasGalleryMovedRef = useRef(false);
        const pointerStartRef = useRef<{ x: number; y: number } | null>(null);
        const [selectedIndex, setSelectedIndex] = useState(0);
        const [{ data: galleryData }, fetchGallery] = useProductListItemImagesQuery({
            pause: true,
            requestPolicy: 'cache-first',
            variables: { uuid: product.uuid },
        });

        const prepareGallery = useCallback(() => {
            if (hasGalleryBeenRequestedRef.current) {
                return;
            }

            hasGalleryBeenRequestedRef.current = true;
            fetchGallery({ requestPolicy: 'cache-first' });
        }, [fetchGallery]);

        const resolvedGalleryImages = galleryData?.product?.images;
        const galleryItemCount = resolvedGalleryImages?.length ?? imageCount;
        const galleryItems = useMemo<TypeImageFragment[]>(() => {
            const mainImage: TypeImageFragment = {
                __typename: 'Image',
                name: null,
                url: product.mainImage?.url ?? '',
            };

            return Array.from({ length: galleryItemCount }, (_, index) => {
                if (index === 0) {
                    return resolvedGalleryImages?.[0] ?? mainImage;
                }

                return (
                    resolvedGalleryImages?.[index] ?? {
                        __typename: 'Image',
                        name: null,
                        url: '',
                    }
                );
            });
        }, [galleryItemCount, product.mainImage?.url, resolvedGalleryImages]);

        const selectImage = useCallback(
            (index: number) => {
                prepareGallery();
                carouselTrackRef.current?.scrollToIndex(index);
            },
            [prepareGallery],
        );

        useImperativeHandle(
            ref,
            () => ({
                prepareGallery,
                selectNextImage: () => selectImage(selectedIndex < galleryItemCount - 1 ? selectedIndex + 1 : 0),
                selectPreviousImage: () => selectImage(selectedIndex > 0 ? selectedIndex - 1 : galleryItemCount - 1),
            }),
            [galleryItemCount, prepareGallery, selectImage, selectedIndex],
        );

        useEffect(() => {
            hasGalleryBeenRequestedRef.current = false;
            setSelectedIndex(0);
        }, [product.uuid]);

        useEffect(
            () => () => {
                if (galleryHoverTimeoutRef.current !== null) {
                    clearTimeout(galleryHoverTimeoutRef.current);
                }
            },
            [],
        );

        const handlePointerEnter = (event: React.PointerEvent) => {
            if (event.pointerType !== 'mouse') {
                return;
            }

            galleryHoverTimeoutRef.current = setTimeout(prepareGallery, GALLERY_HOVER_DELAY_MS);
        };

        const handlePointerLeave = () => {
            if (galleryHoverTimeoutRef.current !== null) {
                clearTimeout(galleryHoverTimeoutRef.current);
                galleryHoverTimeoutRef.current = null;
            }
        };

        const handlePointerMove = (event: React.PointerEvent) => {
            const pointerStart = pointerStartRef.current;

            if (
                pointerStart !== null &&
                Math.hypot(event.clientX - pointerStart.x, event.clientY - pointerStart.y) >= SWIPE_THRESHOLD_PX
            ) {
                hasGalleryMovedRef.current = true;
            }
        };

        return (
            <div
                className="flex w-full flex-col items-center gap-2"
                onClickCapture={(event) => {
                    if (event.detail !== 0 && hasGalleryMovedRef.current) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                }}
                onPointerCancel={() => {
                    pointerStartRef.current = null;
                }}
                onPointerDown={(event) => {
                    pointerStartRef.current = { x: event.clientX, y: event.clientY };
                    hasGalleryMovedRef.current = false;
                    prepareGallery();
                }}
                onPointerEnter={handlePointerEnter}
                onPointerLeave={handlePointerLeave}
                onPointerMove={handlePointerMove}
                onPointerUp={() => {
                    pointerStartRef.current = null;
                }}
                onWheel={(event) => {
                    if (Math.abs(event.deltaX) > Math.abs(event.deltaY)) {
                        prepareGallery();
                    }
                }}
            >
                <MediaCarouselTrack
                    ariaLabel={t('Product media gallery', { ns: 'accessibility' })}
                    className="shrink-0 grid-rows-1"
                    itemClassName="bg-background-more vl:group-hover:bg-background-default"
                    items={galleryItems}
                    ref={carouselTrackRef}
                    selectedIndex={selectedIndex}
                    style={{ height: imageSize }}
                    onSelectedIndexChange={setSelectedIndex}
                    onTrackScroll={() => {
                        hasGalleryMovedRef.current = true;
                        prepareGallery();
                    }}
                    renderItem={(galleryItem, _index, isLoaded) =>
                        isLoaded && galleryItem.__typename === 'Image' && galleryItem.url ? (
                            <Image
                                alt={galleryItem.name || imageAlt}
                                className="h-full w-full object-contain mix-blend-multiply"
                                draggable={false}
                                height={imageSize}
                                src={galleryItem.url}
                                width={imageSize}
                            />
                        ) : (
                            <span className="size-full rounded-lg bg-skeleton-default motion-safe:animate-pulse" />
                        )
                    }
                />

                <div
                    className={twJoin(
                        'pointer-events-none h-2 opacity-100 transition-opacity duration-200 motion-reduce:transition-none',
                        'vl:opacity-0 vl:group-focus-within:opacity-100 vl:group-hover:opacity-100',
                    )}
                >
                    <MediaCarouselPagination itemCount={galleryItemCount} selectedIndex={selectedIndex} />
                </div>
            </div>
        );
    },
);

ProductListItemGallery.displayName = 'ProductListItemGallery';
