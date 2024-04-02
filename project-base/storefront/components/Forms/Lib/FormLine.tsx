import { HTMLAttributes } from 'react';
import { ExtractNativePropsFromDefault } from 'types/ExtractNativePropsFromDefault';
import { twMergeCustom } from 'utils/twMerge';

type NativeProps = ExtractNativePropsFromDefault<HTMLAttributes<HTMLDivElement>, never, 'style'>;

type FormLineProps = NativeProps & {
    bottomGap?: boolean;
};

export const FormLine: FC<FormLineProps> = ({ bottomGap, children, className, ...props }) => (
    <div className={twMergeCustom('flex-1', bottomGap && 'pb-3', className, 'form-line')} {...props}>
        {children}
    </div>
);
