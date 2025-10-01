import { HTMLAttributes } from 'react';
import { ExtractNativePropsFromDefault } from 'types/ExtractNativePropsFromDefault';
import { twMergeCustom } from 'utils/twMerge';

type NativeProps = ExtractNativePropsFromDefault<HTMLAttributes<HTMLDivElement>, never, 'style'>;

export const FormLine: FC<NativeProps> = ({ children, className, ...props }) => (
    <div className={twMergeCustom('w-full', className)} {...props}>
        {children}
    </div>
);
