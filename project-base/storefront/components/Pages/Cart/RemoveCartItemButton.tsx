import { RemoveIcon } from 'components/Basic/Icon/RemoveIcon';
import { TIDs } from 'cypress/tids';
import useTranslation from 'next-translate/useTranslation';
import { MouseEventHandler } from 'react';

type RemoveCartItemButtonProps = {
    onRemoveFromCart: MouseEventHandler<HTMLButtonElement>;
};

export const RemoveCartItemButton: FC<RemoveCartItemButtonProps> = ({ onRemoveFromCart, className }) => {
    const { t } = useTranslation();

    return (
        <button
            className={className}
            data-tid={TIDs.pages_cart_removecartitembutton}
            tabIndex={0}
            title={t('Remove from cart')}
            onClick={onRemoveFromCart}
        >
            <RemoveIcon className="size-6" />
        </button>
    );
};
