import NextImage, { ImageLoader, ImageProps as NextImageProps } from 'next/image';
import { memo, SyntheticEvent, useCallback, useEffect, useState } from 'react';

type ImageProps = {
    src: NextImageProps['src'] | undefined | null;
    hash?: string;
} & Omit<NextImageProps, 'src'> &
    React.RefAttributes<HTMLImageElement | null>;

const fallbackImageSrc = '/images/optimized-noimage.webp';

const ImageComponent: FC<ImageProps> = ({ src, hash, tid, ...props }) => {
    const imageUrl = src ?? null;
    const [error, setError] = useState<SyntheticEvent<HTMLImageElement, Event> | null>(null);
    const shouldLoadFallbackImage = !!error || !imageUrl;
    const onError = useCallback((err: SyntheticEvent<HTMLImageElement, Event> | null) => setError(err), []);

    const loader = useCallback<ImageLoader>(
        ({ src, width }) => {
            if (shouldLoadFallbackImage) {
                return src;
            }

            return `${src}?width=${width || '0'}${hash ? `&${hash}` : ''}`;
        },
        [hash],
    );

    const finalImageUrl = shouldLoadFallbackImage ? fallbackImageSrc : imageUrl;

    // Extract src from StaticImageData object if needed
    const getSrcFromImageUrl = (imageUrl: typeof finalImageUrl): string => {
        if (typeof imageUrl === 'string') {
            return imageUrl;
        }

        // Handle StaticImageData objects that have src property
        if (typeof imageUrl === 'object') {
            return (imageUrl as any).src || '';
        }

        return '';
    };

    const finalSrc = getSrcFromImageUrl(finalImageUrl);

    useEffect(() => {
        setError(null);
    }, [src]);

    return (
        <NextImage
            data-tid={tid}
            loader={loader}
            overrideSrc={finalSrc}
            src={finalImageUrl}
            unoptimized={shouldLoadFallbackImage}
            onError={onError}
            {...props}
        />
    );
};

export const Image = memo(ImageComponent);
