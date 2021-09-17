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
    const initCart = getCart('1007c9a3-f570-484a-b84e-4a4f49bb35c0');
    const dispatch = useShopsysDispatch();
    useEffect(() => {
        dispatch(userActions.setCart(initCart));
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
