import { RemoveCartItemButtonStyled } from './RemoveCartItemButton.style';
import { Icon } from 'components/Basic/Icon/Icon';
import { FC, MouseEventHandler } from 'react';

type RemoveCartItemButtonProps = {
    onItemRemove: MouseEventHandler<HTMLButtonElement>;
};

const TEST_IDENTIFIER = 'pages-cart-removecartitembutton';

export const RemoveCartItemButton: FC<RemoveCartItemButtonProps> = ({ onItemRemove }) => (
    <RemoveCartItemButtonStyled onClick={onItemRemove} data-testid={TEST_IDENTIFIER}>
        <Icon iconType="icon" icon="RemoveBold" />
    </RemoveCartItemButtonStyled>
);
