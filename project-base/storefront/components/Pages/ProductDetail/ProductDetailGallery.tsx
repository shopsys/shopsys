import { ImageSizesFragmentApi, SimpleFlagFragmentApi, VideoTokenFragmentApi } from 'graphql/generated';
import { Gallery } from 'components/Basic/Gallery/Gallery';
import { ProductFlags } from 'components/Blocks/Product/ProductFlags';
import { Image } from 'components/Basic/Image/Image';
import { twJoin } from 'tailwind-merge';
import { PlayIcon } from 'components/Basic/Icon/IconsSvg';

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
        <Gallery selector=".lightboxItem" className="basis-1/2 flex-col gap-6 vl:basis-3/5 vl:flex-row">
            <div
                data-src={mainImageUrl}
                className={twJoin(
                    'relative flex w-full justify-center vl:order-2',
                    additionalImages.length && 'lightboxItem',
                )}
            >
                <Image
                    image={mainImage}
                    alt={mainImage?.name || productName}
                    type="default"
                    height={400}
                    className="max-h-[460px] w-auto"
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
                                className={twJoin(
                                    'lightboxItem relative flex w-full basis-1/5 cursor-pointer justify-center vl:basis-auto',
                                    index > galleryLastShownItemIndex && 'hidden',
                                )}
                                data-src={dataSrc}
                                data-poster={dataPoster}
                            >
                                {isImage && (
                                    <img
                                        src={galleryItemThumbnail?.url}
                                        width={galleryItemThumbnail?.width || 90}
                                        height={galleryItemThumbnail?.height || 90}
                                        srcSet={`${galleryItemThumbnail?.additionalSizes[0].url} 1.5x`}
                                        alt={galleryItem.name || `${productName}-${index}`}
                                        className="max-h-16 w-auto sm:max-h-20"
                                    />
                                )}

                                {isVideo && (
                                    <>
                                        <img
                                            src={`https://img.youtube.com/vi/${galleryItem.token}/1.jpg`}
                                            width={480}
                                            height={360}
                                            alt={galleryItem.description}
                                            className="max-h-20 w-auto"
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
