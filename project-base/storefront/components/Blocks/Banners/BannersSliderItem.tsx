import { Image } from 'components/Basic/Image/Image';
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
                <BannerImage alt={image.name || name} src={image.url} />
            )}
        </a>
    );
};

const BannerImage: FC<{ src: string; alt: string }> = ({ src, alt }) => (
    <Image
        alt={alt}
        className="block h-full w-full object-cover"
        height={300}
        sizes="(max-width: 1024px) 100vw, 80vw"
        src={src}
        width={1025}
    />
);
