import { CartSteps } from 'components/Blocks/CartSteps/CartSteps';
import { DeferredRecommendedProducts } from 'components/Blocks/Product/DeferredRecommendedProducts';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeCartFragment } from 'graphql/requests/cart/fragments/CartFragment.generated';
import { TypeRecommendationType } from 'graphql/types';
import { type RefObject } from 'react';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { CartList } from './CartList/CartList';
import { CartSummary } from './CartSummary';

type CartContentProps = {
    cart: TypeCartFragment;
    cartPreviewRef: RefObject<HTMLDivElement | null>;
};

export const CartContent: FC<CartContentProps> = ({ cart, cartPreviewRef }) => {
    const { t } = useTranslation();
    const { url, isLuigisBoxActive } = useDomainConfig();

    return (
        <VerticalStack gap="md">
            <h1 className="sr-only">{t('Cart')}</h1>

            <Webline width="xl">
                <CartSteps activeStep={1} domainUrl={url} />

                <CartList items={cart.items} />

                <CartSummary cartPreviewRef={cartPreviewRef} />
            </Webline>

            {isLuigisBoxActive && (
                <DeferredRecommendedProducts
                    itemUuids={cart.items.map((item) => item.uuid)}
                    recommendationType={TypeRecommendationType.Basket}
                    render={(recommendedProductsContent) => (
                        <Webline>
                            <p className="h3 mb-3">{t('Recommended for you')}</p>
                            {recommendedProductsContent}
                        </Webline>
                    )}
                />
            )}
        </VerticalStack>
    );
};
