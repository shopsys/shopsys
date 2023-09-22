import { RemoveBoldIcon } from 'components/Basic/Icon/IconsSvg';
import { twMergeCustom } from 'helpers/twMerge';
import useTranslation from 'next-translate/useTranslation';
import { MouseEventHandler } from 'react';

type RemoveCartItemButtonProps = {
    onItemRemove: MouseEventHandler<HTMLButtonElement>;
};

const TEST_IDENTIFIER = 'pages-cart-removecartitembutton';

export const RemoveCartItemButton: FC<RemoveCartItemButtonProps> = ({ onItemRemove, className }) => {
    const { t } = useTranslation();

    return (
        <button
            className={twMergeCustom(
                'flex h-7 w-7 cursor-pointer items-center justify-center rounded-full border-none bg-whitesmoke outline-none transition hover:bg-blueLight',
                className,
            )}
            onClick={onItemRemove}
            data-testid={TEST_IDENTIFIER}
            title={t('Remove from cart')}
        >
            <RemoveBoldIcon className="mx-auto w-2" />
        </button>
    );
};
