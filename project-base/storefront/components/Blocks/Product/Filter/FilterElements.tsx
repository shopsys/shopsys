import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { LabelLink } from 'components/Basic/LabelLink/LabelLink';
import { twJoin } from 'tailwind-merge';

export const FilterGroupWrapper: FC = ({ children }) => <div className="">{children}</div>;

export const FilterGroupTitle: FC<{ isOpen: boolean; title: string; onClick: () => void }> = ({
    isOpen,
    title,
    onClick,
}) => (
    <div
        className="flex cursor-pointer items-center justify-between py-6 font-bold uppercase text-text"
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
        className={twJoin(
            'w-fit cursor-pointer border-none p-0 text-sm underline outline-none hover:no-underline bg-none hover:bg-none',
            'text-link',
            'hover:text-linkHovered',
        )}
        onClick={onClick}
    >
        {children}
    </button>
);

export const SelectedParametersName: FC = ({ children }) => <p className="py-1 text-sm">{children}</p>;

export const SelectedParametersList: FC = ({ children }) => <ul className="flex flex-wrap gap-2">{children}</ul>;

export const SelectedParametersListItem: FC<{ onClick: () => void }> = ({ children, onClick }) => (
    <LabelLink render={(labelLink) => <li className="text-sm">{labelLink}</li>} onClick={onClick}>
        {children}
    </LabelLink>
);
