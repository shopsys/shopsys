import React from 'react';
import { theme } from '../../theme/main';
import { ThemeProvider } from 'styled-components';

/**
 * This global provider is used primary for styleguidist as wrapper.
 */

function SsfwGlobalProvider({ children }) {
    return <ThemeProvider theme={theme}>{children}</ThemeProvider>;
}

/* @component */
export default SsfwGlobalProvider;
