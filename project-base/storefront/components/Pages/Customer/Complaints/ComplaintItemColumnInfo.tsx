import { ReactNode } from 'react';
import { twMergeCustom } from 'utils/twMerge';

type ComplaintItemColumnInfoProps = {
    title: string;
    value: ReactNode;
    valueClassName?: string;
    wrapperClassName?: string;
    tid?: string;
};

export const ComplaintItemColumnInfo: FC<ComplaintItemColumnInfoProps> = ({
    title,
    value,
    valueClassName,
    wrapperClassName,
    tid,
}) => {
    return (
        <div className={twMergeCustom('flex flex-col gap-1', wrapperClassName)}>
            <span className="text-sm">{title}</span>
            <span className={twMergeCustom('font-bold', valueClassName)} data-tid={tid}>
                {value}
            </span>
        </div>
    );
};
