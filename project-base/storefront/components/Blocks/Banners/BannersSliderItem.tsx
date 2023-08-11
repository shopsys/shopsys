import { SliderItemFragmentApi } from 'graphql/generated';

type BannersSliderItemProps = {
    item: SliderItemFragmentApi;
    isDesktop: boolean;
};

export const BannersSliderItem: FC<BannersSliderItemProps> = ({
    item: { webMainImage, mobileMainImage, link, name },
    isDesktop,
}) => {
    const image = isDesktop ? webMainImage : mobileMainImage;
    const imageSize = image?.sizes.find((i) => i.size === 'default');

    return (
        <a href={link} className="keen-slider__slide w-full">
            {!imageSize ? (
                <BannerImage src="images/optimized-noimage.webp" alt="no image" />
            ) : (
                <picture>
                    {imageSize.additionalSizes.map((additionalSize) => (
                        <source key={additionalSize.url} srcSet={additionalSize.url} media={additionalSize.media} />
                    ))}
                    <BannerImage src={imageSize.url} alt={image?.name || name} />
                </picture>
            )}
        </a>
    );
};

const BannerImage: FC<{ src: string; alt: string }> = ({ src, alt }) => (
    <img className="block h-full w-full object-cover" src={src} alt={alt} />
);
