import { Image } from 'components/Basic/Image/Image';
import { isElementVisible } from 'components/Helpers/isElementVisible';
import { Webline } from 'components/Layout/Webline/Webline';
import { desktopFirstSizes } from 'components/Theme/mediaQueries';
import { AdvertsFragmentApi, CategoryDetailFragmentApi, useAdvertsQueryApi } from 'graphql/generated';
import { getFirstImageOrNull } from 'helpers/mappers/image';
import { useQueryError } from 'hooks/graphQl/useQueryError';
import { useGetWindowSize } from 'hooks/ui/useGetWindowSize';
import { useResizeWidthEffect } from 'hooks/ui/useResizeWidthEffect';
import NextLink from 'next/link';
import { Fragment, useState } from 'react';
import { twJoin } from 'tailwind-merge';

type PositionNameType = 'productList' | 'footer' | 'header' | 'productListMiddle' | 'cartPreview';

type AdvertsProps = {
    positionName: PositionNameType;
    withGapBottom?: boolean;
    withGapTop?: boolean;
    withWebline?: boolean;
    currentCategory?: CategoryDetailFragmentApi;
};

export const Adverts: FC<AdvertsProps> = ({
    positionName,
    withGapBottom,
    withGapTop,
    withWebline,
    currentCategory,
    className,
}) => {
    const [{ data: advertsData }] = useQueryError(useAdvertsQueryApi());
    const [isMobile, setIsMobile] = useState(false);
    const { width } = useGetWindowSize();
    const isPositionNameSet = advertsData?.adverts.some((item) => item.positionName === positionName);

    useResizeWidthEffect(
        width,
        desktopFirstSizes.tablet,
        () => setIsMobile(false),
        () => setIsMobile(true),
        () => setIsMobile(isElementVisible([{ min: 0, max: desktopFirstSizes.tablet }], width)),
    );

    if (!isPositionNameSet) {
        return null;
    }

    const content = (
        <div className={twJoin(withGapTop && 'mb-8', withGapBottom && 'mt-8')}>
            {advertsData?.adverts.map(
                (item, index) =>
                    shouldBeShown(item, positionName, currentCategory) &&
                    (item.__typename === 'AdvertImage' ? (
                        <Fragment key={index}>
                            {item.link !== null ? (
                                <NextLink href={item.link} passHref>
                                    <a target="_blank">
                                        <Image
                                            image={
                                                isMobile
                                                    ? getFirstImageOrNull(item.imageMobile)
                                                    : getFirstImageOrNull(item.image)
                                            }
                                            type={item.positionName}
                                            alt={item.name}
                                        />
                                    </a>
                                </NextLink>
                            ) : (
                                <Image
                                    image={
                                        isMobile
                                            ? getFirstImageOrNull(item.imageMobile)
                                            : getFirstImageOrNull(item.image)
                                    }
                                    type={item.positionName}
                                    alt={item.name}
                                />
                            )}
                        </Fragment>
                    ) : (
                        <div dangerouslySetInnerHTML={{ __html: item.code }} key={index} />
                    )),
            )}
        </div>
    );

    return withWebline ? wrapWithWebline(content, className) : content;
};

const shouldBeShown = (
    advert: AdvertsFragmentApi,
    positionName: PositionNameType,
    currentCategory?: CategoryDetailFragmentApi,
): boolean => {
    if (advert.positionName !== positionName) {
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
