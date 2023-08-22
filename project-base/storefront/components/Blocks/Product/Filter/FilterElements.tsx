import { twJoin } from 'tailwind-merge';

export const FilterGroupWrapper: FC = ({ children, dataTestId }) => (
    <div className="border-b border-border last:border-none" data-testid={dataTestId}>
        {children}
    </div>
);

export const FilterGroupTitle: FC<{ onClick: () => void }> = ({ children, onClick }) => (
    <div className="relative block cursor-pointer py-6 pr-5 font-bold uppercase text-black" onClick={onClick}>
        {children}
    </div>
);

export const FilterGroupContent: FC = ({ children }) => <div className="mb-6 flex flex-col flex-wrap">{children}</div>;

export const FilterGroupContentItem: FC<{ isDisabled: boolean }> = ({ children, isDisabled, dataTestId }) => (
    <div className={twJoin('mb-3', isDisabled && 'pointer-events-none opacity-30')} data-testid={dataTestId}>
        {children}
    </div>
);

export const ShowAllButton: FC<{ onClick: () => void }> = ({ children, onClick }) => (
    <button
        className="w-fit cursor-pointer border-none bg-none p-0 text-sm text-black underline outline-none hover:bg-none hover:text-primary hover:no-underline"
        onClick={onClick}
    >
        {children}
    </button>
);

export const SelectedParametersName: FC = ({ children }) => <p className="py-2 text-sm">{children}</p>;

export const SelectedParametersList: FC = ({ children }) => <ul className="flex flex-wrap gap-2">{children}</ul>;

export const SelectedParametersListItem: FC = ({ children, dataTestId }) => (
    <li className="rounded bg-creamWhite p-2 text-sm text-dark" data-testid={dataTestId}>
        {children}
    </li>
);
