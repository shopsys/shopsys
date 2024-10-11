import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Button } from 'components/Forms/Button/Button';
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
        <Popup>
            <p className="mb-6 text-lg lg:text-2xl">{t('Some products could not have been added to your cart')}</p>
            <ul>
                {notAddedProductNames.map((productName) => (
                    <li key={productName} className="mb-2">
                        {productName}
                    </li>
                ))}
            </ul>
            <div className="mt-4 flex justify-end">
                <ExtendedNextLink className="mt-2 w-full md:w-auto" href={cartUrl} skeletonType="cart">
                    <Button>{t('Go to cart')}</Button>
                </ExtendedNextLink>
            </div>
        </Popup>
    );
};
