import { TruckClockIcon } from 'components/Basic/Icon/TruckClockIcon';
import { TextButton } from 'components/Basic/TextButton/TextButton';
import { TIDs } from 'cypress/tids';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { DeliveryOptionsProduct } from './deliveryOptionsPopupTypes';
import { useOpenDeliveryOptionsPopup } from './useOpenDeliveryOptionsPopup';

type DeliveryOptionsLinkProps = {
    products: DeliveryOptionsProduct[];
    preselectedProductUuid?: string;
};

export const DeliveryOptionsLink: FC<DeliveryOptionsLinkProps> = ({ products, preselectedProductUuid }) => {
    const { t } = useTranslation();
    const openDeliveryOptionsPopup = useOpenDeliveryOptionsPopup();

    return (
        <TextButton
            aria-haspopup="dialog"
            className="w-fit"
            tid={TIDs.product_detail_delivery_options_link}
            tone="subtle"
            onClick={() => openDeliveryOptionsPopup(products, preselectedProductUuid)}
        >
            <TruckClockIcon aria-hidden="true" className="size-4 shrink-0" />
            {t('Delivery & pickup')}
        </TextButton>
    );
};
