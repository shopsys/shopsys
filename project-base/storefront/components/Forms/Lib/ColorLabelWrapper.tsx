import { CheckmarkIcon } from 'components/Basic/Icon/CheckmarkIcon';
import { LabelHTMLAttributes } from 'react';
import { ExtractNativePropsFromDefault } from 'types/ExtractNativePropsFromDefault';
import { getYIQContrastTextColor } from 'utils/colors/colors';
import { twMergeCustom } from 'utils/twMerge';

type NativeProps = ExtractNativePropsFromDefault<LabelHTMLAttributes<HTMLLabelElement>, never, 'htmlFor'>;

type ColorLabelWrapperProps = NativeProps & {
    label?: string;
    bgColor: string;
    count?: number;
    checked?: boolean;
    disabled?: boolean;
};

export const ColorLabelWrapper: FC<ColorLabelWrapperProps> = ({
    label,
    bgColor,
    count,
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
                'text-input-text-default group relative flex w-full cursor-pointer items-center gap-2 text-sm font-semibold',
                disabled && 'text-input-text-disabled cursor-no-drop opacity-60',
                'rounded-sm outline-none peer-focus-visible:bg-orange-500',
            )}
        >
            <div
                style={{ backgroundColor: bgColor }}
                className={twMergeCustom(
                    'border-icon-default bg-input-bg-default flex size-7 shrink-0 justify-center rounded-sm border transition',
                    disabled && 'border-icon-default outline-0 group-active:outline-0 active:scale-100',
                )}
            >
                <CheckmarkIcon
                    className={twMergeCustom(
                        'h-full opacity-0 transition',
                        checked && 'opacity-100',
                        getYIQContrastTextColor(bgColor),
                    )}
                />
            </div>
            <div className="flex w-full justify-between">
                <div className="w-full">{label}</div>
                {!!count && !checked && (
                    <div className="text-input-placeholder-default ml-auto font-normal">({count})</div>
                )}
            </div>
        </label>
    </div>
);
