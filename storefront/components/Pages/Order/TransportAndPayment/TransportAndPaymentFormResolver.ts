import * as Yup from 'yup';
import { Resolver } from 'react-hook-form';
import { yupResolver } from '@hookform/resolvers/yup';

export const getTransportAndPaymentFormResolver = (isPersonalPickup: boolean): Resolver => {
    const schema: { [key: string]: Yup.StringSchema } = {
        transport: Yup.string().required(),
        payment: Yup.string().required(),
    };

    if (isPersonalPickup) {
        schema.personalPickupStore = Yup.string().required();
    }

    return yupResolver(Yup.object().shape(schema));
};
