import { OrderSummaryContent } from './OrderSummaryElements';
import { TypePriceFragment } from 'graphql/requests/prices/fragments/PriceFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import { isPriceVisible } from 'utils/mappers/price';

type TotalPriceProps = {
    totalPrice: TypePriceFragment;
};

export const TotalPrice: FC<TotalPriceProps> = ({ totalPrice }) => {
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();

    if (!isPriceVisible(totalPrice.priceWithVat)) {
        return null;
    }

    return (
        <div className="mb-5">
            <OrderSummaryContent>
                <div className="flex justify-end">
                    <span className="mr-4 inline-flex items-end">{t('Total price')}</span>
                    <strong className="text-2xl text-price">{formatPrice(totalPrice.priceWithVat)}</strong>
                </div>
            </OrderSummaryContent>
        </div>
    );
};
