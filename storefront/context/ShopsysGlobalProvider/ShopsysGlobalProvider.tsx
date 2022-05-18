import { ToastsStyle } from 'components/Helpers/Toasts/Toasts.style';
import FontFaceStyle from 'components/Theme/FontFaceStyle';
import GlobalStyle from 'components/Theme/GlobalStyle';
import { theme } from 'components/Theme/main';
import { FC } from 'react';
import { ThemeProvider } from 'styled-components';

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
