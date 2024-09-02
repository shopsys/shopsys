import NextImage, { ImageProps as NextImageProps } from 'next/image';
import { useState } from 'react';

type ImageProps = {
    src: NextImageProps['src'] | undefined | null;
    hash?: string;
} & Omit<NextImageProps, 'src'>;

export const Image: FC<ImageProps> = ({ src, hash, ...props }) => {
    const [imageUrl, setImageUrl] = useState(src ?? '/images/optimized-noimage.webp');

    return (
        <NextImage
            loader={({ src, width }) => `${src}?width=${width || '0'}${hash ? `&${hash}` : ''}`}
            src={imageUrl}
            onError={() => setImageUrl('/images/optimized-noimage.webp')}
            {...props}
        />
    );
};
