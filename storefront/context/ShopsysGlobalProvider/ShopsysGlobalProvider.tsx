import { FC } from 'react';
import FontFaceStyle from 'components/Theme/FontFaceStyle';
import GlobalStyle from 'components/Theme/GlobalStyle';
import { theme } from 'components/Theme/main';
import { ThemeProvider } from 'styled-components';
import { ToastsStyle } from 'components/Helpers/Toasts/Toasts.style';

/**
 * This global provider is used primary for styleguidist as wrapper.
 */
const ShopsysGlobalProvider: FC = ({ children }) => {
    return (
        <ThemeProvider theme={theme}>
            <FontFaceStyle />
            <GlobalStyle />
            <ToastsStyle />
            {children}
        </ThemeProvider>
    );
};

/* @component */
export default ShopsysGlobalProvider;
