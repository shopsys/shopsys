import Head from 'next/head';
import { getImageProps } from 'next/image';
import { DragEvent } from 'react';

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
    const commonImageProps = {
        fill: true,
        priority: isFirst,
        onDragStart: (e: DragEvent<HTMLImageElement>) => e.preventDefault(),
        loader: ({ src }: { src: string }) => `${src}`,
        unoptimized: true,
    };

    const {
        props: { src: desktopImageSrc },
    } = getImageProps({
        ...commonImageProps,
        alt: desktopAlt,
        src: desktopSrc,
    });

    const {
        props: { src: mobileImageSrc, ...mobileImageProps },
    } = getImageProps({
        ...commonImageProps,
        alt: mobileAlt,
        src: mobileSrc,
    });

    return (
        <>
            {isFirst && (
                <Head>
                    <link
                        key="carousel_preload_mobile"
                        as="image"
                        fetchPriority="high"
                        href={mobileImageSrc + '?width=480'}
                        media="(max-width: 769px)"
                        rel="preload"
                    />
                    <link
                        key="carousel_preload_desktop"
                        as="image"
                        fetchPriority="high"
                        href={desktopImageSrc + '?width=1400'}
                        media="(min-width: 770px)"
                        rel="preload"
                    />
                </Head>
            )}
            <div className="vl:h-[425px] relative h-[250px] w-full grow md:h-[345px]">
                <picture>
                    <source media="(min-width: 769px)" srcSet={desktopImageSrc + '?width=1400'} />
                    <source media="(max-width: 769px)" srcSet={mobileImageSrc + '?width=480'} />
                    {/* eslint-disable-next-line jsx-a11y/alt-text */}
                    <img
                        {...mobileImageProps}
                        className="h-full w-full object-cover"
                        loading={isFirst ? 'eager' : 'lazy'}
                        src={mobileImageSrc + '?width=480'}
                    />
                </picture>
                {children}
            </div>
        </>
    );
};
