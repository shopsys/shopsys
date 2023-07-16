import { desktopFirstSizes } from 'components/Theme/mediaQueries';
import { ImageSizeFragmentApi, SliderItemFragmentApi } from 'graphql/generated';
import { useGetWindowSize } from 'hooks/ui/useGetWindowSize';
import 'keen-slider/keen-slider.min.css';

type BannersSliderItemProps = {
    item: SliderItemFragmentApi;
};

export const BannersSliderItem: FC<BannersSliderItemProps> = ({
    item: { webMainImage, mobileMainImage, link, name },
}) => {
    const { width } = useGetWindowSize();
    const image = width > desktopFirstSizes.tablet ? webMainImage : mobileMainImage;

    const imageSize: ImageSizeFragmentApi | null = image?.sizes.find((i) => i.size === 'default') ?? null;

    return (
        <a href={link} className="keen-slider__slide">
            {!imageSize ? (
                <BannerImage src="images/optimized-noimage.png" alt="no image" />
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
