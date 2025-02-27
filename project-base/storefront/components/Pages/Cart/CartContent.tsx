import { CartList } from './CartList/CartList';
import { CartSummary } from './CartSummary';
import { CartSteps } from 'components/Blocks/CartSteps/CartSteps';
import { DeferredRecommendedProducts } from 'components/Blocks/Product/DeferredRecommendedProducts';
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
        <Webline>
            <CartSteps activeStep={1} domainUrl={url} />

            <CartList items={cart.items} />

            <CartSummary />

            {isLuigisBoxActive && (
                <DeferredRecommendedProducts
                    itemUuids={cart.items.map((item) => item.uuid)}
                    recommendationType={TypeRecommendationType.Basket}
                    render={(recommendedProductsContent) => (
                        <section>
                            <h3 className="mb-3">{t('Recommended for you')}</h3>
                            {recommendedProductsContent}
                        </section>
                    )}
                />
            )}
        </Webline>
    );
};
