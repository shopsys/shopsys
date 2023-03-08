import { twJoin } from 'tailwind-merge';

export const FilterGroupWrapper: FC = ({ children, dataTestId }) => (
    <div className="border-b border-border" data-testid={dataTestId}>
        {children}
    </div>
);

export const FilterGroupTitle: FC<{ onClick: () => void }> = ({ children, onClick }) => (
    <div className="relative block cursor-pointer py-6 pr-5 font-bold uppercase text-black" onClick={onClick}>
        {children}
    </div>
);

export const FilterGroupContent: FC<{ isOpen: boolean }> = ({ children, isOpen }) => (
    <div className={twJoin('mb-6 flex-col flex-wrap', isOpen ? 'flex' : 'hidden')}>{children}</div>
);

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

export const SelectedParametersName: FC = ({ children }) => <p className="mb-2 ml-2 py-2 text-sm">{children}:</p>;

export const SelectedParametersList: FC = ({ children }) => <ul className="flex flex-wrap">{children}</ul>;

export const SelectedParametersListItem: FC = ({ children, dataTestId }) => (
    <li className="mb-2 ml-2 rounded bg-creamWhite px-3 py-2 text-sm text-dark" data-testid={dataTestId}>
        {children}
    </li>
);
