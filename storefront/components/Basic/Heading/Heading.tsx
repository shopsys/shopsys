import { HTMLAttributes } from 'react';
import { twJoin } from 'tailwind-merge';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';
import { twMergeCustom } from 'utils/twMerge';

type NativeProps = ExtractNativePropsFromDefault<HTMLAttributes<HTMLHeadingElement>, never, 'style' | 'onClick'>;
type HeadingType = 'h1' | 'h2' | 'h3' | 'h4';

export type HeadingProps = NativeProps & {
    //  A enumerator-like list of all available typeHeadings of the custom Heading element
    type: HeadingType;
    'data-testid'?: string;
};

const getDataTestId = (type: HeadingType) => 'basic-heading-' + type;

// A global heading element, which takes a "type" prop, and based on that displays a heading of type h1 - h4
export const Heading: FC<HeadingProps> = ({ type, type: HeadingTag, className, ...props }) => (
    <HeadingTag
        className={twMergeCustom(
            twJoin(
                'mb-3 break-words font-bold text-dark ',
                type === 'h1' && 'text-2xl lg:mb-4 lg:text-3xl',
                type === 'h2' && 'text-lg lg:text-2xl',
                type === 'h3' && 'text-base lg:text-lg',
                type === 'h4' && 'text-sm lg:text-base',
            ),
            className,
        )}
        data-testid={getDataTestId(type)}
        {...props}
    />
);
