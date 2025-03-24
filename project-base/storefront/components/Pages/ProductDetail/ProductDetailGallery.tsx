import { PlayIcon } from 'components/Basic/Icon/PlayIcon';
import { Image } from 'components/Basic/Image/Image';
import { ProductFlags } from 'components/Blocks/Product/ProductFlags';
import { TIDs } from 'cypress/tids';
import { TypeSimpleFlagFragment } from 'graphql/requests/flags/fragments/SimpleFlagFragment.generated';
import { TypeImageFragment } from 'graphql/requests/images/fragments/ImageFragment.generated';
import { TypeVideoTokenFragment } from 'graphql/requests/products/fragments/VideoTokenFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { useState } from 'react';
import { twJoin } from 'tailwind-merge';

const ModalGallery = dynamic(() =>
    import('components/Basic/ModalGallery/ModalGallery').then((component) => component.ModalGallery),
);

type ProductDetailGalleryProps = {
    images: TypeImageFragment[];
    productName: string;
    flags: TypeSimpleFlagFragment[];
    videoIds?: TypeVideoTokenFragment[];
    percentageDiscount: number | null;
};

const GALLERY_SHOWN_ITEMS_COUNT = 5;

export const ProductDetailGallery: FC<ProductDetailGalleryProps> = ({
    flags,
    images,
    productName,
    videoIds = [],
    percentageDiscount,
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
                    tid={TIDs.product_detail_main_image}
                >
                    <Image
                        priority
                        alt={mainImage?.name || productName}
                        className="vl:size-[500px] h-[320px] w-full object-contain lg:h-[500px]"
                        height={500}
                        sizes="50vw"
                        src={mainImage?.url}
                        width={500}
                        onClickCapture={() => setSelectedGalleryItemIndex(0)}
                    />

                    <ProductFlags
                        flags={flags}
                        percentageDiscount={percentageDiscount}
                        variant="detail"
                        visibleItemsConfig={{ flags: true, discount: true }}
                    />
                </div>

                {!!galleryItems.length && (
                    <ul className="vl:order-none vl:w-16 vl:flex-col mx-auto flex w-full max-w-lg items-center justify-center gap-2 lg:relative">
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
                                <li
                                    key={index}
                                    className={twJoin(
                                        'outline-borderAccent vl:w-auto flex w-1/5 cursor-pointer items-center justify-center rounded-lg hover:outline-1 sm:h-16',
                                        (isWithAdditionalImages || isVideo) && 'relative',
                                    )}
                                    onClick={() => setSelectedGalleryItemIndex(index + 1)}
                                >
                                    {isImage && (
                                        <Image
                                            alt={galleryItem.name || `${productName}-${index}`}
                                            className="bg-backgroundMore aspect-square max-h-full rounded-md object-contain p-1 mix-blend-multiply"
                                            height={90}
                                            src={galleryItemThumbnail?.url}
                                            tid={TIDs.product_gallery_image}
                                            width={90}
                                        />
                                    )}

                                    {isVideo && (
                                        <>
                                            <Image
                                                alt={galleryItem.description ?? t('Product Video')}
                                                className="max-h-full rounded-md"
                                                height={90}
                                                src={`https://img.youtube.com/vi/${galleryItem.token}/1.jpg`}
                                                width={90}
                                            />
                                            <div className="bg-imageOverlay absolute flex h-full w-full items-center justify-center overflow-hidden rounded-lg">
                                                <PlayIcon className="text-textInverted h-8 w-8 rounded-full" />
                                            </div>
                                        </>
                                    )}

                                    {isWithAdditionalImages && (
                                        <div className="bg-imageOverlay absolute top-0 left-0 flex h-full w-full items-center justify-center rounded-lg text-lg font-bold">
                                            +{galleryAdditionalItemsCount}
                                        </div>
                                    )}
                                </li>
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
