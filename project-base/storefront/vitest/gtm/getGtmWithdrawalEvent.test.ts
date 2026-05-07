import { GtmEventType } from 'gtm/enums/GtmEventType';
import { getGtmWithdrawalEvent } from 'gtm/factories/getGtmWithdrawalEvent';
import { describe, expect, test } from 'vitest';

describe('getGtmWithdrawalEvent', () => {
    test('should create withdrawal event with order number as ecommerce id', () => {
        expect(getGtmWithdrawalEvent('T12345')).toStrictEqual({
            event: GtmEventType.withdrawal,
            ecommerce: {
                id: 'T12345',
            },
            _clear: true,
        });
    });
});
