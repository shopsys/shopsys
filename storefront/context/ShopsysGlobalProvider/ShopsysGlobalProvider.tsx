import { FC } from 'react';
import GlobalStyle from '../../components/Theme/GlobalStyle';
import { theme } from '../../components/Theme/main';
import { ThemeProvider } from 'styled-components';

/**
 * This global provider is used primary for styleguidist as wrapper.
 */
const ShopsysGlobalProvider: FC = ({ children }) => {
    return (
        <ThemeProvider theme={theme}>
            <GlobalStyle />
            {children}
        </ThemeProvider>
    );
};

/* @component */
export default ShopsysGlobalProvider;
