import { PlayIcon } from 'components/Basic/Icon/PlayIcon';
import { Image } from 'components/Basic/Image/Image';
import { ProductFlags } from 'components/Blocks/Product/ProductFlags';
import { TIDs } from 'cypress/tids';
import { TypeSimpleFlagFragment } from 'graphql/requests/flags/fragments/SimpleFlagFragment.generated';
import { TypeImageFragment } from 'graphql/requests/images/fragments/ImageFragment.generated';
import { TypeVideoTokenFragment } from 'graphql/requests/products/fragments/VideoTokenFragment.generated';
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
};

const GALLERY_SHOWN_ITEMS_COUNT = 5;

export const ProductDetailGallery: FC<ProductDetailGalleryProps> = ({ flags, images, productName, videoIds = [] }) => {
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
                className="flex basis-1/2 flex-col items-start gap-6 vl:basis-3/5 vl:flex-row w-full"
            >
                <div
                    className={twJoin('relative flex w-full justify-center vl:order-2')}
                    data-src={mainImage?.url}
                    tid={TIDs.product_detail_main_image}
                >
                    <Image
                        priority
                        alt={mainImage?.name || productName}
                        className="max-h-[500px] w-auto"
                        height={500}
                        sizes="(max-width: 768px) 100vw, 50vw"
                        src={mainImage?.url}
                        width={720}
                        onClickCapture={() => setSelectedGalleryItemIndex(0)}
                    />

                    {!!flags.length && (
                        <div className="absolute top-3 left-4 flex flex-col">
                            <ProductFlags flags={flags} />
                        </div>
                    )}
                </div>

                {!!galleryItems.length && (
                    <ul className="mx-auto flex w-full max-w-lg items-center justify-center gap-2 lg:relative vl:order-none vl:w-16 vl:flex-col">
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
                                        'flex w-1/5 cursor-pointer items-center justify-center sm:h-16 vl:w-auto hover:outline outline-borderAccent rounded-lg outline-1',
                                        (isWithAdditionalImages || isVideo) && 'relative',
                                    )}
                                    onClick={() => setSelectedGalleryItemIndex(index + 1)}
                                >
                                    {isImage && (
                                        <Image
                                            alt={galleryItem.name || `${productName}-${index}`}
                                            className="bg-backgroundMore aspect-square max-h-full object-contain mix-blend-multiply p-1 rounded-md"
                                            height={90}
                                            src={galleryItemThumbnail?.url}
                                            width={90}
                                        />
                                    )}

                                    {isVideo && (
                                        <>
                                            <Image
                                                alt={galleryItem.description}
                                                className="max-h-full rounded-md"
                                                height={90}
                                                src={`https://img.youtube.com/vi/${galleryItem.token}/1.jpg`}
                                                width={90}
                                            />
                                            <div className="absolute items-center justify-center bg-imageOverlay w-full h-full flex rounded-lg overflow-hidden">
                                                <PlayIcon className="h-8 w-8 rounded-full text-textInverted" />
                                            </div>
                                        </>
                                    )}

                                    {isWithAdditionalImages && (
                                        <div className="absolute top-0 left-0 flex h-full w-full items-center justify-center bg-imageOverlay text-lg font-bold rounded-lg">
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
