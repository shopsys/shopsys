import { CartLoadingWrapperStyled } from './EmptyCart.style';
import { Loader } from 'components/Basic/Loader/Loader';
import { Webline } from 'components/Layout/Webline/Webline';
import { FC } from 'react';

const TEST_IDENTIFIER = 'blocks-cartloading';

export const CartLoading: FC = () => {
    return (
        <Webline>
            <CartLoadingWrapperStyled data-testid={TEST_IDENTIFIER}>
                <Loader />
            </CartLoadingWrapperStyled>
        </Webline>
    );
};
