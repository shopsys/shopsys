import { twMergeCustom } from 'helpers/twMerge';
import NextImage, { ImageProps as NextImageProps } from 'next/image';
import { useState } from 'react';

type ImageProps = {
    src: NextImageProps['src'] | undefined | null;
} & Omit<NextImageProps, 'src'>;

export const Image: FC<ImageProps> = ({ src, className, ...props }) => {
    const [imageUrl, setImageUrl] = useState(src ?? '/images/optimized-noimage.webp');

    return (
        <NextImage
            className={twMergeCustom('[image-rendering:-webkit-optimize-contrast]', className)}
            loader={({ src, width }) => `${src}?width=${width || '0'}`}
            src={imageUrl}
            onError={() => setImageUrl('/images/optimized-noimage.webp')}
            {...props}
        />
    );
};
