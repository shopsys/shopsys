import { TypeMainVariantDetailFragment } from 'graphql/requests/products/fragments/MainVariantDetailFragment.generated';
import { TypeProductDetailFragment } from 'graphql/requests/products/fragments/ProductDetailFragment.generated';
import { TypeProductTypeEnum } from 'graphql/types';
import { isProductSellable } from './isProductSellable';

/**
 * An electronic gift voucher is sent by email after the payment, so there are no delivery options to choose from
 */
export const showProductDeliveryOptionsPopup = (
    product: TypeProductDetailFragment | TypeMainVariantDetailFragment['variants'][number],
): boolean => isProductSellable(product) && product.productType !== TypeProductTypeEnum.ElectronicGiftVoucher;
