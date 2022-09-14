import { CartLoadingWrapperStyled } from './EmptyCart.style';
import { Icon } from 'components/Basic/Icon/Icon';
import { Webline } from 'components/Layout/Webline/Webline';
import { FC } from 'react';

const TEST_IDENTIFIER = 'blocks-cartloading';

export const CartLoading: FC = () => {
    return (
        <Webline>
            <CartLoadingWrapperStyled data-testid={TEST_IDENTIFIER}>
                <Icon icon="Spinner" iconType="icon" />
            </CartLoadingWrapperStyled>
        </Webline>
    );
};
