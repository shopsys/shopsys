import { Img } from './Image.style';
import { FC, ImgHTMLAttributes } from 'react';
import { CSSProperties } from 'styled-components';
import { ImageSizeType, ImageType } from 'types/image';

type ImageProps = {
    image: ImageType | null;
    alt: string;
    type: string;
    loading?: ImgHTMLAttributes<HTMLImageElement>['loading'];
    testIdentifier?: string;
    maxWidth?: CSSProperties['maxWidth'];
    maxHeight?: CSSProperties['maxHeight'];
};

const getTestIdentifier = (testIdentifier?: string) => testIdentifier ?? 'basic-image';

export const Image: FC<ImageProps> = ({ image, alt, type, loading, testIdentifier, maxWidth, maxHeight }) => {
    const img: ImageSizeType | null = image?.sizes?.find((i) => i.size === type) ?? null;

    if (img === null) {
        return (
            <img
                src={'/images/optimized-noimage.png'}
                alt={alt}
                data-testid={getTestIdentifier(testIdentifier) + '-empty'}
            />
        );
    }

    return (
        <picture data-testid={getTestIdentifier(testIdentifier)}>
            {img.additionalSizes.map((size) => (
                <source key={size.url} srcSet={size.url} media={size.media} />
            ))}
            <Img
                className="responsive-image"
                src={img.url}
                alt={alt}
                loading={loading}
                maxWidth={maxWidth ?? (img.width !== null ? `${img.width}px` : undefined)}
                maxHeight={maxHeight ?? (img.height !== null ? `${img.height}px` : undefined)}
            />
        </picture>
    );
};
