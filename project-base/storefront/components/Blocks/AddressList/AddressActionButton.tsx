import { ReactNode } from 'react';

type AddressActionButtonProps = {
    ariaLabel: string | undefined;
    children: ReactNode;
    onClick: (e: React.MouseEvent<HTMLButtonElement>) => void;
    onKeyDown?: (e: React.KeyboardEvent<HTMLButtonElement>) => void;
};

export const AddressActionButton: FC<AddressActionButtonProps> = ({ ariaLabel, children, onClick, onKeyDown }) => {
    return (
        <button
            aria-haspopup="dialog"
            aria-label={ariaLabel}
            className="flex cursor-pointer items-center gap-2 self-start rounded-sm pr-1 font-secondary font-semibold text-link-default text-sm underline hover:text-link-hovered hover:no-underline"
            tabIndex={0}
            type="button"
            onClick={onClick}
            onKeyDown={onKeyDown}
        >
            {children}
        </button>
    );
};
