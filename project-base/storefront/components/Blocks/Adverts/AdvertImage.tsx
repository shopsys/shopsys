import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Image } from 'components/Basic/Image/Image';
import { TypeAdvertsFragment_AdvertImage } from 'graphql/requests/adverts/fragments/AdvertsFragment.generated';
import { TypeImage } from 'graphql/types';
import { getImageProps, type ImageLoader } from 'next/image';
import { twJoin } from 'tailwind-merge';

type ImageComponentProps = {
    mainImage: TypeImage | null;
    mainImageMobile: TypeImage | null;
    altBackup: string;
    positionName: TypeAdvertsFragment_AdvertImage['positionName'];
};

type AdvertImageProps = {
    advert: TypeAdvertsFragment_AdvertImage;
};

const advertImageLoader: ImageLoader = ({ src, width }) => `${src}?width=${width || '0'}`;

const ImageComponent = ({ mainImage, mainImageMobile, altBackup, positionName }: ImageComponentProps) => {
    const isFooterAdvert = positionName === 'footer';
    const isHeaderAdvert = positionName === 'header';
    const isProductListAdvert = positionName === 'productListSecondRow';
    const mobileImageClassName = isFooterAdvert || isProductListAdvert ? 'aspect-[770/220]' : 'aspect-[770/300]';
    const desktopImageClassName = isFooterAdvert
        ? 'lg:aspect-[1520/282] lg:max-w-[1520px]'
        : isHeaderAdvert
          ? 'lg:aspect-[1520/400] lg:max-w-[1520px]'
          : isProductListAdvert
            ? 'lg:aspect-[1360/300] lg:max-w-[1360px]'
            : 'lg:aspect-[1280/400] lg:max-w-[1280px]';
    const desktopImageSizes = isFooterAdvert
        ? '(min-width: 1560px) 1520px, calc(100vw - 40px)'
        : isHeaderAdvert
          ? '(min-width: 1560px) 1520px, calc(100vw - 40px)'
          : isProductListAdvert
            ? '(min-width: 1440px) 1360px, 100vw'
            : '(min-width: 1320px) 1280px, 100vw';
    const desktopImageSrcSet = mainImage
        ? getImageProps({
              fill: true,
              alt: '',
              loader: advertImageLoader,
              sizes: desktopImageSizes,
              src: mainImage.url,
          }).props.srcSet
        : undefined;
    const fallbackImage = mainImageMobile ?? mainImage;
    const imageAlt = mainImage?.name || mainImageMobile?.name || altBackup;

    return (
        <div
            className={twJoin(
                "relative mx-auto w-full max-w-192.5 overflow-hidden rounded-xl after:pointer-events-none after:absolute after:inset-0 after:z-above after:rounded-xl after:content-[''] group-focus-visible:after:outline-2 group-focus-visible:after:outline-orange-500 group-focus-visible:after:-outline-offset-2",
                mobileImageClassName,
                desktopImageClassName,
            )}
        >
            <picture className="relative block size-full">
                {desktopImageSrcSet && (
                    <source media="(min-width: 769px)" sizes={desktopImageSizes} srcSet={desktopImageSrcSet} />
                )}
                <Image
                    fill
                    alt={imageAlt}
                    className="object-cover transition-transform duration-300 ease-out group-hover:scale-101"
                    sizes="(max-width: 768px) 100vw, 770px"
                    src={fallbackImage?.url}
                />
            </picture>
        </div>
    );
};

export const AdvertImage: FC<AdvertImageProps> = ({
    advert: { mainImage, mainImageMobile, name, link, positionName },
}) => {
    if (!link) {
        return (
            <ImageComponent
                altBackup={name}
                mainImage={mainImage}
                mainImageMobile={mainImageMobile}
                positionName={positionName}
            />
        );
    }

    return (
        <ExtendedNextLink
            className="group block focus-visible:outline-hidden"
            data-focus-color="preserve"
            href={link}
            target="_blank"
        >
            <ImageComponent
                altBackup={name}
                mainImage={mainImage}
                mainImageMobile={mainImageMobile}
                positionName={positionName}
            />
        </ExtendedNextLink>
    );
};
