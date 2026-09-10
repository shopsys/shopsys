import { Image } from 'components/Basic/Image/Image';
import { ReactNode } from 'react';
import { twMergeCustom } from 'utils/twMerge';

type ColorPreviewProps = {
    rgbHex: string | null | undefined;
    imageUrl: string | null | undefined;
    imageAlt: string;
    className?: string;
    imageClassName?: string;
    isDecorative?: boolean;
    children?: ReactNode;
};

export const ColorPreview: FC<ColorPreviewProps> = ({
    rgbHex,
    imageUrl,
    imageAlt,
    className,
    imageClassName,
    isDecorative,
    children,
}) => {
    const hasImage = !!imageUrl;
    const hasColor = rgbHex && rgbHex !== '';

    if (!hasImage && !hasColor) {
        return null;
    }

    return (
        <div
            aria-hidden={isDecorative ? 'true' : undefined}
            style={{ backgroundColor: hasImage ? undefined : (rgbHex ?? undefined) }}
            className={twMergeCustom(
                'relative flex size-4 shrink-0 justify-center overflow-hidden rounded-sm',
                !hasImage && 'border border-icon-default',
                className,
            )}
        >
            {hasImage && (
                <Image
                    alt={isDecorative ? '' : imageAlt}
                    className={twMergeCustom('size-full object-cover', imageClassName)}
                    height={16}
                    src={imageUrl}
                    width={16}
                />
            )}
            {children}
        </div>
    );
};
