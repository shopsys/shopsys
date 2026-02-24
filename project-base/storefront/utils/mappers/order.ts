import { TypeOrderItemTypeEnum } from 'graphql/types';

const getOrderItemByType = <T extends { type: TypeOrderItemTypeEnum }>(
    items: T[] | null | undefined,
    type: TypeOrderItemTypeEnum,
): T | undefined => items?.find((item) => item.type === type);

export const getOrderTransportItem = <T extends { type: TypeOrderItemTypeEnum }>(items: T[] | null | undefined) =>
    getOrderItemByType(items, TypeOrderItemTypeEnum.Transport);

export const getOrderPaymentItem = <T extends { type: TypeOrderItemTypeEnum }>(items: T[] | null | undefined) =>
    getOrderItemByType(items, TypeOrderItemTypeEnum.Payment);

export const getOrderRoundingItem = <T extends { type: TypeOrderItemTypeEnum }>(items: T[] | null | undefined) =>
    getOrderItemByType(items, TypeOrderItemTypeEnum.Rounding);
