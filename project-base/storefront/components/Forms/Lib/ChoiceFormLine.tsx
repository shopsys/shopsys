import { twMergeCustom } from 'helpers/twMerge';
import { HTMLAttributes } from 'react';
import { ExtractNativePropsFromDefault } from 'types/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<HTMLAttributes<HTMLDivElement>, never, 'style'>;

type ChoiceFormLineProps = NativeProps;

export const ChoiceFormLine: FC<ChoiceFormLineProps> = ({ children, style, className }) => (
    <div className={twMergeCustom('mb-4 w-fit', className)} style={style}>
        {children}
    </div>
);
