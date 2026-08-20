import { TypeLoginTypeEnum } from 'graphql/types';
import { getAllowedSocialNetworkType } from 'utils/auth/getAllowedSocialNetworkType';
import { describe, expect, test } from 'vitest';

describe('getAllowedSocialNetworkType', () => {
    test.each([
        TypeLoginTypeEnum.Facebook,
        TypeLoginTypeEnum.Google,
        TypeLoginTypeEnum.Seznam,
    ])('accepts supported social login type %s', (socialNetworkType) => {
        expect(getAllowedSocialNetworkType(socialNetworkType)).toBe(socialNetworkType);
    });

    test.each([
        TypeLoginTypeEnum.Admin,
        TypeLoginTypeEnum.Web,
        '<img src=x onerror=alert(1)>',
        undefined,
    ])('rejects unsupported social login value %s', (socialNetworkType) => {
        expect(getAllowedSocialNetworkType(socialNetworkType)).toBeUndefined();
    });
});
