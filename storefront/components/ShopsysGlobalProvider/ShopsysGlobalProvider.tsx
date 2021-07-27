import { DefaultTheme, ThemeProvider } from 'styled-components';
import { FC } from 'react';
import { theme } from '../../theme/main';

/**
 * This global provider is used primary for styleguidist as wrapper.
 */
const ShopsysGlobalProvider: FC<{ theme: DefaultTheme }> = ({ children }) => {
    return <ThemeProvider theme={theme}>{children}</ThemeProvider>;
};

/* @component */
export default ShopsysGlobalProvider;
