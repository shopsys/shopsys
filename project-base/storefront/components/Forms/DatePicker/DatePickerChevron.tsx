import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { type CSSProperties, type ReactElement } from 'react';
import { twMergeCustom } from 'utils/twMerge';

type DatePickerChevronProps = {
    className?: string;
    disabled?: boolean;
    orientation?: 'left' | 'right' | 'up' | 'down';
    size?: number;
    style?: CSSProperties;
};

export const DatePickerChevron = ({ className, orientation }: DatePickerChevronProps): ReactElement => {
    return (
        <ArrowIcon
            className={twMergeCustom(
                'size-4',
                orientation === 'left' && 'rotate-90',
                orientation === 'right' && '-rotate-90',
                orientation === 'up' && 'rotate-180',
                className,
            )}
        />
    );
};
