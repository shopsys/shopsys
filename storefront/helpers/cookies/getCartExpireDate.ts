import getConfig from 'next/config';

export const getCartExpireDate = (): Date => {
    const { publicRuntimeConfig } = getConfig();
    const expireDate = new Date();
    expireDate.setDate(expireDate.getDate() + Number(publicRuntimeConfig.reduxExpirationDays));

    return expireDate;
};
