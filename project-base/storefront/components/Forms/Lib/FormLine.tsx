import { HTMLAttributes } from 'react';
import { ExtractNativePropsFromDefault } from 'types/ExtractNativePropsFromDefault';
import { twMergeCustom } from 'utils/twMerge';

type NativeProps = ExtractNativePropsFromDefault<HTMLAttributes<HTMLDivElement>, never, 'style'>;

type FormLineProps = NativeProps & {
    bottomGap?: boolean;
    isHalfWidthInput?: boolean;
    isSmallInput?: boolean;
};

export const FormLine: FC<FormLineProps> = ({
    bottomGap,
    children,
    className,
    isHalfWidthInput,
    isSmallInput,
    ...props
}) => (
    <div
        className={twMergeCustom(
            'flex-1',
            bottomGap && 'pb-2.5',
            isHalfWidthInput && 'vl:w-1/2 vl:pr-1.5',
            isSmallInput && 'w-[150px] flex-none vl:w-[220px]',
            className,
        )}
        {...props}
    >
        {children}
    </div>
);
