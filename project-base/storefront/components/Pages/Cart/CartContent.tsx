import { CartList } from './CartList/CartList';
import { CartLoading } from './CartLoading';
import { CartSummary } from './CartSummary';
import { OrderAction } from 'components/Blocks/OrderAction/OrderAction';
import { OrderSteps } from 'components/Blocks/OrderSteps/OrderSteps';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import { TypeRecommendationType } from 'graphql/types';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

const RecommendedProducts = dynamic(() =>
    import('components/Blocks/Product/RecommendedProducts').then((component) => component.RecommendedProducts),
);

export const CartContent: FC = () => {
    const { t } = useTranslation();
    const { url, isLuigisBoxActive } = useDomainConfig();
    const [transportAndPaymentUrl] = getInternationalizedStaticUrls(['/order/transport-and-payment'], url);
    const { cart, isFetching } = useCurrentCart();

    if (cart === undefined || isFetching) {
        return <CartLoading />;
    }

    if (!cart?.items.length) {
        return (
            <p className="my-28 text-center text-2xl" tid={TIDs.cart_page_empty_cart_text}>
                {t('Your cart is currently empty.')}
            </p>
        );
    }

    return (
        <>
            <OrderSteps activeStep={1} domainUrl={url} />

            <CartList items={cart.items} />

            <CartSummary />

            <OrderAction
                withGapBottom
                buttonBack={t('Back')}
                buttonBackLink="/"
                buttonNext={t('Transport and payment')}
                buttonNextLink={transportAndPaymentUrl}
                hasDisabledLook={false}
                withGapTop={false}
            />

            {isLuigisBoxActive && (
                <RecommendedProducts
                    itemUuids={cart.items.map((item) => item.uuid)}
                    recommendationType={TypeRecommendationType.Basket}
                    render={(recommendedProductsContent) => (
                        <div className="mb-6 px-0">
                            <div className="h2 mb-3">{t('Recommended for you')}</div> {recommendedProductsContent}
                        </div>
                    )}
                />
            )}
        </>
    );
};
