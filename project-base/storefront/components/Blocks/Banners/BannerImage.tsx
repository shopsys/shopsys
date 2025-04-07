import { getImageProps } from 'next/image';
import { DragEvent } from 'react';
import Head from 'next/head';

type BannerImageProps = {
    mobileSrc: string;
    desktopSrc: string;
    mobileAlt: string;
    desktopAlt: string;
    isFirst: boolean;
};

export const BannerImage: FC<BannerImageProps> = ({
    mobileSrc,
    desktopSrc,
    mobileAlt,
    desktopAlt,
    isFirst,
    children,
}) => {
    const common = {
        fill: true,
        priority: isFirst,
        onDragStart: (e: DragEvent<HTMLImageElement>) => e.preventDefault(),
        loader: ({ src }: { src: string }) => `${src}`,
        unoptimized: true,
    };

    const {
        props: { src: desktop },
    } = getImageProps({
        ...common,
        alt: desktopAlt,
        src: desktopSrc,
    });
    const {
        props: { src: mobile, ...rest },
    } = getImageProps({
        ...common,
        alt: mobileAlt,
        src: mobileSrc,
    });

    return (
        <>
            {isFirst && (
                <Head>
                    <link key="carousel_preload_mobile" as="image" fetchPriority="high" href={mobile + '?width=480'}
                        media="(max-width: 769px)" rel="preload"
                    />
                    <link key="carousel_preload_desktop" as="image" fetchPriority="high" href={desktop + '?width=1400'}
                          media="(min-width: 770px)" rel="preload"
                    />
                </Head>
            )}
            <div className="vl:h-[425px] relative h-[250px] w-full grow md:h-[345px]">
                <picture>
                    <source media="(min-width: 769px)" srcSet={desktop + '?width=1400'} />
                    <source media="(max-width: 769px)" srcSet={mobile + '?width=480'} />
                    <img {...rest} className="h-full w-full object-cover" src={mobile} />
                </picture>
                {children}
            </div>
        </>
    );
};
