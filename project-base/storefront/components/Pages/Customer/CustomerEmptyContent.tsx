import { ReactNode } from 'react';

type CustomerEmptyContentProps = {
    title: ReactNode;
    description: ReactNode;
};

export const CustomerEmptyContent: FC<CustomerEmptyContentProps> = ({ title, description }) => (
    <div className="flex flex-col items-center gap-2 rounded-xl bg-background-more px-6 py-10 text-center">
        <h2 className="h4">{title}</h2>

        <p className="text-balance text-input-placeholder-default text-sm">{description}</p>
    </div>
);
