import { ImageSizeFragmentApi, ImageSizesFragmentApi } from 'graphql/generated';
import { ImgHTMLAttributes } from 'react';
import { twMergeCustom } from 'utils/twMerge';

type ImageProps = {
    image: ImageSizesFragmentApi | null;
    alt: string | null | undefined;
    type: string;
    loading?: ImgHTMLAttributes<HTMLImageElement>['loading'];
    maxWidth?: string | number;
    maxHeight?: string | number;
};

const getDataTestId = (dataTestId?: string) => dataTestId ?? 'basic-image';

export const Image: FC<ImageProps> = ({ image, alt, type, loading, dataTestId, maxWidth, maxHeight, className }) => {
    const img: ImageSizeFragmentApi | null = image?.sizes.find((i) => i.size === type) ?? null;

    if (img === null) {
        return (
            <img
                src="/images/optimized-noimage.webp"
                alt={alt || ''}
                data-testid={getDataTestId(dataTestId) + '-empty'}
                className={className}
            />
        );
    }

    return (
        <picture data-testid={getDataTestId(dataTestId)}>
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
                alt={alt || ''}
                loading={loading}
            />
        </picture>
    );
};
