import { Button } from 'components/Forms/Button/Button';
import { Popup } from 'components/Layout/Popup/Popup';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { getInternationalizedStaticUrls } from 'helpers/getInternationalizedStaticUrls';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';

type NotAddedProductsPopupProps = {
    notAddedProductNames: string[];
    onCloseCallback: () => void;
};

export const NotAddedProductsPopup: FC<NotAddedProductsPopupProps> = ({ notAddedProductNames, onCloseCallback }) => {
    const { t } = useTranslation();
    const router = useRouter();
    const { url } = useDomainConfig();
    const [cartUrl] = getInternationalizedStaticUrls(['/cart'], url);

    return (
        <Popup onCloseCallback={onCloseCallback}>
            <p className="mb-6 text-lg lg:text-2xl">{t('Some products could not have been added to your cart')}</p>
            <ul>
                {notAddedProductNames.map((productName) => (
                    <li key={productName} className="mb-2">
                        {productName}
                    </li>
                ))}
            </ul>
            <div className="mt-4 flex justify-end">
                <Button
                    onClick={() => {
                        onCloseCallback();
                        router.push(cartUrl);
                    }}
                >
                    {t('Go to cart')}
                </Button>
            </div>
        </Popup>
    );
};
