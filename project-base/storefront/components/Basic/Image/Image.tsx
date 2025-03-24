import NextImage, { ImageLoader, ImageProps as NextImageProps } from 'next/image';
import { memo, SyntheticEvent, useCallback, useEffect, useState } from 'react';

type ImageProps = {
    src: NextImageProps['src'] | undefined | null;
    hash?: string;
} & Omit<NextImageProps, 'src'> &
    React.RefAttributes<HTMLImageElement | null>;

const fallbackImageSrc = '/images/optimized-noimage.webp';

const ImageComponent: FC<ImageProps> = ({ src, hash, ...props }) => {
    const imageUrl = src ?? fallbackImageSrc;
    const [error, setError] = useState<SyntheticEvent<HTMLImageElement, Event> | null>(null);

    const onError = useCallback((err: SyntheticEvent<HTMLImageElement, Event> | null) => setError(err), []);

    const loader = useCallback<ImageLoader>(
        ({ src, width }) => `${src}?width=${width || '0'}${hash ? `&${hash}` : ''}`,
        [hash],
    );

    useEffect(() => {
        setError(null);
    }, [src]);

    return <NextImage loader={loader} src={error ? fallbackImageSrc : imageUrl} onError={onError} {...props} />;
};

export const Image = memo(ImageComponent);
