import { PlayIcon } from 'components/Basic/Icon/PlayIcon';
import { Image } from 'components/Basic/Image/Image';
import { ProductFlags } from 'components/Blocks/Product/ProductFlags';
import { TIDs } from 'cypress/tids';
import { TypeSimpleFlagFragment } from 'graphql/requests/flags/fragments/SimpleFlagFragment.generated';
import { TypeImageFragment } from 'graphql/requests/images/fragments/ImageFragment.generated';
import { TypeVideoTokenFragment } from 'graphql/requests/products/fragments/VideoTokenFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { Fragment, useState } from 'react';
import { twJoin } from 'tailwind-merge';
import { generateProductImageAlt } from 'utils/productAltText';

const ModalGallery = dynamic(() =>
    import('components/Basic/ModalGallery/ModalGallery').then((component) => component.ModalGallery),
);

type ProductDetailGalleryProps = {
    images: TypeImageFragment[];
    productName: string;
    flags: TypeSimpleFlagFragment[];
    videoIds?: TypeVideoTokenFragment[];
    percentageDiscount: number | null;
    categoryName?: string;
};

const GALLERY_SHOWN_ITEMS_COUNT = 5;

export const ProductDetailGallery: FC<ProductDetailGalleryProps> = ({
    flags,
    images,
    productName,
    videoIds = [],
    percentageDiscount,
    categoryName,
}) => {
    const { t } = useTranslation();
    const [firstImage, ...additionalImages] = images;
    const mainImage = images.length ? firstImage : undefined;

    const galleryItems = [...videoIds, ...additionalImages];
    const galleryLastShownItemIndex = GALLERY_SHOWN_ITEMS_COUNT - 1;
    const galleryAdditionalItemsCount = galleryItems.length - GALLERY_SHOWN_ITEMS_COUNT;

    const [selectedGalleryItemIndex, setSelectedGalleryItemIndex] = useState<number>();

    return (
        <>
            <div
                key={productName}
                className="vl:basis-3/5 vl:flex-row flex w-full basis-1/2 flex-col items-start gap-6"
            >
                <div
                    className={twJoin('vl:order-2 relative flex w-full justify-center')}
                    data-src={mainImage?.url}
                    data-tid={TIDs.product_detail_main_image}
                >
                    <Image
                        priority
                        alt={generateProductImageAlt(productName, categoryName)}
                        height={500}
                        sizes="(max-width: 1023px) 100vw, 500px"
                        src={mainImage?.url}
                        width={500}
                        className={twJoin(
                            'vl:size-[500px] h-[320px] w-full object-contain lg:h-[500px]',
                            !!galleryItems.length && 'cursor-pointer',
                        )}
                        onClickCapture={() => !!galleryItems.length && setSelectedGalleryItemIndex(0)}
                    />

                    <ProductFlags
                        flags={flags}
                        percentageDiscount={percentageDiscount}
                        variant="detail"
                        visibleItemsConfig={{ flags: true, discount: true }}
                    />
                </div>

                {!!galleryItems.length && (
                    <ul className="vl:flex-col flex gap-1.5 sm:mx-auto sm:max-w-lg sm:gap-2">
                        {galleryItems.map((galleryItem, index) => {
                            const isImage = galleryItem.__typename === 'Image';
                            const isVideo = galleryItem.__typename === 'VideoToken';

                            const galleryItemThumbnail = isImage ? galleryItem : undefined;
                            const isWithAdditionalImages =
                                index === galleryLastShownItemIndex && galleryAdditionalItemsCount > 0;

                            if (index > galleryLastShownItemIndex) {
                                return null;
                            }

                            return (
                                <Fragment key={index}>
                                    <li>
                                        <button
                                            aria-label={t('Open image gallery of {{ productName }}', { productName })}
                                            tabIndex={0}
                                            title={t('View product image')}
                                            className={twJoin(
                                                'outline-border-default bg-background-more flex size-12 cursor-pointer items-center justify-center rounded-lg hover:outline-1 sm:size-16',
                                                (isWithAdditionalImages || isVideo) && 'relative',
                                            )}
                                            onClick={() => setSelectedGalleryItemIndex(index + 1)}
                                        >
                                            {isImage && (
                                                <Image
                                                    alt={`${productName}-${index}`}
                                                    className="aspect-square object-contain object-center p-1 mix-blend-multiply"
                                                    height={64}
                                                    sizes="(max-width: 1023px) 60px, 56px"
                                                    src={galleryItemThumbnail?.url}
                                                    tid={TIDs.product_gallery_image}
                                                    width={64}
                                                />
                                            )}

                                            {isVideo && (
                                                <>
                                                    <Image
                                                        alt={galleryItem.description ?? t('Product Video')}
                                                        className="aspect-square object-contain object-center p-1 mix-blend-multiply"
                                                        height={64}
                                                        src={`https://img.youtube.com/vi/${galleryItem.token}/1.jpg`}
                                                        width={64}
                                                    />
                                                    <span className="bg-overlay-image absolute flex h-full w-full items-center justify-center overflow-hidden rounded-lg">
                                                        <PlayIcon className="text-text-inverted h-8 w-8 rounded-full" />
                                                    </span>
                                                </>
                                            )}
                                        </button>
                                    </li>

                                    {isWithAdditionalImages && (
                                        <li>
                                            <button
                                                className="outline-border-default bg-background-more flex size-12 cursor-pointer items-center justify-center rounded-lg hover:outline-1 sm:size-16"
                                                tabIndex={0}
                                                title={t('View product image')}
                                                aria-label={t('Open image gallery of {{ productName }}', {
                                                    productName,
                                                })}
                                                onClick={() => setSelectedGalleryItemIndex(index + 2)}
                                            >
                                                <span className="text-text-accent font-secondary text-sm font-semibold">
                                                    +{galleryAdditionalItemsCount}
                                                </span>
                                            </button>
                                        </li>
                                    )}
                                </Fragment>
                            );
                        })}
                    </ul>
                )}
            </div>

            {selectedGalleryItemIndex !== undefined && (
                <ModalGallery
                    galleryName={productName}
                    initialIndex={selectedGalleryItemIndex}
                    items={[firstImage, ...galleryItems]}
                    onCloseModal={() => setSelectedGalleryItemIndex(undefined)}
                />
            )}
        </>
    );
};
