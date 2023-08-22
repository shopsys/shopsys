import { Webline } from 'components/Layout/Webline/Webline';
import useTranslation from 'next-translate/useTranslation';

const TEST_IDENTIFIER = 'blocks-emptycart';

export const EmptyCart: FC = () => {
    const { t } = useTranslation();

    return (
        <Webline>
            <p className="my-28 text-center text-2xl" data-testid={TEST_IDENTIFIER}>
                {t('Your cart is currently empty.')}
            </p>
        </Webline>
    );
};
