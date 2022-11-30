import { AdvertsStyled } from './Adverts.style';
import { Image } from 'components/Basic/Image/Image';
import { isElementVisible } from 'components/Helpers/isElementVisible';
import { Webline } from 'components/Layout/Webline/Webline';
import { desktopFirstSizes } from 'components/Theme/mediaQueries';
import { useAdverts } from 'connectors/adverts/Adverts';
import { useGetWindowSize } from 'hooks/ui/useGetWindowSize';
import { useResizeWidthEffect } from 'hooks/ui/useResizeWidthEffect';
import NextLink from 'next/link';
import { FC, Fragment, HTMLAttributes, useState } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';
import { AdvertType } from 'types/advert';
import { CategoryDetailType } from 'types/category';

type NativeProps = ExtractNativePropsFromDefault<HTMLAttributes<HTMLElement>, never, 'className'>;

type PositionNameType = 'productList' | 'footer' | 'header' | 'productListMiddle' | 'cartPreview';

type AdvertsProps = NativeProps & {
    positionName: PositionNameType;
    withGapBottom?: boolean;
    withGapTop?: boolean;
    withWebline?: boolean;
    currentCategory?: CategoryDetailType;
};

export const Adverts: FC<AdvertsProps> = ({
    positionName,
    withGapBottom,
    withGapTop,
    withWebline,
    currentCategory,
    className,
}) => {
    const adverts = useAdverts();
    const [isMobile, setIsMobile] = useState(false);
    const { width } = useGetWindowSize();
    const isPositionNameSet = adverts?.some((item) => item.positionName === positionName);

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
        <AdvertsStyled withGapTop={withGapTop} withGapBottom={withGapBottom}>
            {adverts?.map(
                (item, index) =>
                    shouldBeShown(item, positionName, currentCategory) &&
                    (item.__typename === 'AdvertImage' ? (
                        <Fragment key={index}>
                            {item.link !== undefined ? (
                                <NextLink href={item.link} passHref>
                                    <a target="_blank">
                                        <Image
                                            image={isMobile ? item.imageMobile : item.image}
                                            type={item.positionName}
                                            alt={item.name}
                                        />
                                    </a>
                                </NextLink>
                            ) : (
                                <Image
                                    image={isMobile ? item.imageMobile : item.image}
                                    type={item.positionName}
                                    alt={item.name}
                                />
                            )}
                        </Fragment>
                    ) : (
                        <div dangerouslySetInnerHTML={{ __html: item.code }} key={index} />
                    )),
            )}
        </AdvertsStyled>
    );

    return withWebline ? wrapWithWebline(content, className) : content;
};

const shouldBeShown = (
    advert: AdvertType,
    positionName: PositionNameType,
    currentCategory?: CategoryDetailType,
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
