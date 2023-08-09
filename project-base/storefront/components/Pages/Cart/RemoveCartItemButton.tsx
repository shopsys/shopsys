import { Icon } from 'components/Basic/Icon/Icon';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { MouseEventHandler } from 'react';

type RemoveCartItemButtonProps = {
    onItemRemove: MouseEventHandler<HTMLButtonElement>;
};

const TEST_IDENTIFIER = 'pages-cart-removecartitembutton';

export const RemoveCartItemButton: FC<RemoveCartItemButtonProps> = ({ onItemRemove }) => {
    const t = useTypedTranslationFunction();

    return (
        <button
            className="flex h-5 w-5 cursor-pointer items-center justify-center rounded-full border-none bg-whitesmoke p-0 outline-none transition hover:bg-blueLight"
            onClick={onItemRemove}
            data-testid={TEST_IDENTIFIER}
            title={t('Remove from cart')}
        >
            <Icon iconType="icon" icon="RemoveBold" className="mx-auto w-2 basis-2" />
        </button>
    );
};
