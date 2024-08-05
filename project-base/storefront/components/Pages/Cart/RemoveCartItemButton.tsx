import { RemoveIcon } from 'components/Basic/Icon/RemoveIcon';
import { TIDs } from 'cypress/tids';
import useTranslation from 'next-translate/useTranslation';
import { MouseEventHandler } from 'react';
import { twMergeCustom } from 'utils/twMerge';

type RemoveCartItemButtonProps = {
    onRemoveFromCart: MouseEventHandler<HTMLButtonElement>;
};

export const RemoveCartItemButton: FC<RemoveCartItemButtonProps> = ({ onRemoveFromCart, className }) => {
    const { t } = useTranslation();

    return (
        <button
            tid={TIDs.pages_cart_removecartitembutton}
            title={t('Remove from cart')}
            className={twMergeCustom(
                'flex h-7 w-7 cursor-pointer items-center justify-center rounded-full border-none outline-none transition',
                'text-actionInvertedText',
                'hover:text-actionInvertedTextActive',
                className,
            )}
            onClick={onRemoveFromCart}
        >
            <RemoveIcon className="mx-auto w-4" />
        </button>
    );
};
