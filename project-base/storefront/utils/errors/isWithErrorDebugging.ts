import getConfig from 'next/config';

const { publicRuntimeConfig } = getConfig();

const isWithConsoleErrorDebugging = publicRuntimeConfig.errorDebuggingLevel === 'console';

export const isWithToastAndConsoleErrorDebugging = publicRuntimeConfig.errorDebuggingLevel === 'toast-and-console';

export const isWithErrorDebugging = isWithConsoleErrorDebugging || isWithToastAndConsoleErrorDebugging;
