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

type NativeProps = ExtractNativePropsFromDefault<HTMLAttributes<HTMLDivElement>, never, 'className'>;

type AdvertsProps = {
    positionName: 'productList' | 'footer' | 'header' | 'productListMiddle' | 'cartPreview';
    withGap?: boolean;
    currentCategory?: CategoryDetailType;
};

const Adverts: FC<AdvertsProps & NativeProps> = (props) => {
    const adverts = getAdverts();
    const [isMobile, setIsMobile] = useState(false);
    const { width } = useGetWindowSize();

    useResizeWidthEffect(
        width,
        desktopFirstSizes.tablet,
        () => setIsMobile(false),
        () => setIsMobile(true),
        () => setIsMobile(isElementVisible([{ min: 0, max: desktopFirstSizes.tablet }], width)),
    );

    return (
        <AdvertsStyled className={props.className} withGap={props.withGap}>
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

    return false;
};

export default Adverts;
