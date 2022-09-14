import { EmptyCartStyled } from './EmptyCart.style';
import { Webline } from 'components/Layout/Webline/Webline';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { FC } from 'react';

const TEST_IDENTIFIER = 'blocks-emptycart';

export const EmptyCart: FC = () => {
    const t = useTypedTranslationFunction();

    return (
        <Webline>
            <EmptyCartStyled data-testid={TEST_IDENTIFIER}>{t('Your cart is currently empty.')}</EmptyCartStyled>
        </Webline>
    );
};
