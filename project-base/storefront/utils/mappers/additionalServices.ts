import { AdditionalServiceSummaryLine } from 'components/Blocks/Product/AdditionalServices/AdditionalServiceSummaryList';
import { TypeAdditionalServiceFragment } from 'graphql/requests/additionalServices/fragments/AdditionalServiceFragment.generated';
import { TypeOrderDetailItemFragment } from 'graphql/requests/orders/fragments/OrderDetailItemFragment.generated';
import { TypeOrderItemTypeEnum } from 'graphql/types';
import { isPriceVisible, mapPriceForCalculations } from 'utils/mappers/price';

type OrderItemRelatedItem = Omit<TypeOrderDetailItemFragment['relatedItems'][number], 'deliveryDaysExtension'> & {
    deliveryDaysExtension?: number | null;
};

export const mapCartItemAdditionalServiceSummaryLines = (
    additionalServices: TypeAdditionalServiceFragment[],
    quantity: number,
    unitName: string,
    formatPrice: (price: string | number) => string,
): AdditionalServiceSummaryLine[] =>
    additionalServices.map((additionalService) => ({
        uuid: additionalService.uuid,
        name: additionalService.name,
        deliveryDaysExtension: additionalService.deliveryDaysExtension,
        imageUrl: additionalService.mainImage?.url ?? null,
        quantity,
        unitName,
        unitPriceLabel: isPriceVisible(additionalService.price.priceWithVat)
            ? formatPrice(additionalService.price.priceWithVat)
            : undefined,
        priceLabel: isPriceVisible(additionalService.price.priceWithVat)
            ? formatPrice(mapPriceForCalculations(additionalService.price.priceWithVat) * quantity)
            : null,
    }));

type MapOrderItemAdditionalServiceSummaryLinesOptions = {
    includeItemDetails?: boolean;
};

export const mapOrderItemAdditionalServiceSummaryLines = (
    relatedItems: readonly OrderItemRelatedItem[],
    formatPrice: (price: string | number) => string,
    options: MapOrderItemAdditionalServiceSummaryLinesOptions = {},
): AdditionalServiceSummaryLine[] =>
    relatedItems
        .filter((relatedItem) => relatedItem.type === TypeOrderItemTypeEnum.AdditionalService)
        .map((serviceItem) => ({
            uuid: serviceItem.uuid,
            name: serviceItem.name,
            code: options.includeItemDetails ? serviceItem.catnum : undefined,
            deliveryDaysExtension: serviceItem.deliveryDaysExtension,
            imageUrl: serviceItem.mainImage?.url ?? null,
            quantity: serviceItem.quantity,
            unitName: serviceItem.unit,
            unitPriceLabel: isPriceVisible(serviceItem.unitPrice.priceWithVat)
                ? formatPrice(serviceItem.unitPrice.priceWithVat)
                : undefined,
            priceLabel: isPriceVisible(serviceItem.totalPrice.priceWithVat)
                ? formatPrice(serviceItem.totalPrice.priceWithVat)
                : null,
        }));
