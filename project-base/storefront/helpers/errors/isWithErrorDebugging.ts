import getConfig from 'next/config';

const { publicRuntimeConfig } = getConfig();

export const isWithErrorDebugging = publicRuntimeConfig.errorDebugging;
