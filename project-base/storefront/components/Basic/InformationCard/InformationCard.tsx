import React from 'react';
import { twMergeCustom } from 'utils/twMerge';

type InformationCardProps = {
    icon: React.ReactNode;
    heading: string;
};

export const InformationCard: FC<InformationCardProps> = ({ children, icon, heading, className }) => {
    return (
        <div className={twMergeCustom('flex min-w-60 flex-col gap-3', className)}>
            <div className="flex items-center gap-3">
                {icon}
                <h5>{heading}</h5>
            </div>

            <div className="flex flex-col text-sm">{children}</div>
        </div>
    );
};
