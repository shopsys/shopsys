import { FC, useEffect } from 'react';
import { getCart } from 'connectors/cart/Cart';
import GlobalStyle from 'components/Theme/GlobalStyle';
import { theme } from 'components/Theme/main';
import { ThemeProvider } from 'styled-components';
import { ToastsStyle } from 'components/Helpers/Toasts/Toasts.style';
import { userActions } from 'redux/store/UserStore';
import { useShopsysDispatch } from 'redux/store';

/**
 * This global provider is used primary for styleguidist as wrapper.
 */
const ShopsysGlobalProvider: FC = ({ children }) => {
    const initCart = getCart();
    const dispatch = useShopsysDispatch();
    useEffect(() => {
        if (initCart !== undefined) {
            dispatch(userActions.setCart(initCart));
        }
    }, [initCart]);

    return (
        <ThemeProvider theme={theme}>
            <GlobalStyle />
            <ToastsStyle />
            {children}
        </ThemeProvider>
    );
};

/* @component */
export default ShopsysGlobalProvider;
