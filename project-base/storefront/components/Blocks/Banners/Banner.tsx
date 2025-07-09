import { BannerImage } from './BannerImage';
import { CarouselState, getBannerOrderCSSProperty } from './bannersUtils';
import { TypeSliderItemFragment } from 'graphql/requests/sliderItems/fragments/SliderItemFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import { twJoin } from 'tailwind-merge';
import { getRGBColorString, getYIQContrastTextColor } from 'utils/colors/colors';
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
            'mt-auto flex flex-col justify-center px-14 py-6 lg:absolute lg:right-0 lg:h-full lg:w-[455px]',
            className,
        )}
        style={{
            backgroundColor: getRGBColorString(banner.rgbBackgroundColor, banner.opacity),
        }}
    >
        <span
            className={twJoin(
                'h1 vl:mb-5 group-focus-visible:text-text-inverted mb-2.5 line-clamp-5 rounded-sm wrap-anywhere group-focus-visible:bg-orange-500',
                getYIQContrastTextColor(banner.rgbBackgroundColor),
            )}
        >
            {banner.name}
        </span>
        {banner.description && (
            <p className={twJoin('line-clamp-10 wrap-anywhere', getYIQContrastTextColor(banner.rgbBackgroundColor))}>
                {banner.description}
            </p>
        )}
    </div>
);

export const Banner: FC<BannerProps> = ({ banner, bannerSliderState, index, numItems }) => {
    const { t } = useTranslation();

    return (
        <div
            key={banner.link}
            className="vl:flex-row flex flex-[1_0_100%] basis-full flex-col"
            style={{
                order: getBannerOrderCSSProperty(index, bannerSliderState.sliderPosition, numItems),
            }}
        >
            <BannerImage
                desktopAlt={`${t('Promotional banner')} - ${banner.webMainImage.name || banner.name} - ${banner.description?.slice(0, 50)}`}
                desktopSrc={banner.webMainImage.url}
                isFirst={index === 0}
                mobileAlt={`${t('Promotional banner')} - ${banner.mobileMainImage.name || banner.name} - ${banner.description?.slice(0, 50)}`}
                mobileSrc={banner.mobileMainImage.url}
            >
                {banner.description && <BannerContent banner={banner} className="hidden lg:flex" />}
            </BannerImage>

            {banner.description && <BannerContent banner={banner} className="block lg:hidden" />}
        </div>
    );
};
