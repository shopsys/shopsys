import { ImageFragmentApi } from 'graphql/generated';
import { twMergeCustom } from 'helpers/twMerge';
import { ImgHTMLAttributes } from 'react';

type ImageProps = {
    image: ImageFragmentApi | null | undefined;
    alt: string | null | undefined;
    loading?: ImgHTMLAttributes<HTMLImageElement>['loading'];
    width?: string | number;
    height?: string | number;
    wrapperClassName?: string;
};

const getDataTestId = (dataTestId?: string) => dataTestId ?? 'basic-image';

export const Image: FC<ImageProps> = ({
    image,
    alt,
    loading,
    dataTestId,
    width,
    height,
    className,
    wrapperClassName,
}) => {
    const imageTwClass = twMergeCustom(
        'object-contain [image-rendering:-webkit-optimize-contrast] max-w-full max-h-full mx-auto',
        className,
    );

    if (!image) {
        return (
            <div className={wrapperClassName}>
                <img
                    alt={alt || ''}
                    className={twMergeCustom('h-auto w-full', imageTwClass)}
                    data-testid={getDataTestId(dataTestId) + '-empty'}
                    height={height || 160}
                    src="/images/optimized-noimage.webp"
                    width={width || 160}
                />
            </div>
        );
    }

    return (
        <picture className={wrapperClassName}>
            <img
                alt={image.name || alt || ''}
                className={imageTwClass}
                height={height}
                loading={loading}
                src={image.url}
                width={width}
            />
        </picture>
    );
};
