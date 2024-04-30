import { CartList } from './CartList/CartList';
import { CartSummary } from './CartSummary';
import { OrderAction } from 'components/Blocks/OrderAction/OrderAction';
import { OrderSteps } from 'components/Blocks/OrderSteps/OrderSteps';
import { RecommendedProducts } from 'components/Blocks/Product/RecommendedProducts';
import { SkeletonPageCart } from 'components/Blocks/Skeleton/SkeletonPageCart';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import { RecommendationTypeApi } from 'graphql/generated';
import { getInternationalizedStaticUrls } from 'helpers/getInternationalizedStaticUrls';
import { useCurrentCart } from 'hooks/cart/useCurrentCart';
import useTranslation from 'next-translate/useTranslation';

export const CartContent: FC = () => {
    const { t } = useTranslation();
    const { url, isLuigisBoxActive } = useDomainConfig();
    const [transportAndPaymentUrl] = getInternationalizedStaticUrls(['/order/transport-and-payment'], url);
    const { cart, isFetching, isCartHydrated } = useCurrentCart();

    if (!isCartHydrated || isFetching) {
        return <SkeletonPageCart />;
    }

    if (!cart || !cart.items.length) {
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
                    recommendationType={RecommendationTypeApi.BasketApi}
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
