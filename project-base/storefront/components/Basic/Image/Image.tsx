import NextImage, { ImageProps as NextImageProps } from 'next/image';

type ImageProps = {
    src: NextImageProps['src'] | undefined | null;
} & Omit<NextImageProps, 'src'>;

const imagePlaceholderPath = '/images/noimage.jpg';

export const Image: FC<ImageProps> = ({ src, ...props }) => {
    return (
        <NextImage
            loader={({ src, width }) => `${src}?width=${width || '0'}`}
            src={src || imagePlaceholderPath}
            unoptimized={!src}
            {...props}
        />
    );
};
