import { ImageSizeFragmentApi, ImageSizesFragmentApi } from 'graphql/generated';
import { ImgHTMLAttributes } from 'react';
import { CSSProperties } from 'styled-components';
import { twMergeCustom } from 'utils/twMerge';

type ImageProps = {
    image: ImageSizesFragmentApi | null;
    alt: string;
    type: string;
    loading?: ImgHTMLAttributes<HTMLImageElement>['loading'];
    testIdentifier?: string;
    maxWidth?: CSSProperties['maxWidth'];
    maxHeight?: CSSProperties['maxHeight'];
};

const getTestIdentifier = (testIdentifier?: string) => testIdentifier ?? 'basic-image';

export const Image: FC<ImageProps> = ({
    image,
    alt,
    type,
    loading,
    testIdentifier,
    maxWidth,
    maxHeight,
    className,
}) => {
    const img: ImageSizeFragmentApi | null = image?.sizes.find((i) => i.size === type) ?? null;

    if (img === null) {
        return (
            <img
                src="/images/optimized-noimage.png"
                alt={alt}
                data-testid={getTestIdentifier(testIdentifier) + '-empty'}
                className={className}
            />
        );
    }

    return (
        <picture data-testid={getTestIdentifier(testIdentifier)}>
            {img.additionalSizes.map((size) => (
                <source key={size.url} srcSet={size.url} media={size.media} />
            ))}
            <img
                className={twMergeCustom('responsive-image block w-full object-contain', className)}
                style={{
                    maxWidth: maxWidth ?? (img.width !== null ? `${img.width}px` : undefined),
                    maxHeight: maxHeight ?? (img.height !== null ? `${img.height}px` : undefined),
                }}
                src={img.url}
                alt={alt}
                loading={loading}
            />
        </picture>
    );
};
