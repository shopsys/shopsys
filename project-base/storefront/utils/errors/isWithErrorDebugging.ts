// const { publicRuntimeConfig } = getConfig();

// const isWithConsoleErrorDebugging = publicRuntimeConfig.errorDebuggingLevel === 'console';
const isWithConsoleErrorDebugging = process.env.errorDebuggingLevel === 'console';

// export const isWithToastAndConsoleErrorDebugging = publicRuntimeConfig.errorDebuggingLevel === 'toast-and-console';
export const isWithToastAndConsoleErrorDebugging = process.env.errorDebuggingLevel === 'toast-and-console';

export const isWithErrorDebugging = isWithConsoleErrorDebugging || isWithToastAndConsoleErrorDebugging;
