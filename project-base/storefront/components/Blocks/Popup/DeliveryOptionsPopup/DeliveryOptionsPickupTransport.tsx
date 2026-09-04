import { StoreSearchList } from 'components/Blocks/StoreList/StoreSearchList';
import { usePaginatedStoreConnection } from 'components/Blocks/StoreList/usePaginatedStoreConnection';
import { TypeProductDeliveryOptionFragment } from 'graphql/requests/transports/fragments/ProductDeliveryOptionFragment.generated';
import {
    ProductDeliveryStoresQueryDocument,
    TypeProductDeliveryStoresQuery,
} from 'graphql/requests/transports/queries/ProductDeliveryStoresQuery.generated';
import { useMemo } from 'react';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { mapConnectionEdges } from 'utils/mappers/connection';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';
import { DeliveryOptionRow } from './DeliveryOptionRow';
import { DeliveryOptionsProduct } from './deliveryOptionsPopupTypes';
import { getProductDeliveryStoresConnectionFromData } from './deliveryOptionsPopupUtils';

type DeliveryOptionsPickupTransportProps = {
    deliveryOption: TypeProductDeliveryOptionFragment;
    product: DeliveryOptionsProduct;
    scrollableTargetId: string;
};

export const DeliveryOptionsPickupTransport: FC<DeliveryOptionsPickupTransportProps> = ({
    deliveryOption,
    product,
    scrollableTargetId,
}) => {
    const { t } = useTranslation();
    const additionalQueryVariables = useMemo(
        () => ({ transportUuid: deliveryOption.transport.uuid, productUuid: product.uuid }),
        [deliveryOption.transport.uuid, product.uuid],
    );
    const {
        isDistanceFromSearchText,
        isFetchingStores,
        isLoadingMoreStores,
        loadMoreStores,
        searchTextValue,
        setSearchTextValue,
        storeConnectionError,
        stores,
    } = usePaginatedStoreConnection<TypeProductDeliveryStoresQuery, { transportUuid: string; productUuid: string }>({
        queryDocument: ProductDeliveryStoresQueryDocument,
        additionalQueryVariables,
        getStoreConnectionFromData: getProductDeliveryStoresConnectionFromData,
        requestPolicy: 'network-only',
    });

    const mappedStores = useMemo(
        () => (stores === null ? [] : (mapConnectionEdges<StoreOrPacketeryPoint>(stores.edges || []) ?? [])),
        [stores],
    );

    return (
        <div className="flex flex-col">
            <DeliveryOptionRow deliveryOption={deliveryOption} />

            <StoreSearchList
                displayedStores={mappedStores}
                isDistanceFromSearchText={isDistanceFromSearchText}
                isFetchingStores={isFetchingStores}
                isLoadingMoreStores={isLoadingMoreStores}
                loadedStoresCount={mappedStores.length}
                scrollableTargetId={scrollableTargetId}
                searchTextValue={searchTextValue}
                stores={stores}
                unknownDeliveryDateExplanation={t(
                    'The product is out of stock and we do not know its restocking date yet',
                )}
                onLoadMoreStoresCallback={loadMoreStores}
                onSearchTextCallback={setSearchTextValue}
                storeConnectionErrorMessage={
                    storeConnectionError !== undefined
                        ? t('Stores could not be loaded. Please try again later.')
                        : undefined
                }
            />
        </div>
    );
};
