import { Webline } from 'components/Layout/Webline/Webline';
import { TIDs } from 'cypress/tids';
import useTranslation from 'next-translate/useTranslation';

export const EmptyCart: FC = () => {
    const { t } = useTranslation();

    return (
        <Webline>
            <p className="my-28 text-center text-2xl" tid={TIDs.cart_page_empty_cart_text}>
                {t('Your cart is currently empty.')}
            </p>
        </Webline>
    );
};
