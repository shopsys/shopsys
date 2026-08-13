import { TypeTransportTypeEnum } from 'graphql/types';

export const isPacketeryTransport = (transportTypeCode: TypeTransportTypeEnum | undefined) =>
    transportTypeCode === TypeTransportTypeEnum.Packetery;

export const isPersonalPickupTransport = (transportTypeCode: TypeTransportTypeEnum | undefined) =>
    transportTypeCode === TypeTransportTypeEnum.PersonalPickup;

export const isPickupPlaceTransport = (transportTypeCode: TypeTransportTypeEnum | undefined) =>
    isPersonalPickupTransport(transportTypeCode) || isPacketeryTransport(transportTypeCode);
