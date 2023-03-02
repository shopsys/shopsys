import 'keen-slider/keen-slider.min.css';
import { ImageSizeType } from 'types/image';

type BannersSliderItemProps = {
    link: string;
    image: ImageSizeType | null;
};

export const BannersSliderItem: FC<BannersSliderItemProps> = ({ image, link }) => (
    <a href={link} className="keen-slider__slide">
        {image === null ? (
            <BannerImage src="images/optimized-noimage.png" />
        ) : (
            <picture>
                {image.additionalSizes.map((size) => (
                    <source key={size.url} srcSet={size.url} media={size.media} />
                ))}
                <BannerImage src={image.url} />
            </picture>
        )}
    </a>
);

const BannerImage: FC<{ src: string }> = ({ src }) => (
    <img className="block h-full w-full rounded-xl object-cover" src={src} />
);
