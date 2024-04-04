import { HTMLAttributes } from 'react';
import { ExtractNativePropsFromDefault } from 'types/ExtractNativePropsFromDefault';
import { twMergeCustom } from 'utils/twMerge';

type NativeProps = ExtractNativePropsFromDefault<HTMLAttributes<HTMLDivElement>, never, 'style'>;

type ChoiceFormLineProps = NativeProps;

export const ChoiceFormLine: FC<ChoiceFormLineProps> = ({ children, style, className }) => (
    <div className={twMergeCustom('mb-4 w-fit', className)} style={style}>
        {children}
    </div>
);
