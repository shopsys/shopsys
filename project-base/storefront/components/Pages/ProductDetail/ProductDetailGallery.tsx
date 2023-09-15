import { ImageSizesFragmentApi, SimpleFlagFragmentApi, VideoTokenFragmentApi } from 'graphql/generated';
import { Gallery } from 'components/Basic/Gallery/Gallery';
import { ProductFlags } from 'components/Blocks/Product/ProductFlags';
import { Image } from 'components/Basic/Image/Image';
import { twJoin } from 'tailwind-merge';
import { ProductDetailGalleryVideo } from './ProductDetailGalleryVideo';

type ProductDetailGalleryProps = {
    images: ImageSizesFragmentApi[];
    productName: string;
    flags: SimpleFlagFragmentApi[];
    videoIds?: VideoTokenFragmentApi[];
};

export const ProductDetailGallery: FC<ProductDetailGalleryProps> = ({ flags, images, productName, videoIds }) => {
    const [firstImage, ...additionalImages] = images;
    const mainImage = images.length ? firstImage : undefined;
    const mainImageUrl = mainImage?.sizes.find((size) => size.size === 'default')?.url;

    return (
        <Gallery selector=".lightboxItem" className="flex-col gap-5 lg:flex-row">
            <div
                data-src={mainImageUrl}
                className={twJoin(
                    'relative w-full justify-center lg:order-2',
                    additionalImages.length && 'lightboxItem',
                )}
            >
                <Image image={mainImage} alt={mainImage?.name || productName} type="default" height={400} />

                {!!flags.length && (
                    <div className="absolute top-3 left-4 flex flex-col">
                        <ProductFlags flags={flags} />
                    </div>
                )}
            </div>

            {!!(additionalImages.length || videoIds?.length) && (
                <div className="flex w-full flex-wrap justify-center gap-2 lg:relative lg:order-none lg:w-24 lg:flex-col">
                    {!!additionalImages.length &&
                        additionalImages.map((image, index) => (
                            <div
                                key={index}
                                className={twJoin(
                                    'lightboxItem h-16 w-20 cursor-pointer rounded lg:bg-greyVeryLight lg:transition lg:hover:bg-greyLighter',
                                    index > 6 && 'hidden',
                                )}
                                data-src={image.sizes.find((size) => size.size === 'default')?.url}
                            >
                                <Image
                                    image={image}
                                    alt={image.name || `${productName}-${index}`}
                                    type="default"
                                    className=""
                                />
                            </div>
                        ))}

                    {videoIds?.map((videoId) => (
                        <ProductDetailGalleryVideo key={videoId.token} videoId={videoId} />
                    ))}
                </div>
            )}
        </Gallery>
    );
};
