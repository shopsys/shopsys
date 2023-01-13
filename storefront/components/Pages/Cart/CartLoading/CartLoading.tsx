import { CartLoadingWrapperStyled } from './EmptyCart.style';
import { Loader } from 'components/Basic/Loader/Loader';
import { Webline } from 'components/Layout/Webline/Webline';
import { FC } from 'react';

const TEST_IDENTIFIER = 'blocks-cartloading';

export const CartLoading: FC = () => {
    return (
        <Webline style={{ minHeight: '75rem' }}>
            <CartLoadingWrapperStyled data-testid={TEST_IDENTIFIER}>
                <Loader iconSize={50} />
            </CartLoadingWrapperStyled>
        </Webline>
    );
};
