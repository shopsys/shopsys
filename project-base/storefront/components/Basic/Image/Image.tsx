import { ImageSizeFragmentApi, ImageSizesFragmentApi } from 'graphql/generated';
import { ImgHTMLAttributes } from 'react';
import { twMergeCustom } from 'helpers/twMerge';

type ImageProps = {
    image: ImageSizesFragmentApi | null | undefined;
    alt: string | null | undefined;
    type: string;
    loading?: ImgHTMLAttributes<HTMLImageElement>['loading'];
    width?: string | number;
    height?: string | number;
    wrapperClassName?: string;
};

const getDataTestId = (dataTestId?: string) => dataTestId ?? 'basic-image';

export const Image: FC<ImageProps> = ({
    image,
    alt,
    type,
    loading,
    dataTestId,
    width,
    height,
    className,
    wrapperClassName,
}) => {
    const img: ImageSizeFragmentApi | null = image?.sizes.find((i) => i.size === type) ?? null;

    const imageTwClass = twMergeCustom(
        'object-contain [image-rendering:-webkit-optimize-contrast] max-w-full max-h-full',
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
                className={twMergeCustom('h-auto w-full', imageTwClass)}
            />
        );
    }

    return (
        <picture className={twMergeCustom('flex items-center justify-center', wrapperClassName)}>
            {img.additionalSizes.map((size) => (
                <source key={size.url} srcSet={size.url} media={size.media} />
            ))}
            <img
                className={imageTwClass}
                width={width ?? (img.width !== null ? img.width : undefined)}
                height={height ?? (img.height !== null ? img.height : undefined)}
                src={img.url}
                alt={alt || ''}
                loading={loading}
            />
        </picture>
    );
};
