import { TrashCanIcon } from 'components/Basic/Icon/TrashCanIcon';
import { IconButton } from 'components/Forms/Button/IconButton';
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
        <IconButton
            Icon={TrashCanIcon}
            ariaLabel={ariaLabel}
            className={className}
            disabled={disabled}
            shape="rounded"
            size="small"
            tid={TIDs.pages_cart_removecartitembutton}
            title={title}
            tooltipLabel={title}
            tooltipPlacement="left"
            variant="ghost"
            onClick={onRemoveFromCart}
        />
    );
};
