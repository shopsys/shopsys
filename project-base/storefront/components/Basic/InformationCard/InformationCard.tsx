import React from 'react';
import { twMergeCustom } from 'utils/twMerge';

type InformationCardProps = {
    icon: React.ReactNode;
    heading: string;
};

export const InformationCard: FC<InformationCardProps> = ({ children, icon, heading, className }) => {
    return (
        <div className={twMergeCustom('flex gap-4', className)}>
            <div className="flex aspect-square min-h-[72px] min-w-[72px] h-[72px] w-[72px] items-center justify-center rounded-full bg-backgroundMore [&>svg]:h-12 [&>svg]:w-12 [&>svg]:text-backgroundAccent">
                {icon}
            </div>

            <div>
                <div className="-mt-[6px] pb-2 text-xl font-bold">{heading}</div>
                <div className="flex flex-col text-sm leading-[26px] vl:text-base">{children}</div>
            </div>
        </div>
    );
};
