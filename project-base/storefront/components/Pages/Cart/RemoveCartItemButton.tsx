import { RemoveIcon } from 'components/Basic/Icon/RemoveIcon';
import { TIDs } from 'cypress/tids';
import { MouseEventHandler } from 'react';

type RemoveCartItemButtonProps = {
    title: string;
    ariaLabel: string;
    onRemoveFromCart: MouseEventHandler<HTMLButtonElement>;
    disabled?: boolean;
};

export const RemoveCartItemButton: FC<RemoveCartItemButtonProps> = ({
    onRemoveFromCart,
    className,
    title,
    ariaLabel,
    disabled,
}) => {
    return (
        <button
            aria-label={ariaLabel}
            className={className}
            data-tid={TIDs.pages_cart_removecartitembutton}
            disabled={disabled}
            tabIndex={0}
            title={title}
            onClick={onRemoveFromCart}
        >
            <RemoveIcon className="size-6" />
        </button>
    );
};
