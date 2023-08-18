import { OrderSummaryContent } from './OrderSummaryElements';
import { PriceFragmentApi } from 'graphql/generated';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import useTranslation from 'next-translate/useTranslation';

type TotalPriceProps = {
    totalPrice: PriceFragmentApi;
};

const TEST_IDENTIFIER = 'blocks-ordersummary-totalprice';

export const TotalPrice: FC<TotalPriceProps> = ({ totalPrice }) => {
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();

    return (
        <div className="mb-5" data-testid={TEST_IDENTIFIER}>
            <OrderSummaryContent>
                <div className="flex justify-end">
                    <span className="mr-4 inline-flex items-end">{t('Total price')}</span>
                    <strong className="text-2xl text-primary" data-testid={TEST_IDENTIFIER + '-amount'}>
                        {formatPrice(totalPrice.priceWithVat)}
                    </strong>
                </div>
            </OrderSummaryContent>
        </div>
    );
};
