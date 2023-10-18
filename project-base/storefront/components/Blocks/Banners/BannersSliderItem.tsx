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
        <a className="keen-slider__slide w-full" href={link}>
            {!imageSize ? (
                <BannerImage alt="no image" src="images/optimized-noimage.webp" />
            ) : (
                <picture>
                    {imageSize.additionalSizes.map((additionalSize) => (
                        <source key={additionalSize.url} media={additionalSize.media} srcSet={additionalSize.url} />
                    ))}
                    <BannerImage alt={image?.name || name} src={imageSize.url} />
                </picture>
            )}
        </a>
    );
};

const BannerImage: FC<{ src: string; alt: string }> = ({ src, alt }) => (
    <img alt={alt} className="block h-full w-full object-cover" src={src} />
);
