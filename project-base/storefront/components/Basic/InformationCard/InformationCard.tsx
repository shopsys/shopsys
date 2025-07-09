import React from 'react';
import { twMergeCustom } from 'utils/twMerge';

type InformationCardProps = {
    icon: React.ReactNode;
    heading: string;
};

export const InformationCard: FC<InformationCardProps> = ({ children, icon, heading, className }) => {
    return (
        <div className={twMergeCustom('flex flex-col gap-3', className)}>
            <div className="flex items-center gap-3">
                {icon}
                <span className="h5">{heading}</span>
            </div>

            <div className="flex flex-col text-sm">{children}</div>
        </div>
    );
};
