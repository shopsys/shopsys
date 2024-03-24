import { RemoveBoldIcon } from 'components/Basic/Icon/RemoveBoldIcon';
import { TIDs } from 'cypress/tids';
import { twMergeCustom } from 'helpers/twMerge';
import useTranslation from 'next-translate/useTranslation';
import { MouseEventHandler } from 'react';

type RemoveCartItemButtonProps = {
    onItemRemove: MouseEventHandler<HTMLButtonElement>;
};

export const RemoveCartItemButton: FC<RemoveCartItemButtonProps> = ({ onItemRemove, className }) => {
    const { t } = useTranslation();

    return (
        <button
            tid={TIDs.pages_cart_removecartitembutton}
            title={t('Remove from cart')}
            className={twMergeCustom(
                'flex h-7 w-7 cursor-pointer items-center justify-center rounded-full border-none bg-whitesmoke outline-none transition hover:bg-blueLight',
                className,
            )}
            onClick={onItemRemove}
        >
            <RemoveBoldIcon className="mx-auto w-2" />
        </button>
    );
};
