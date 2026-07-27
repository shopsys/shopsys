import { Image } from 'components/Basic/Image/Image';
import { MediaCarouselNavigationButton } from 'components/Basic/MediaCarousel/MediaCarouselNavigationButton';
import { MediaCarouselPagination } from 'components/Basic/MediaCarousel/MediaCarouselPagination';
import { MediaCarouselTrack, MediaCarouselTrackHandle } from 'components/Basic/MediaCarousel/MediaCarouselTrack';
import { getYouTubeThumbnailUrl } from 'components/Basic/YouTubeThumbnail/YouTubeThumbnail';
import { ProductFlags } from 'components/Blocks/Product/ProductFlags';
import { TIDs } from 'cypress/tids';
import { TypeSimpleFlagFragment } from 'graphql/requests/flags/fragments/SimpleFlagFragment.generated';
import { useCallback, useEffect, useRef, useState } from 'react';
import { twJoin } from 'tailwind-merge';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { generateProductImageAlt } from 'utils/productAltText';

import { ProductDetailGalleryItem } from './ProductDetailGallery.types';
import { ProductDetailGallerySlide } from './ProductDetailGallerySlide';

type ProductDetailGalleryMainProps = {
    galleryItems: ProductDetailGalleryItem[];
    selectedIndex: number;
    productName: string;
    flags: TypeSimpleFlagFragment[];
    percentageDiscount: number | null;
    categoryName?: string;
    onOpenGallery: (initialIndex: number) => void;
    onSelectedIndexChange: (index: number) => void;
};

const POSITION_COUNTER_HIDE_DELAY_MS = 3000;

export const ProductDetailGalleryMain: FC<ProductDetailGalleryMainProps> = ({
    galleryItems,
    selectedIndex,
    productName,
    flags,
    percentageDiscount,
    categoryName,
    onOpenGallery,
    onSelectedIndexChange,
}) => {
    const { t } = useTranslation();
    const carouselTrackRef = useRef<MediaCarouselTrackHandle>(null);
    const hasGalleryMovedRef = useRef(false);
    const pointerStartRef = useRef<{ x: number; y: number } | null>(null);
    const positionCounterHideTimeoutRef = useRef<ReturnType<typeof setTimeout> | null>(null);
    const [isPositionCounterVisible, setIsPositionCounterVisible] = useState(false);

    const handleTrackScroll = useCallback(() => {
        hasGalleryMovedRef.current = true;
        setIsPositionCounterVisible(true);

        if (positionCounterHideTimeoutRef.current !== null) {
            clearTimeout(positionCounterHideTimeoutRef.current);
        }

        positionCounterHideTimeoutRef.current = setTimeout(() => {
            setIsPositionCounterVisible(false);
        }, POSITION_COUNTER_HIDE_DELAY_MS);
    }, []);

    useEffect(
        () => () => {
            if (positionCounterHideTimeoutRef.current !== null) {
                clearTimeout(positionCounterHideTimeoutRef.current);
            }
        },
        [],
    );

    const selectedGalleryItem = galleryItems[selectedIndex];
    const hasMultipleItems = galleryItems.length > 1;
    const lastItemIndex = galleryItems.length - 1;
    const selectedItemSrc =
        selectedGalleryItem?.__typename === 'Image'
            ? selectedGalleryItem.url
            : selectedGalleryItem?.__typename === 'VideoToken'
              ? getYouTubeThumbnailUrl(selectedGalleryItem.token)
              : undefined;

    const selectGalleryItem = (index: number) => {
        carouselTrackRef.current?.scrollToIndex(index);
    };

    const selectPreviousItem = () => {
        selectGalleryItem(selectedIndex > 0 ? selectedIndex - 1 : lastItemIndex);
    };

    const selectNextItem = () => {
        selectGalleryItem(selectedIndex < lastItemIndex ? selectedIndex + 1 : 0);
    };

    const selectedItemPositionLabel = t('{{ slideName }}, slide {{ current }} of {{ total }}', {
        slideName: productName,
        current: selectedIndex + 1,
        total: galleryItems.length,
    });

    return (
        <div
            className="flex w-full min-w-0 flex-col items-center gap-4"
            data-src={selectedItemSrc}
            data-tid={TIDs.product_detail_main_image}
        >
            <div className="relative flex w-full min-w-0 justify-center">
                {galleryItems.length > 0 ? (
                    <MediaCarouselTrack
                        ariaLabel={t('Product media gallery', { ns: 'accessibility' })}
                        initialIndex={0}
                        items={galleryItems}
                        ref={carouselTrackRef}
                        selectedIndex={selectedIndex}
                        onSelectedIndexChange={onSelectedIndexChange}
                        onTrackScroll={handleTrackScroll}
                        renderItem={(galleryItem, index, isLoaded, isSelected) =>
                            galleryItem.__typename === 'File' ? null : (
                                <ProductDetailGallerySlide
                                    categoryName={categoryName}
                                    galleryItem={galleryItem}
                                    hasGalleryMovedRef={hasGalleryMovedRef}
                                    index={index}
                                    isLoaded={isLoaded}
                                    isSelected={isSelected}
                                    pointerStartRef={pointerStartRef}
                                    productName={productName}
                                    onOpenGallery={onOpenGallery}
                                />
                            )
                        }
                    />
                ) : (
                    <Image
                        priority
                        alt={generateProductImageAlt(productName, categoryName)}
                        className="vl:size-125 h-80 w-full object-contain lg:h-125"
                        height={500}
                        sizes="(max-width: 1023px) 100vw, 500px"
                        src={undefined}
                        width={500}
                    />
                )}

                {hasMultipleItems && (
                    <>
                        <MediaCarouselNavigationButton
                            className="absolute top-1/2 left-2 z-above vl:flex hidden -translate-y-1/2 bg-background-default p-2 shadow-md transition hover:text-icon-accent focus-visible:bg-orange-500"
                            direction="previous"
                            iconClassName="size-8"
                            title={t('Previous')}
                            onClick={selectPreviousItem}
                        />
                        <MediaCarouselNavigationButton
                            className="absolute top-1/2 right-2 z-above vl:flex hidden -translate-y-1/2 bg-background-default p-2 shadow-md transition hover:text-icon-accent focus-visible:bg-orange-500"
                            direction="next"
                            iconClassName="size-8"
                            title={t('Next')}
                            onClick={selectNextItem}
                        />

                        <span
                            aria-label={selectedItemPositionLabel}
                            aria-live="polite"
                            className={twJoin(
                                'absolute top-0 right-0 z-above rounded-full bg-background-dark/40 px-2 py-1 text-text-inverted text-xs backdrop-blur-xs transition-opacity duration-200 motion-reduce:transition-none',
                                isPositionCounterVisible ? 'opacity-100' : 'opacity-0',
                            )}
                        >
                            <span aria-hidden="true">
                                {selectedIndex + 1} / {galleryItems.length}
                            </span>
                        </span>
                    </>
                )}

                <ProductFlags
                    flags={flags}
                    percentageDiscount={percentageDiscount}
                    variant="detail"
                    visibleItemsConfig={{ flags: true, discount: true }}
                />
            </div>

            {hasMultipleItems && (
                <MediaCarouselPagination itemCount={galleryItems.length} selectedIndex={selectedIndex} />
            )}
        </div>
    );
};
