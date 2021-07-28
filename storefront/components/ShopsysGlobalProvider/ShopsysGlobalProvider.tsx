import { FC } from 'react';
import GlobalStyle from '../../theme/GlobalStyle';
import { theme } from '../../theme/main';
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
