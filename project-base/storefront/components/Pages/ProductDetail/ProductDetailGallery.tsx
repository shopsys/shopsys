import { Gallery } from 'components/Basic/Gallery/Gallery';
import { PlayIcon } from 'components/Basic/Icon/IconsSvg';
import { Image } from 'components/Basic/Image/Image';
import { ProductFlags } from 'components/Blocks/Product/ProductFlags';
import { ImageSizesFragmentApi, SimpleFlagFragmentApi, VideoTokenFragmentApi } from 'graphql/generated';
import { twJoin } from 'tailwind-merge';

type ProductDetailGalleryProps = {
    images: ImageSizesFragmentApi[];
    productName: string;
    flags: SimpleFlagFragmentApi[];
    videoIds?: VideoTokenFragmentApi[];
};

const GALLERY_SHOWN_ITEMS_COUNT = 5;

export const ProductDetailGallery: FC<ProductDetailGalleryProps> = ({ flags, images, productName, videoIds = [] }) => {
    const [firstImage, ...additionalImages] = images;
    const mainImage = images.length ? firstImage : undefined;
    const mainImageUrl = mainImage?.sizes.find((size) => size.size === 'default')?.url;

    const galleryItems = [...videoIds, ...additionalImages];
    const galleryLastShownItemIndex = GALLERY_SHOWN_ITEMS_COUNT - 1;
    const galleryAdditionalItemsCount = galleryItems.length - GALLERY_SHOWN_ITEMS_COUNT;

    return (
        <Gallery className="basis-1/2 flex-col gap-6 vl:basis-3/5 vl:flex-row" selector=".lightboxItem">
            <div
                data-src={mainImageUrl}
                className={twJoin(
                    'relative flex w-full justify-center vl:order-2',
                    additionalImages.length && 'lightboxItem',
                )}
            >
                <Image
                    alt={mainImage?.name || productName}
                    className="max-h-[460px] w-auto"
                    height={400}
                    image={mainImage}
                    type="default"
                    wrapperClassName="block h-full"
                />

                {!!flags.length && (
                    <div className="absolute top-3 left-4 flex flex-col">
                        <ProductFlags flags={flags} />
                    </div>
                )}
            </div>

            {!!galleryItems.length && (
                <div className="mx-auto flex w-full max-w-lg items-center justify-center gap-2 lg:relative vl:order-none vl:w-24 vl:flex-col">
                    {galleryItems.map((galleryItem, index) => {
                        const isImage = galleryItem.__typename === 'Image';
                        const isVideo = galleryItem.__typename === 'VideoToken';

                        const galleryItemThumbnail = isImage
                            ? galleryItem.sizes.find((size) => size.size === 'list')
                            : undefined;

                        const dataSrc = isImage
                            ? galleryItem.sizes.find((size) => size.size === 'default')?.url
                            : `https://www.youtube.com/embed/${galleryItem.token}`;
                        const dataPoster = isImage
                            ? undefined
                            : `https://img.youtube.com/vi/${galleryItem.token}/0.jpg`;

                        return (
                            <div
                                key={index}
                                data-poster={dataPoster}
                                data-src={dataSrc}
                                className={twJoin(
                                    'lightboxItem relative flex w-full basis-1/5 cursor-pointer justify-center vl:basis-auto',
                                    index > galleryLastShownItemIndex && 'hidden',
                                )}
                            >
                                {isImage && (
                                    <img
                                        alt={galleryItem.name || `${productName}-${index}`}
                                        className="max-h-16 w-auto sm:max-h-20"
                                        height={galleryItemThumbnail?.height || 90}
                                        src={galleryItemThumbnail?.url}
                                        srcSet={`${galleryItemThumbnail?.additionalSizes[0].url} 1.5x`}
                                        width={galleryItemThumbnail?.width || 90}
                                    />
                                )}

                                {isVideo && (
                                    <>
                                        <img
                                            alt={galleryItem.description}
                                            className="max-h-20 w-auto"
                                            height={360}
                                            src={`https://img.youtube.com/vi/${galleryItem.token}/1.jpg`}
                                            width={480}
                                        />

                                        <PlayIcon className="absolute top-1/2 left-1/2 flex h-8 w-8 -translate-y-1/2 -translate-x-1/2 items-center justify-center rounded-full bg-dark bg-opacity-50 text-white" />
                                    </>
                                )}

                                {index === galleryLastShownItemIndex && !!galleryAdditionalItemsCount && (
                                    <div className="absolute top-0 left-0 flex h-full w-full items-center justify-center bg-white bg-opacity-60 text-lg font-bold">
                                        +{galleryAdditionalItemsCount}
                                    </div>
                                )}
                            </div>
                        );
                    })}
                </div>
            )}
        </Gallery>
    );
};
