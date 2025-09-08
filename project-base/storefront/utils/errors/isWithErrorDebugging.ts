import { getPublicConfigProperty } from 'utils/config/getNextConfig';

const errorDebuggingLevel = getPublicConfigProperty('errorDebuggingLevel', '');

const isWithConsoleErrorDebugging = errorDebuggingLevel === 'console';

export const isWithToastAndConsoleErrorDebugging = errorDebuggingLevel === 'toast-and-console';

export const isWithErrorDebugging = isWithConsoleErrorDebugging || isWithToastAndConsoleErrorDebugging;
