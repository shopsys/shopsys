import { getStringFromUrlQuery } from './getStringFromUrlQuery';

const CODE_PATTERN = /^[a-z0-9_-]+$/;

export const getCodeFromUrlQuery = (codeQuery: string | string[] | undefined): string | null => {
    const code = getStringFromUrlQuery(codeQuery);

    if (!CODE_PATTERN.test(code)) {
        return null;
    }

    return code;
};
