import { RemoveBoldIcon } from 'components/Basic/Icon/IconsSvg';
import useTranslation from 'next-translate/useTranslation';
import { MouseEventHandler } from 'react';

type RemoveCartItemButtonProps = {
    onItemRemove: MouseEventHandler<HTMLButtonElement>;
};

const TEST_IDENTIFIER = 'pages-cart-removecartitembutton';

export const RemoveCartItemButton: FC<RemoveCartItemButtonProps> = ({ onItemRemove }) => {
    const { t } = useTranslation();

    return (
        <button
            className="flex h-5 w-5 cursor-pointer items-center justify-center rounded-full border-none bg-whitesmoke p-0 outline-none transition hover:bg-blueLight"
            onClick={onItemRemove}
            data-testid={TEST_IDENTIFIER}
            title={t('Remove from cart')}
        >
            <RemoveBoldIcon className="mx-auto w-2 basis-2" />
        </button>
    );
};