import { SkeletonModuleStoreList } from 'components/Blocks/Skeleton/SkeletonModuleStoreList';
import { StoreList } from 'components/Blocks/StoreList/StoreList';
import { StoreListEmpty } from 'components/Blocks/StoreList/StoreListEmpty';
import { StoreListError } from 'components/Blocks/StoreList/StoreListError';
import { StoreListLoader } from 'components/Blocks/StoreList/StoreListLoader';
import { SearchInput } from 'components/Forms/TextInput/SearchInput';
import { TypeListedStoreConnectionFragment } from 'graphql/requests/stores/fragments/ListedStoreConnectionFragment.generated';
import InfiniteScroll from 'react-infinite-scroll-component';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';

type StoreSearchListProps = {
    stores: TypeListedStoreConnectionFragment | null;
    displayedStores: StoreOrPacketeryPoint[];
    loadedStoresCount: number;
    isDistanceFromSearchText: boolean;
    isFetchingStores: boolean;
    isLoadingMoreStores: boolean;
    searchTextValue: string;
    storeConnectionErrorMessage?: string;
    scrollableTargetId?: string;
    itemMode?: 'default' | 'selectOnItemClick';
    selectedStoreUuid?: string | null;
    unknownDeliveryDateExplanation?: string;
    onLoadMoreStoresCallback?: () => void;
    onSearchTextCallback: (searchText: string) => void;
    onSelectStoreCallback?: (storeUuid: string | null) => void;
};

export const StoreSearchList: FC<StoreSearchListProps> = ({
    stores,
    displayedStores,
    loadedStoresCount,
    isDistanceFromSearchText,
    isFetchingStores,
    isLoadingMoreStores,
    searchTextValue,
    storeConnectionErrorMessage,
    scrollableTargetId,
    itemMode,
    selectedStoreUuid = null,
    unknownDeliveryDateExplanation,
    onLoadMoreStoresCallback,
    onSearchTextCallback,
    onSelectStoreCallback,
}) => {
    const { t } = useTranslation();
    const shouldShowStores = displayedStores.length > 0;
    const shouldShowStoreListSkeleton =
        isFetchingStores && !shouldShowStores && storeConnectionErrorMessage === undefined;
    const isStoreListEmpty =
        stores !== null && !shouldShowStores && !isFetchingStores && storeConnectionErrorMessage === undefined;
    const shouldLoadMoreStores =
        stores?.pageInfo.hasNextPage === true &&
        !isFetchingStores &&
        !isLoadingMoreStores &&
        onLoadMoreStoresCallback !== undefined;

    return (
        <>
            <SearchInput
                ariaLabelForSearchButton={t('Search for a store', { ns: 'accessibility' })}
                label={t('City or postcode')}
                shouldShowSpinnerInInput={isFetchingStores}
                value={searchTextValue}
                onChange={(e) => onSearchTextCallback(e.currentTarget.value)}
                onClear={() => onSearchTextCallback('')}
            />

            {storeConnectionErrorMessage !== undefined && <StoreListError message={storeConnectionErrorMessage} />}

            {shouldShowStoreListSkeleton && <SkeletonModuleStoreList />}

            {isStoreListEmpty && (
                <StoreListEmpty description={t('Try changing the city or postcode.')} message={t('No stores found')} />
            )}

            {shouldShowStores && (
                <InfiniteScroll
                    dataLength={loadedStoresCount}
                    hasMore={shouldLoadMoreStores}
                    loader={<StoreListLoader />}
                    next={onLoadMoreStoresCallback ?? (() => undefined)}
                    scrollableTarget={scrollableTargetId}
                    style={{ overflow: 'visible' }}
                >
                    <StoreList
                        itemMode={itemMode}
                        unknownDeliveryDateExplanation={unknownDeliveryDateExplanation}
                        isDistanceFromSearchText={isDistanceFromSearchText}
                        selectedStoreUuid={selectedStoreUuid}
                        stores={displayedStores}
                        onSelectStoreCallback={onSelectStoreCallback}
                    />
                </InfiniteScroll>
            )}

            {isLoadingMoreStores && <StoreListLoader />}
        </>
    );
};
