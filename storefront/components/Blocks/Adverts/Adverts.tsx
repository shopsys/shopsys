import { AdvertsLinkStyled, AdvertsStyled } from './Adverts.style';
import { FC, Fragment, HTMLAttributes, useState } from 'react';
import { AdvertType } from 'types/advert';
import { CategoryDetailType } from 'types/category';
import { desktopFirstSizes } from 'components/Theme/mediaQueries';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';
import { getAdverts } from 'connectors/adverts/Adverts';
import Image from 'components/Basic/Image/Image';
import { isElementVisible } from 'components/Helpers/isElementVisible';
import Link from 'next/link';
import { useGetWindowSize } from 'hooks/ui/UseGetWindowSize';
import { useResizeWidthEffect } from 'hooks/ui/UseResizeWidthEffect';
import Webline from 'components/Layout/Webline';

type NativeProps = ExtractNativePropsFromDefault<HTMLAttributes<HTMLDivElement>, never, 'className'>;

type AdvertsProps = {
    positionName: 'productList' | 'footer' | 'header' | 'productListMiddle' | 'cartPreview';
    withGapBottom?: boolean;
    withGapTop?: boolean;
    withWebline?: boolean;
    currentCategory?: CategoryDetailType;
};

const Adverts: FC<AdvertsProps & NativeProps> = (props) => {
    const adverts = getAdverts();
    const [isMobile, setIsMobile] = useState(false);
    const { width } = useGetWindowSize();
    const WrapperComponent = props.withWebline ? Webline : Fragment;
    const isPositionNameSet = adverts?.some((item) => item.positionName === props.positionName);

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

    return (
        <WrapperComponent>
            <AdvertsStyled
                className={props.className}
                withGapTop={props.withGapTop}
                withGapBottom={props.withGapBottom}
            >
                {adverts?.map(
                    (item, index) =>
                        shouldBeShown(item, props) &&
                        (item.__typename === 'AdvertImage' ? (
                            <Fragment key={index}>
                                {item.link !== undefined ? (
                                    <Link href={item.link} passHref>
                                        <AdvertsLinkStyled target="_blank">
                                            {isMobile ? (
                                                <Image image={item.imageMobile} alt={item.name} />
                                            ) : (
                                                <Image image={item.image} alt={item.name} />
                                            )}
                                        </AdvertsLinkStyled>
                                    </Link>
                                ) : (
                                    <>
                                        {isMobile ? (
                                            <Image image={item.imageMobile} alt={item.name} />
                                        ) : (
                                            <Image image={item.image} alt={item.name} />
                                        )}
                                    </>
                                )}
                            </Fragment>
                        ) : (
                            <div dangerouslySetInnerHTML={{ __html: item.code }} key={index} />
                        )),
                )}
            </AdvertsStyled>
        </WrapperComponent>
    );
};

const shouldBeShown = (advert: AdvertType, advertsProps: AdvertsProps): boolean => {
    if (advert.positionName !== advertsProps.positionName) {
        return false;
    }
    if (advert.positionName === 'productListMiddle' && advert.categories.length === 0) {
        return false;
    }
    for (const category of advert.categories) {
        if (
            category.slug === advertsProps.currentCategory?.slug ||
            category.slug === advertsProps.currentCategory?.originalCategorySlug
        ) {
            return true;
        }
    }
    return advertsProps.positionName !== 'productListMiddle' && advert.positionName === advertsProps.positionName;
};

export default Adverts;
