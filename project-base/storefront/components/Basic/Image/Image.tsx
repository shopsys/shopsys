import { ImageSizeFragmentApi, ImageSizesFragmentApi } from 'graphql/generated';
import { ImgHTMLAttributes } from 'react';
import { twMergeCustom } from 'helpers/visual/twMerge';

type ImageProps = {
    image: ImageSizesFragmentApi | null;
    alt: string | null | undefined;
    type: string;
    loading?: ImgHTMLAttributes<HTMLImageElement>['loading'];
    width?: string | number;
    height?: string | number;
};

const getDataTestId = (dataTestId?: string) => dataTestId ?? 'basic-image';

export const Image: FC<ImageProps> = ({ image, alt, type, loading, dataTestId, width, height, className }) => {
    const img: ImageSizeFragmentApi | null = image?.sizes.find((i) => i.size === type) ?? null;

    const classNameTwClass = twMergeCustom(
        'block object-contain [image-rendering:-webkit-optimize-contrast] max-w-full max-h-full',
        className,
    );

    if (!img) {
        return (
            <img
                src="/images/optimized-noimage.webp"
                alt={alt || ''}
                data-testid={getDataTestId(dataTestId) + '-empty'}
                height={height || 160}
                width={width || 160}
                className={twMergeCustom(classNameTwClass, 'h-auto w-full')}
            />
        );
    }

    return (
        <picture data-testid={getDataTestId(dataTestId)}>
            {img.additionalSizes.map((size) => (
                <source key={size.url} srcSet={size.url} media={size.media} />
            ))}
            <img
                className={classNameTwClass}
                width={width ?? (img.width !== null ? `${img.width}px` : undefined)}
                height={height ?? (img.height !== null ? `${img.height}px` : undefined)}
                src={img.url}
                alt={alt || ''}
                loading={loading}
            />
        </picture>
    );
};
