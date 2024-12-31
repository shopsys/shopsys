import { BannerImage } from './BannerImage';
import { CarouselState, getBannerOrderCSSProperty, getRGBColorString, getYIQContrastTextColor } from './bannersUtils';
import { TypeSliderItemFragment } from 'graphql/requests/sliderItems/fragments/SliderItemFragment.generated';
import { twJoin } from 'tailwind-merge';
import { twMergeCustom } from 'utils/twMerge';

type BannerProps = {
    banner: TypeSliderItemFragment;
    bannerSliderState: CarouselState;
    index: number;
    numItems: number;
};

const BannerContent: FC<{ banner: TypeSliderItemFragment }> = ({ banner, className }) => (
    <div
        className={twMergeCustom(
            'mt-auto p-6 lg:absolute lg:right-0 lg:h-full lg:w-[455px] lg:px-14 lg:py-24',
            className,
        )}
        style={{
            backgroundColor: getRGBColorString(banner.rgbBackgroundColor, banner.opacity),
        }}
    >
        <h1 className={twJoin('mb-2.5 vl:mb-5', getYIQContrastTextColor(banner.rgbBackgroundColor))}>{banner.name}</h1>
        {banner.description && (
            <p className={getYIQContrastTextColor(banner.rgbBackgroundColor)}>{banner.description}</p>
        )}
    </div>
);

export const Banner: FC<BannerProps> = ({ banner, bannerSliderState, index, numItems }) => {
    return (
        <div
            key={banner.link}
            className="flex flex-[1_0_100%] basis-full flex-col vl:flex-row"
            style={{
                order: getBannerOrderCSSProperty(index, bannerSliderState.sliderPosition, numItems),
            }}
        >
            <BannerImage
                desktopAlt={banner.webMainImage.name || banner.name}
                desktopSrc={banner.webMainImage.url}
                isFirst={index === 0}
                mobileAlt={banner.mobileMainImage.name || banner.name}
                mobileSrc={banner.mobileMainImage.url}
            >
                {banner.description && <BannerContent banner={banner} className="hidden lg:block" />}
            </BannerImage>

            {banner.description && <BannerContent banner={banner} className="block lg:hidden" />}
        </div>
    );
};
