import { AdvertsLinkStyled, AdvertsStyled } from './Adverts.style';
import { FC, Fragment, HTMLAttributes, useState } from 'react';
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
                    item.positionName === props.positionName &&
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

export default Adverts;
