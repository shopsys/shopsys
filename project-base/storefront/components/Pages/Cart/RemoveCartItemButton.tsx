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
            tabIndex={0}
            tid={TIDs.pages_cart_removecartitembutton}
            title={t('Remove from cart')}
            onClick={onRemoveFromCart}
        >
            <RemoveIcon className="text-icon-less hover:text-icon-default size-6" />
        </button>
    );
};
