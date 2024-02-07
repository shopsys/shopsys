import { Webline } from 'components/Layout/Webline/Webline';
import useTranslation from 'next-translate/useTranslation';

export const EmptyCart: FC = () => {
    const { t } = useTranslation();

    return (
        <Webline>
            <p className="my-28 text-center text-2xl">{t('Your cart is currently empty.')}</p>
        </Webline>
    );
};
