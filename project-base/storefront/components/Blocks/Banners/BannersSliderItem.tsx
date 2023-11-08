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

    return (
        <a className="keen-slider__slide w-full" href={link}>
            {!image ? (
                <BannerImage alt="no image" src="images/optimized-noimage.webp" />
            ) : (
                <picture>
                    <BannerImage alt={image.name || name} src={image.url} />
                </picture>
            )}
        </a>
    );
};

const BannerImage: FC<{ src: string; alt: string }> = ({ src, alt }) => (
    <img alt={alt} className="block h-full w-full object-cover" src={src} />
);
