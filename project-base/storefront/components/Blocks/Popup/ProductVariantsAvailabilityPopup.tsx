import { Popup } from 'components/Layout/Popup/Popup';
import { ProductDetailAvailabilityList } from 'components/Pages/ProductDetail/ProductDetailAvailabilityList';
import { TypeStoreAvailabilityFragment } from 'graphql/requests/storeAvailabilities/fragments/StoreAvailabilityFragment.generated';

type ProductVariantsAvailabilityPopupProps = {
    storeAvailabilities: TypeStoreAvailabilityFragment[];
};

export const ProductVariantsAvailabilityPopup: FC<ProductVariantsAvailabilityPopupProps> = ({
    storeAvailabilities,
}) => {
    return (
        <Popup className="w-11/12 max-w-2xl">
            <ProductDetailAvailabilityList storeAvailabilities={storeAvailabilities} />
        </Popup>
    );
};
