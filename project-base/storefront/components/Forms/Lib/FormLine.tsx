import { HTMLAttributes } from 'react';
import { ExtractNativePropsFromDefault } from 'types/ExtractNativePropsFromDefault';
import { twMergeCustom } from 'utils/twMerge';

type NativeProps = ExtractNativePropsFromDefault<HTMLAttributes<HTMLDivElement>, never, 'style'>;

export type FormLineWidth = 'half' | 'narrow' | 'wide';

const formLineWidthClassNameByWidth: Record<FormLineWidth, string> = {
    half: 'col-span-12 md:col-span-6',
    narrow: 'col-span-12  md:col-span-5 vl:col-span-4',
    wide: 'col-span-12  md:col-span-7 vl:col-span-8',
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
