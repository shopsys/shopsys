import { HTMLAttributes } from 'react';
import { ExtractNativePropsFromDefault } from 'types/ExtractNativePropsFromDefault';
import { twMergeCustom } from 'utils/twMerge';

type NativeProps = ExtractNativePropsFromDefault<HTMLAttributes<HTMLDivElement>, never, 'style'>;

export type FormLineWidth = 'half' | 'narrow' | 'wide';

const formLineWidthClassNameByWidth: Record<FormLineWidth, string> = {
    half: 'col-span-12 md:col-span-6',
    narrow: 'col-span-12  md:col-span-4 vl:col-span-3',
    wide: 'col-span-12  md:col-span-8 vl:col-span-9',
};

type FormLineProps = NativeProps & {
    width?: FormLineWidth;
};

export const FormLine: FC<FormLineProps> = ({ children, className, width, ...props }) => (
    <div
        className={twMergeCustom('w-full', width ? formLineWidthClassNameByWidth[width] : undefined, className)}
        {...props}
    >
        {children}
    </div>
);
