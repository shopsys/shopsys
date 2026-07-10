import { TypeProductTypeEnum } from 'graphql/types';
import { GtmProductType } from 'gtm/types/objects';

export const mapGtmProductType = (productType: TypeProductTypeEnum): GtmProductType =>
    productType === TypeProductTypeEnum.ElectronicGiftVoucher || productType === TypeProductTypeEnum.PrintedGiftVoucher
        ? 'voucher'
        : 'product';
