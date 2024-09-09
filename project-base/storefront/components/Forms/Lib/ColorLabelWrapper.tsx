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
                'group relative flex w-full cursor-pointer items-center gap-2 text-sm font-semibold text-inputText',
                disabled && 'cursor-no-drop text-inputTextDisabled opacity-60',
            )}
        >
            <div
                style={{ backgroundColor: bgColor }}
                className={twMergeCustom(
                    'flex size-7 border border-text bg-inputBackground justify-center transition rounded shrink-0',
                    checked
                        ? 'border-text'
                        : 'group-hover:border-inputBorderHovered group-active:border-inputBorderHovered',
                    disabled &&
                        'border-inputBorderDisabled outline-0 group-hover:border-inputBorderDisabled group-hover:bg-inputBorderDisabled group-active:border-inputBorderDisabled group-active:outline-0 active:scale-100',
                    disabled && checked && 'bg-inputBorderDisabled group-hover:bg-inputBorderDisabled',
                )}
            >
                <CheckmarkIcon
                    className={twMergeCustom(
                        'h-full opacity-0 transition',
                        checked && 'opacity-100',
                        isLightColor ? 'text-text' : 'text-textInverted',
                        disabled && 'text-inputTextDisabled',
                    )}
                />
            </div>
            <div className="w-full flex justify-between">
                <div className="w-full">{label}</div>
                {!!count && !checked && <div className="text-inputPlaceholder font-normal ml-auto">({count})</div>}
            </div>
        </label>
    </div>
);
