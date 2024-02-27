import { PlayIcon } from 'components/Basic/Icon/IconsSvg';
import { Image } from 'components/Basic/Image/Image';
import { ProductFlags } from 'components/Blocks/Product/ProductFlags';
import { ImageFragmentApi, SimpleFlagFragmentApi, VideoTokenFragmentApi } from 'graphql/generated';
import dynamic from 'next/dynamic';
import { useState } from 'react';
import { twJoin } from 'tailwind-merge';

const ModalGallery = dynamic(() =>
    import('components/Basic/ModalGallery/ModalGallery').then((component) => component.ModalGallery),
);

type ProductDetailGalleryProps = {
    images: ImageFragmentApi[];
    productName: string;
    flags: SimpleFlagFragmentApi[];
    videoIds?: VideoTokenFragmentApi[];
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
            <div key={productName} className="flex basis-1/2 flex-col items-start gap-6 vl:basis-3/5 vl:flex-row">
                <div className={twJoin('relative flex w-full justify-center vl:order-2')} data-src={mainImage?.url}>
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
                    <ul className="mx-auto flex w-full max-w-lg items-center justify-center gap-2 lg:relative vl:order-none vl:w-24 vl:flex-col">
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
                                        'flex max-h-16 w-1/5 cursor-pointer items-center justify-center sm:h-20 vl:w-auto',
                                        (isWithAdditionalImages || isVideo) && 'relative',
                                    )}
                                    onClick={() => setSelectedGalleryItemIndex(index + 1)}
                                >
                                    {isImage && (
                                        <Image
                                            alt={galleryItem.name || `${productName}-${index}`}
                                            className="aspect-square max-h-full object-contain"
                                            height={90}
                                            src={galleryItemThumbnail?.url}
                                            width={90}
                                        />
                                    )}

                                    {isVideo && (
                                        <>
                                            <Image
                                                alt={galleryItem.description}
                                                className="max-h-full"
                                                height={90}
                                                src={`https://img.youtube.com/vi/${galleryItem.token}/1.jpg`}
                                                width={90}
                                            />

                                            <PlayIcon className="absolute top-1/2 left-1/2 flex h-8 w-8 -translate-y-1/2 -translate-x-1/2 items-center justify-center rounded-full bg-dark bg-opacity-50 text-white" />
                                        </>
                                    )}

                                    {isWithAdditionalImages && (
                                        <div className="absolute top-0 left-0 flex h-full w-full items-center justify-center bg-white bg-opacity-60 text-lg font-bold">
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
