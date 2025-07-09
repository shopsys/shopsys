import { LinkButton } from 'components/Forms/Button/LinkButton';
import { Popup } from 'components/Layout/Popup/Popup';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import useTranslation from 'next-translate/useTranslation';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

type NotAddedProductsPopupProps = {
    notAddedProductNames: string[];
};

export const NotAddedProductsPopup: FC<NotAddedProductsPopupProps> = ({ notAddedProductNames }) => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [cartUrl] = getInternationalizedStaticUrls(['/cart'], url);

    return (
        <Popup title={t('Some products could not have been added to your cart')}>
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
