import { Popup } from 'components/Layout/Popup/Popup';
import { TypeSimpleStoreAvailabilityFragment } from 'graphql/requests/storeAvailabilities/fragments/SimpleStoreAvailabilityFragment.generated';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { ProductDetailAvailabilityList } from './ProductDetailAvailabilityList';

type ProductDetailAvailabilityPopupProps = {
    storeAvailabilities: TypeSimpleStoreAvailabilityFragment[];
};

export const ProductDetailAvailabilityPopup: FC<ProductDetailAvailabilityPopupProps> = ({ storeAvailabilities }) => {
    const { t } = useTranslation();

    return (
        <Popup contentClassName="overflow-auto" title={t('Availability in stores')}>
            <ProductDetailAvailabilityList storeAvailabilities={storeAvailabilities} />
        </Popup>
    );
};
