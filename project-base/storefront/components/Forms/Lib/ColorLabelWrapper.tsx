import { CheckmarkIcon } from 'components/Basic/Icon/CheckmarkIcon';
import { LabelHTMLAttributes } from 'react';
import { ExtractNativePropsFromDefault } from 'types/ExtractNativePropsFromDefault';
import { twMergeCustom } from 'utils/twMerge';

type NativeProps = ExtractNativePropsFromDefault<LabelHTMLAttributes<HTMLLabelElement>, never, 'htmlFor'>;

type ColorLabelWrapperProps = NativeProps & {
    label?: string;
    isLightColor: boolean;
    bgColor: string;
    count?: number;
    checked?: boolean;
    disabled?: boolean;
};

export const ColorLabelWrapper: FC<ColorLabelWrapperProps> = ({
    label,
    isLightColor,
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
                'text-inputText group relative flex w-full cursor-pointer items-center gap-2 text-sm font-semibold',
                disabled && 'text-inputTextDisabled cursor-no-drop opacity-60',
            )}
        >
            <div
                style={{ backgroundColor: bgColor }}
                className={twMergeCustom(
                    'border-text bg-inputBackground flex size-7 shrink-0 justify-center rounded-sm border transition',
                    checked
                        ? 'border-text'
                        : 'group-hover:border-inputBorderHovered group-active:border-inputBorderHovered',
                    disabled &&
                        'border-inputBorderDisabled group-hover:border-inputBorderDisabled group-hover:bg-inputBorderDisabled group-active:border-inputBorderDisabled outline-0 group-active:outline-0 active:scale-100',
                    disabled && checked && 'bg-inputBorderDisabled group-hover:bg-inputBorderDisabled',
                )}
            >
                <CheckmarkIcon
                    className={twMergeCustom(
                        'h-full opacity-0 transition',
                        checked && 'opacity-100',
                        isLightColor ? 'text-text-default' : 'text-text-inverted',
                        disabled && 'text-inputTextDisabled',
                    )}
                />
            </div>
            <div className="flex w-full justify-between">
                <div className="w-full">{label}</div>
                {!!count && !checked && <div className="text-inputPlaceholder ml-auto font-normal">({count})</div>}
            </div>
        </label>
    </div>
);
