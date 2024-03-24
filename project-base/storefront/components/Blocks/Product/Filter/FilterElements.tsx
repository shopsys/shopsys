import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { twJoin } from 'tailwind-merge';

export const FilterGroupWrapper: FC = ({ children }) => <div className="">{children}</div>;

export const FilterGroupTitle: FC<{ isOpen: boolean; title: string; onClick: () => void }> = ({
    isOpen,
    title,
    onClick,
}) => (
    <div
        className="flex cursor-pointer items-center justify-between py-6 font-bold uppercase text-black"
        onClick={onClick}
    >
        {title}
        <ArrowIcon className={twJoin('rotate-0 select-none text-xs transition', isOpen && 'rotate-180')} />
    </div>
);

export const FilterGroupContent: FC = ({ children }) => (
    <div className="flex flex-col flex-wrap gap-3 pb-6">{children}</div>
);

export const FilterGroupContentItem: FC<{ isDisabled: boolean }> = ({ children, isDisabled }) => (
    <div className={twJoin('', isDisabled && 'pointer-events-none opacity-30')}>{children}</div>
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

export const SelectedParametersListItem: FC = ({ children }) => (
    <li className="rounded bg-creamWhite p-2 text-sm text-dark">{children}</li>
);
