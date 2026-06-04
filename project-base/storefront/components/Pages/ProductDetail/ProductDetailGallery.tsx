import { PlayIcon } from 'components/Basic/Icon/PlayIcon';
import { Image } from 'components/Basic/Image/Image';
import { ProductFlags } from 'components/Blocks/Product/ProductFlags';
import { TIDs } from 'cypress/tids';
import { TypeSimpleFlagFragment } from 'graphql/requests/flags/fragments/SimpleFlagFragment.generated';
import { TypeImageFragment } from 'graphql/requests/images/fragments/ImageFragment.generated';
import { TypeVideoTokenFragment } from 'graphql/requests/products/fragments/VideoTokenFragment.generated';
import dynamic from 'next/dynamic';
import { Fragment } from 'react';
import { useSessionStore } from 'store/useSessionStore';
import { twJoin } from 'tailwind-merge';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { generateProductImageAlt } from 'utils/productAltText';

type ProductDetailGalleryProps = {
    images: TypeImageFragment[];
    productName: string;
    flags: TypeSimpleFlagFragment[];
    videoIds?: TypeVideoTokenFragment[];
    percentageDiscount: number | null;
    categoryName?: string;
};

const GALLERY_SHOWN_ITEMS_COUNT = 5;

const ModalGallery = dynamic(
    () => import('components/Basic/ModalGallery/ModalGallery').then((component) => component.ModalGallery),
    { ssr: false },
);

export const ProductDetailGallery: FC<ProductDetailGalleryProps> = ({
    flags,
    images,
    productName,
    videoIds = [],
    percentageDiscount,
    categoryName,
}) => {
    const { t } = useTranslation();
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);
    const closePortalContent = useSessionStore((s) => s.closePortalContent);
    const storeCurrentFocus = useSessionStore((s) => s.storeCurrentFocus);
    const [firstImage, ...additionalImages] = images;
    const mainImage = images.length ? firstImage : undefined;

    const galleryItems = [...videoIds, ...additionalImages];
    const galleryLastShownItemIndex = GALLERY_SHOWN_ITEMS_COUNT - 1;
    const galleryAdditionalItemsCount = galleryItems.length - GALLERY_SHOWN_ITEMS_COUNT;

    const openGallery = (initialIndex: number) => {
        storeCurrentFocus();

        updatePortalContent(
            <ModalGallery
                galleryName={productName}
                initialIndex={initialIndex}
                items={[firstImage, ...galleryItems]}
                onCloseModal={closePortalContent}
            />,
        );
    };

    return (
        <div key={productName} className="flex w-full basis-1/2 vl:basis-3/5 vl:flex-row flex-col items-start gap-6">
            <div
                className={twJoin('relative vl:order-2 flex w-full justify-center')}
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
                        'vl:size-125 h-80 w-full object-contain lg:h-125',
                        !!galleryItems.length && 'cursor-pointer',
                    )}
                    onClickCapture={() => {
                        if (galleryItems.length) {
                            openGallery(0);
                        }
                    }}
                />

                <ProductFlags
                    flags={flags}
                    percentageDiscount={percentageDiscount}
                    variant="detail"
                    visibleItemsConfig={{ flags: true, discount: true }}
                />
            </div>

            {!!galleryItems.length && (
                <ul className="flex vl:flex-col gap-1.5 sm:mx-auto sm:max-w-lg sm:gap-2">
                    {galleryItems.map((galleryItem, index) => {
                        const isImage = galleryItem.__typename === 'Image';
                        const isVideo = galleryItem.__typename === 'VideoToken';
                        const galleryItemKey = isImage ? galleryItem.url : galleryItem.token;

                        const galleryItemThumbnail = isImage ? galleryItem : undefined;
                        const isWithAdditionalImages =
                            index === galleryLastShownItemIndex && galleryAdditionalItemsCount > 0;

                        if (index > galleryLastShownItemIndex) {
                            return null;
                        }

                        return (
                            <Fragment key={galleryItemKey}>
                                <li>
                                    <button
                                        tabIndex={0}
                                        title={t('View product image')}
                                        aria-label={t('Open image gallery of {{ productName }}', {
                                            ns: 'accessibility',
                                            productName,
                                        })}
                                        className={twJoin(
                                            'flex size-12 cursor-pointer items-center justify-center rounded-lg bg-background-more outline-border-default hover:outline-1 sm:size-16',
                                            (isWithAdditionalImages || isVideo) && 'relative',
                                        )}
                                        onClick={() => openGallery(index + 1)}
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
                                                    tid={TIDs.product_gallery_video}
                                                    width={64}
                                                />
                                                <span className="absolute flex h-full w-full items-center justify-center overflow-hidden rounded-lg bg-overlay-image">
                                                    <PlayIcon className="h-8 w-8 rounded-full text-text-inverted" />
                                                </span>
                                            </>
                                        )}
                                    </button>
                                </li>

                                {isWithAdditionalImages && (
                                    <li>
                                        <button
                                            className="flex size-12 cursor-pointer items-center justify-center rounded-lg bg-background-more outline-border-default hover:outline-1 sm:size-16"
                                            tabIndex={0}
                                            title={t('View product image')}
                                            aria-label={t('Open image gallery of {{ productName }}', {
                                                ns: 'accessibility',
                                                productName,
                                            })}
                                            onClick={() => openGallery(index + 2)}
                                        >
                                            <span className="font-secondary font-semibold text-sm text-text-accent">
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
    );
};
