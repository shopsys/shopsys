import { Tooltip } from 'components/Basic/Tooltip/Tooltip';
import { LabelHTMLAttributes } from 'react';
import { twJoin } from 'tailwind-merge';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<LabelHTMLAttributes<HTMLLabelElement>, never, 'htmlFor'>;

type ColorLabelWrapperProps = NativeProps & {
    label?: string;
    isLightColor: boolean;
    bgColor: string;
    isDisabled?: boolean;
    isActive: boolean;
};

export const ColorLabelWrapper: FC<ColorLabelWrapperProps> = ({
    label,
    isLightColor,
    bgColor,
    isDisabled,
    isActive,
    htmlFor,
    children,
}) => (
    <div className="relative mr-1 mb-1 flex w-6">
        {children}
        <Tooltip label={label}>
            <label
                htmlFor={htmlFor}
                style={{ backgroundColor: bgColor }}
                className={twJoin(
                    'relative block h-6 w-6 cursor-pointer rounded-full',
                    'after:absolute after:top-1/2 after:left-1/2 after:flex after:-translate-y-1/2 after:-translate-x-1/2 after:items-center after:justify-center after:text-lg after:opacity-0 after:content-["✕"]',
                    'peer-checked:after:pointer-events-none peer-checked:after:absolute peer-checked:after:left-[12px] peer-checked:after:top-[11px] peer-checked:after:h-[10px] peer-checked:after:w-[6px] peer-checked:after:rotate-45 peer-checked:after:border-r-2 peer-checked:after:border-b-2 peer-checked:after:opacity-100 peer-checked:after:content-[""]',
                    isDisabled && !isActive && 'pointer-events-none opacity-30 after:opacity-100',
                    isLightColor
                        ? 'after:text-white peer-checked:after:border-black'
                        : 'peer-checked:after:border-white',
                )}
            />
        </Tooltip>
    </div>
);
