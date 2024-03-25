import { OrderSummaryContent } from './OrderSummaryElements';
import { PriceFragment } from 'graphql/requests/prices/fragments/PriceFragment.generated';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import useTranslation from 'next-translate/useTranslation';

type TotalPriceProps = {
    totalPrice: PriceFragment;
};

export const TotalPrice: FC<TotalPriceProps> = ({ totalPrice }) => {
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();

    return (
        <div className="mb-5">
            <OrderSummaryContent>
                <div className="flex justify-end">
                    <span className="mr-4 inline-flex items-end">{t('Total price')}</span>
                    <strong className="text-2xl text-primary">{formatPrice(totalPrice.priceWithVat)}</strong>
                </div>
            </OrderSummaryContent>
        </div>
    );
};
