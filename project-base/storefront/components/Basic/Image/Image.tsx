import { ImageSizeFragmentApi, ImageSizesFragmentApi } from 'graphql/generated';
import { twMergeCustom } from 'helpers/twMerge';
import { ImgHTMLAttributes } from 'react';

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
        'object-contain [image-rendering:-webkit-optimize-contrast] max-w-full max-h-full mx-auto',
        className,
    );

    if (!img) {
        return (
            <img
                alt={alt || ''}
                className={twMergeCustom('h-auto w-full', imageTwClass)}
                data-testid={getDataTestId(dataTestId) + '-empty'}
                height={height || 160}
                src="/images/optimized-noimage.webp"
                width={width || 160}
            />
        );
    }

    return (
        <picture className={wrapperClassName}>
            {img.additionalSizes.map((size) => (
                <source key={size.url} media={size.media} srcSet={size.url} />
            ))}
            <img
                alt={alt || ''}
                className={imageTwClass}
                height={height ?? (img.height !== null ? img.height : undefined)}
                loading={loading}
                src={img.url}
                width={width ?? (img.width !== null ? img.width : undefined)}
            />
        </picture>
    );
};
