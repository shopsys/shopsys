import { CartList } from './CartList/CartList';
import { CartSummary } from './CartSummary';
import { CartSteps } from 'components/Blocks/CartSteps/CartSteps';
import { DeferredRecommendedProducts } from 'components/Blocks/Product/DeferredRecommendedProducts';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeCartFragment } from 'graphql/requests/cart/fragments/CartFragment.generated';
import { TypeRecommendationType } from 'graphql/types';
import useTranslation from 'next-translate/useTranslation';

type CartContentProps = {
    cart: TypeCartFragment;
};

export const CartContent: FC<CartContentProps> = ({ cart }) => {
    const { t } = useTranslation();
    const { url, isLuigisBoxActive } = useDomainConfig();

    return (
        <VerticalStack gap="md">
            <h1 className="sr-only">{t('Cart')}</h1>

            <Webline>
                <CartSteps activeStep={1} domainUrl={url} />

                <CartList items={cart.items} />

                <CartSummary />
            </Webline>

            {isLuigisBoxActive && (
                <DeferredRecommendedProducts
                    itemUuids={cart.items.map((item) => item.uuid)}
                    recommendationType={TypeRecommendationType.Basket}
                    render={(recommendedProductsContent) => (
                        <section>
                            <p className="h3 mb-3">{t('Recommended for you')}</p>
                            {recommendedProductsContent}
                        </section>
                    )}
                />
            )}
        </VerticalStack>
    );
};
