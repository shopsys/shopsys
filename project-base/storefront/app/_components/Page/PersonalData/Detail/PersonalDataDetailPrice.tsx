'use client';

import { useFormatPrice } from 'utils/formatting/useFormatPrice';

type PersonalDataDetailPriceProps = {
    price: number;
};
export const PersonalDataDetailPrice: FC<PersonalDataDetailPriceProps> = ({ price }) => {
    const formatPrice = useFormatPrice();

    return <>{formatPrice(price)}</>;
};
