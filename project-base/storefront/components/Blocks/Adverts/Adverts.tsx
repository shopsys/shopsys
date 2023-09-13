import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Image } from 'components/Basic/Image/Image';
import { isElementVisible } from 'helpers/isElementVisible';
import { Webline } from 'components/Layout/Webline/Webline';
import { desktopFirstSizes } from 'helpers/mediaQueries';
import { AdvertsFragmentApi, CategoryDetailFragmentApi, useAdvertsQueryApi } from 'graphql/generated';
import { useGetWindowSize } from 'hooks/ui/useGetWindowSize';
import { useResizeWidthEffect } from 'hooks/ui/useResizeWidthEffect';
import { Fragment, useState } from 'react';
import { twJoin } from 'tailwind-merge';

type PositionNameType =
    | 'productList'
    | 'footer'
    | 'header'
    | 'productListMiddle'
    | 'cartPreview'
    | 'productListSecondRow';

type AdvertsProps = {
    positionName: PositionNameType;
    withGapBottom?: boolean;
    withGapTop?: boolean;
    withWebline?: boolean;
    currentCategory?: CategoryDetailFragmentApi;
    isSingle?: boolean;
};

export const Adverts: FC<AdvertsProps> = ({
    positionName,
    withGapBottom,
    withGapTop,
    withWebline,
    currentCategory,
    className,
    isSingle,
}) => {
    const [{ data: advertsData }] = useAdvertsQueryApi();
    const [isMobile, setIsMobile] = useState(false);
    const { width } = useGetWindowSize();

    const filteredAdverts = advertsData?.adverts.filter((advert) => advert.positionName === positionName);
    const displayedAdverts =
        isSingle && filteredAdverts?.length
            ? [filteredAdverts[Math.floor(Math.random() * filteredAdverts.length)]]
            : filteredAdverts;

    useResizeWidthEffect(
        width,
        desktopFirstSizes.tablet,
        () => setIsMobile(false),
        () => setIsMobile(true),
        () => setIsMobile(isElementVisible([{ min: 0, max: desktopFirstSizes.tablet }], width)),
    );

    const content = !!displayedAdverts?.length && (
        <div className={twJoin(withGapBottom && 'mb-8', withGapTop && 'mt-8', !withWebline && className)}>
            {displayedAdverts.map((advert, index) => {
                if (advert.__typename === 'AdvertImage') {
                    if (!shouldBeShown(advert, positionName, currentCategory)) {
                        return null;
                    }

                    const itemImage = isMobile ? advert.mainImageMobile : advert.mainImage;

                    const ImageComponent = (
                        <Image image={itemImage} type={advert.positionName} alt={itemImage?.name || advert.name} />
                    );

                    return (
                        <Fragment key={index}>
                            {advert.link ? (
                                <ExtendedNextLink href={advert.link} target="_blank" type="static">
                                    {ImageComponent}
                                </ExtendedNextLink>
                            ) : (
                                ImageComponent
                            )}
                        </Fragment>
                    );
                }

                return <div dangerouslySetInnerHTML={{ __html: advert.code }} key={index} />;
            })}
        </div>
    );

    if (withWebline && content) {
        return wrapWithWebline(content, className);
    }

    return content || null;
};

const shouldBeShown = (
    advert: AdvertsFragmentApi | undefined,
    positionName: PositionNameType,
    currentCategory?: CategoryDetailFragmentApi,
): boolean => {
    if (!advert || advert.positionName !== positionName) {
        return false;
    }
    if (advert.positionName === 'productListMiddle' && advert.categories.length === 0) {
        return false;
    }
    for (const category of advert.categories) {
        if (category.slug === currentCategory?.slug || category.slug === currentCategory?.originalCategorySlug) {
            return true;
        }
    }
    return positionName !== 'productListMiddle' && advert.positionName === positionName;
};

const wrapWithWebline = (content: JSX.Element, className: string | undefined) => {
    return <Webline className={className}>{content}</Webline>;
};
