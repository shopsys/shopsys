import { Popup } from 'components/Layout/Popup/Popup';
import { ProductDetailAvailabilityList } from 'components/Pages/ProductDetail/ProductDetailAvailabilityList';
import { TypeStoreAvailabilityFragment } from 'graphql/requests/storeAvailabilities/fragments/StoreAvailabilityFragment.generated';
import useTranslation from 'next-translate/useTranslation';

type ProductVariantsAvailabilityPopupProps = {
    storeAvailabilities: TypeStoreAvailabilityFragment[];
};

export const ProductVariantsAvailabilityPopup: FC<ProductVariantsAvailabilityPopupProps> = ({
    storeAvailabilities,
}) => {
    const { t } = useTranslation();

    return (
        <Popup className="w-11/12 max-w-2xl" contentClassName="overflow-y-auto" title={t('Availability in stores')}>
            <ProductDetailAvailabilityList storeAvailabilities={storeAvailabilities} />
        </Popup>
    );
};
