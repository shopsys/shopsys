import { Icon } from 'components/Basic/Icon/Icon';
import { MouseEventHandler } from 'react';

type RemoveCartItemButtonProps = {
    onItemRemove: MouseEventHandler<HTMLButtonElement>;
};

const TEST_IDENTIFIER = 'pages-cart-removecartitembutton';

export const RemoveCartItemButton: FC<RemoveCartItemButtonProps> = ({ onItemRemove }) => (
    <button
        className="flex h-5 w-5 cursor-pointer items-center justify-center rounded-full border-none bg-whitesmoke p-0 outline-none transition hover:bg-blueLight"
        onClick={onItemRemove}
        data-testid={TEST_IDENTIFIER}
    >
        <Icon iconType="icon" icon="RemoveBold" className="mx-auto w-2 basis-2" />
    </button>
);
