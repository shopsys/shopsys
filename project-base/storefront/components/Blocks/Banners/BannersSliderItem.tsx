import { Image } from 'components/Basic/Image/Image';
import { TypeSliderItemFragment } from 'graphql/requests/sliderItems/fragments/SliderItemFragment.generated';

type BannersSliderItemProps = {
    item: TypeSliderItemFragment;
    isDesktop: boolean;
    isFirst: boolean;
};

export const BannersSliderItem: FC<BannersSliderItemProps> = ({
    item: { webMainImage, mobileMainImage, link, name },
    isDesktop,
    isFirst,
}) => {
    const image = isDesktop ? webMainImage : mobileMainImage;

    return (
        <a className="keen-slider__slide w-full" href={link}>
            {!image ? (
                <BannerImage alt="no image" isFirst={isFirst} src="images/optimized-noimage.webp" />
            ) : (
                <BannerImage alt={image.name || name} isFirst={isFirst} src={image.url} />
            )}
        </a>
    );
};

const BannerImage: FC<{ isFirst: boolean; src: string; alt: string }> = ({ isFirst, src, alt }) => (
    <Image
        alt={alt}
        className="block h-full w-full object-cover"
        height={300}
        priority={isFirst}
        sizes="(max-width: 1024px) 100vw, 80vw"
        src={src}
        width={1025}
    />
);
