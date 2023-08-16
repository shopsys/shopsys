import { HTMLAttributes } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';
import { twMergeCustom } from 'helpers/twMerge';

type NativeProps = ExtractNativePropsFromDefault<HTMLAttributes<HTMLDivElement>, never, 'style'>;

type FormLineProps = NativeProps & {
    bottomGap?: boolean;
};

const TEST_IDENTIFIER = 'form-line';

export const FormLine: FC<FormLineProps> = ({ bottomGap, children, className, ...props }) => (
    <div data-testid={TEST_IDENTIFIER} className={twMergeCustom('flex-1', bottomGap && 'pb-3', className)} {...props}>
        {children}
    </div>
);
