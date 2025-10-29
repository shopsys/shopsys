import { CartIcon } from 'components/Basic/Icon/CartIcon';
import { PageHero } from 'components/Layout/PageHero/PageHero';
import { Webline } from 'components/Layout/Webline/Webline';
import { TIDs } from 'cypress/tids';
import useTranslation from 'utils/i18n/useTranslationWrapper';

export const EmptyCart: FC = () => {
    const { t } = useTranslation();

    return (
        <Webline className="mt-8" width="lg">
            <PageHero
                actionHref="/"
                actionSkeletonType="homepage"
                actionTitle={t("Let's shop")}
                icon={CartIcon}
                title={t('Your cart')}
                titleTid={TIDs.cart_page_empty_cart_text}
                description={t(
                    'Your cart is currently empty. Add items to your cart to see them here and easily complete your purchase!',
                )}
            />
        </Webline>
    );
};
