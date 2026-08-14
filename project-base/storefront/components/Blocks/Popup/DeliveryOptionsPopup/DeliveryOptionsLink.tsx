import { TruckClockIcon } from 'components/Basic/Icon/TruckClockIcon';
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
        <button
            aria-haspopup="dialog"
            className="group flex w-fit cursor-pointer items-center gap-1 rounded-md text-sm text-text-less hover:text-link-default"
            data-tid={TIDs.product_detail_delivery_options_link}
            type="button"
            onClick={() => openDeliveryOptionsPopup(products, preselectedProductUuid)}
        >
            <TruckClockIcon className="size-6 shrink-0" />

            <span className="underline">{t('Delivery & pickup')}</span>
        </button>
    );
};
