import { Popup } from 'components/Layout/Popup/Popup';
import { ProductDetailAvailabilityList } from 'components/Pages/ProductDetail/ProductDetailAvailabilityList';
import { TypeSimpleStoreAvailabilityFragment } from 'graphql/requests/storeAvailabilities/fragments/SimpleStoreAvailabilityFragment.generated';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type ProductVariantsAvailabilityPopupProps = {
    storeAvailabilities: TypeSimpleStoreAvailabilityFragment[];
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
