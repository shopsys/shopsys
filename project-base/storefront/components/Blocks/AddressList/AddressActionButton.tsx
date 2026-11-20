import { getLinkClassName } from 'components/Basic/Link/linkUtils';
import { ReactNode } from 'react';
import { twJoin } from 'tailwind-merge';

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
            className={twJoin(getLinkClassName({ placement: 'standalone' }), 'flex self-start pr-1')}
            tabIndex={0}
            type="button"
            onClick={onClick}
            onKeyDown={onKeyDown}
        >
            {children}
        </button>
    );
};
