import { LinkButton } from 'components/Forms/Button/LinkButton';
import { Popup } from 'components/Layout/Popup/Popup';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

type NotAddedProductsPopupProps = {
    notAddedProductNames: string[];
};

export const NotAddedProductsPopup: FC<NotAddedProductsPopupProps> = ({ notAddedProductNames }) => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [cartUrl] = getInternationalizedStaticUrls(['/cart'], url);

    return (
        <Popup size="small" title={t('Products from the order were added to your cart')}>
            <p className="mb-2">{t('These products cannot currently be purchased and were not added:')}</p>
            <ul>
                {notAddedProductNames.map((productName) => (
                    <li key={productName} className="mb-2">
                        {productName}
                    </li>
                ))}
            </ul>
            <div className="flex justify-end">
                <LinkButton href={cartUrl} skeletonType="cart">
                    {t('Go to cart')}
                </LinkButton>
            </div>
        </Popup>
    );
};
