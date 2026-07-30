import NextImage, { ImageLoader, ImageProps as NextImageProps } from 'next/image';
import { SyntheticEvent, useEffect, useState } from 'react';

type ImageProps = {
    src: NextImageProps['src'] | undefined | null;
    hash?: string;
} & Omit<NextImageProps, 'src'> &
    React.RefAttributes<HTMLImageElement | null>;

const fallbackImageSrc = '/images/noimage.webp';

// Extract src from StaticImageData object if needed
const getSrcFromImageUrl = (imageUrl: NextImageProps['src']): string => {
    if (typeof imageUrl === 'string') {
        return imageUrl;
    }

    // Handle StaticImageData objects that have src property
    if (typeof imageUrl === 'object') {
        return (imageUrl as any).src || '';
    }

    return '';
};

export const Image: FC<ImageProps> = ({ src, hash, tid, unoptimized, ...props }) => {
    const imageUrl = src ?? null;
    const [error, setError] = useState<SyntheticEvent<HTMLImageElement, Event> | null>(null);
    const shouldLoadFallbackImage = !!error || !imageUrl;
    const onError = (err: SyntheticEvent<HTMLImageElement, Event> | null) => setError(err);

    const loader: ImageLoader = ({ src, width }) => {
        if (shouldLoadFallbackImage) {
            return src;
        }

        return `${src}?width=${width || '0'}${hash ? `&${hash}` : ''}`;
    };

    const finalImageUrl = shouldLoadFallbackImage ? fallbackImageSrc : imageUrl;

    const finalSrc = getSrcFromImageUrl(finalImageUrl);
    const shouldSkipOptimization = unoptimized || shouldLoadFallbackImage || finalSrc.split('?', 1)[0].endsWith('.svg');

    useEffect(() => {
        setError(null);
    }, [src]);

    return (
        <NextImage
            data-tid={tid}
            loader={loader}
            overrideSrc={finalSrc}
            src={finalImageUrl}
            unoptimized={shouldSkipOptimization}
            onError={onError}
            {...props}
        />
    );
};
