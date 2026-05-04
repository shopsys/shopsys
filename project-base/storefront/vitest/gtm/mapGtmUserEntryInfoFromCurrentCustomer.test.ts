import { TypeLoginTypeEnum } from 'graphql/types';
import { mapGtmUserEntryInfoFromCurrentCustomer } from 'gtm/mappers/mapGtmUserEntryInfoFromCurrentCustomer';
import { CurrentCustomerType } from 'types/customer';
import { describe, expect, test } from 'vitest';

describe('mapGtmUserEntryInfoFromCurrentCustomer', () => {
    test('should map company fields for user entry GTM events', () => {
        const currentCustomer = {
            uuid: '9b2fafe7-f169-4448-92d0-abf649b8fb97',
            email: 'lukas.fibiger@shopsys.com',
            firstName: 'Jan',
            lastName: 'Vopicka',
            companyName: 'Zakoupil a Zbozil s.r.o.',
            companyNumber: '123456',
            companyTaxNumber: 'CZ123456',
            loginInfo: {
                loginType: TypeLoginTypeEnum.Web,
                externalId: null,
            },
        } as CurrentCustomerType;

        const result = mapGtmUserEntryInfoFromCurrentCustomer(currentCustomer);

        expect(result).toEqual({
            id: '9b2fafe7-f169-4448-92d0-abf649b8fb97',
            email: 'lukas.fibiger@shopsys.com',
            firstName: 'Jan',
            lastName: 'Vopicka',
            loginType: TypeLoginTypeEnum.Web,
            externalId: null,
            companyName: 'Zakoupil a Zbozil s.r.o.',
            companyNumber: '123456',
            companyVatNumber: 'CZ123456',
        });
    });
});
