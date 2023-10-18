import { twMergeCustom } from 'helpers/twMerge';
import { HTMLAttributes } from 'react';
import { ExtractNativePropsFromDefault } from 'types/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<HTMLAttributes<HTMLDivElement>, never, 'style'>;

type FormLineProps = NativeProps & {
    bottomGap?: boolean;
};

const TEST_IDENTIFIER = 'form-line';

export const FormLine: FC<FormLineProps> = ({ bottomGap, children, className, ...props }) => (
    <div className={twMergeCustom('flex-1', bottomGap && 'pb-3', className)} data-testid={TEST_IDENTIFIER} {...props}>
        {children}
    </div>
);
