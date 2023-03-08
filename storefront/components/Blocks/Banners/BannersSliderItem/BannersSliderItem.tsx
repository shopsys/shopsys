import { ImageSizeFragmentApi, ImageSizesFragmentApi } from 'graphql/generated';
import 'keen-slider/keen-slider.min.css';

type BannersSliderItemProps = {
    link: string;
    image: ImageSizesFragmentApi | null;
};

export const BannersSliderItem: FC<BannersSliderItemProps> = ({ image, link }) => {
    const imageSize: ImageSizeFragmentApi | null = image?.sizes.find((i) => i.size === 'default') ?? null;

    return (
        <a href={link} className="keen-slider__slide">
            {imageSize === null ? (
                <BannerImage src="images/optimized-noimage.png" />
            ) : (
                <picture>
                    {imageSize.additionalSizes.map((additionalSize) => (
                        <source key={additionalSize.url} srcSet={additionalSize.url} media={additionalSize.media} />
                    ))}
                    <BannerImage src={imageSize.url} />
                </picture>
            )}
        </a>
    );
};

const BannerImage: FC<{ src: string }> = ({ src }) => (
    <img className="block h-full w-full rounded-xl object-cover" src={src} />
);
