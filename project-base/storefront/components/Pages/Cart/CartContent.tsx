import { CartList } from './CartList/CartList';
import { CartSummary } from './CartSummary';
import { Convertim } from './Convertim';
import { useCartPageNavigation } from './cartUtils';
import { OrderAction } from 'components/Blocks/OrderAction/OrderAction';
import { OrderSteps } from 'components/Blocks/OrderSteps/OrderSteps';
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
    const { url, isLuigisBoxActive, convertimUuid } = useDomainConfig();
    const { goToPreviousStepFromCartPage, goToNextStepFromCartPage } = useCartPageNavigation();

    return (
        <Webline>
            <OrderSteps activeStep={1} domainUrl={url} />

            <CartList items={cart.items} />

            <CartSummary />

            <OrderAction
                withGapBottom
                backStepClickHandler={goToPreviousStepFromCartPage}
                buttonBack={t('Back')}
                buttonNext={t('Transport and payment')}
                hasDisabledLook={false}
                nextStepClickHandler={goToNextStepFromCartPage}
                shouldUseConvertim={!!convertimUuid}
                withGapTop={false}
            />

            {isLuigisBoxActive && (
                <DeferredRecommendedProducts
                    itemUuids={cart.items.map((item) => item.uuid)}
                    recommendationType={TypeRecommendationType.Basket}
                    render={(recommendedProductsContent) => (
                        <div className="mb-6 px-0">
                            <div className="h2 mb-3">{t('Recommended for you')}</div> {recommendedProductsContent}
                        </div>
                    )}
                />
            )}

            {convertimUuid && <Convertim cart={cart} convertimUuid={convertimUuid} />}
        </Webline>
    );
};
