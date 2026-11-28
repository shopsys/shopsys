import { ColorPreview } from 'components/Basic/ColorPreview/ColorPreview';
import { CheckmarkIcon } from 'components/Basic/Icon/CheckmarkIcon';
import { LabelHTMLAttributes } from 'react';
import { ExtractNativePropsFromDefault } from 'types/ExtractNativePropsFromDefault';
import { getYIQContrastTextColor } from 'utils/colors/colors';
import { twMergeCustom } from 'utils/twMerge';

type NativeProps = ExtractNativePropsFromDefault<LabelHTMLAttributes<HTMLLabelElement>, never, 'htmlFor'>;

type ColorLabelWrapperProps = NativeProps & {
    label?: string;
    bgColor: string;
    imageUrl?: string;
    count?: number;
    isCountAbsolute?: boolean;
    checked?: boolean;
    disabled?: boolean;
};

export const ColorLabelWrapper: FC<ColorLabelWrapperProps> = ({
    label,
    bgColor,
    imageUrl,
    count,
    isCountAbsolute = true,
    disabled,
    checked,
    htmlFor,
    children,
}) => (
    <div className="relative w-full select-none">
        {children}
        <label
            htmlFor={htmlFor}
            className={twMergeCustom(
                'group relative flex w-full cursor-pointer items-center gap-2 font-semibold text-input-text-default text-sm',
                disabled && 'cursor-no-drop text-input-text-disabled opacity-60',
                'rounded-detail outline-hidden',
            )}
        >
            <ColorPreview
                imageAlt={label ?? ''}
                imageClassName="p-0.5"
                imageUrl={imageUrl}
                isDecorative
                rgbHex={bgColor}
                // hover shows the same halo as a checkbox; the selected outline has a gap so it stays visible on dark swatches
                className={twMergeCustom(
                    'size-7 border border-icon-default bg-input-bg-default outline-offset-2 transition',
                    checked && 'outline-2 outline-text-default',
                    !disabled && 'choice-hover:ring-4 choice-hover:ring-input-glow-hovered',
                    'choice-focus-visible:shadow-[0_0_0_2px_var(--color-focus-inner-ring)] choice-focus-visible:outline-2 choice-focus-visible:outline-focus-ring',
                )}
            >
                {imageUrl && (
                    <div
                        className={twMergeCustom(
                            'absolute inset-0 bg-black/50 opacity-0 transition',
                            checked && 'opacity-100',
                        )}
                    />
                )}
                <CheckmarkIcon
                    className={twMergeCustom(
                        'absolute h-full opacity-0 transition',
                        checked && 'opacity-100',
                        imageUrl ? 'text-white' : getYIQContrastTextColor(bgColor),
                    )}
                />
            </ColorPreview>
            <div className="flex w-full justify-between">
                <div className="w-full">{label}</div>
                {!!count && !checked && (
                    <div className="ml-auto font-normal text-text-less">
                        ({!isCountAbsolute && '+'}
                        {count})
                    </div>
                )}
            </div>
        </label>
    </div>
);
