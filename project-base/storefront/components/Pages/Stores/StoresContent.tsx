import { StoreList } from 'components/Blocks/StoreList/StoreList';
import { GoogleMap } from 'components/Basic/GoogleMap/GoogleMap';
import { SimpleLayout } from 'components/Layout/SimpleLayout/SimpleLayout';
import { TypeListedStoreConnectionFragment } from 'graphql/requests/stores/fragments/ListedStoreConnectionFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import { useMemo } from 'react';
import { mapConnectionEdges } from 'utils/mappers/connection';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';

type StoresContentProps = {
    stores: TypeListedStoreConnectionFragment;
};

export const StoresContent: FC<StoresContentProps> = ({ stores }) => {
    const { t } = useTranslation();
    const mappedStores = useMemo(() => mapConnectionEdges<StoreOrPacketeryPoint>(stores.edges), [stores.edges]);

    return (
        <SimpleLayout standardWidth heading={t('Stores')}>
            {mappedStores && (
                <div className="flex flex-col w-full lg:flex-row lg:gap-5">
                    <div className="w-full lg:basis-1/2 max-lg:order-2 max-lg:mt-5">
                        {mappedStores.length && <StoreList stores={mappedStores} />}
                    </div>
                    <div className="w-full lg:basis-1/2 max-lg:order-1">
                        <div className="flex aspect-square w-full mt-5 p-5 bg-backgroundMore rounded-xl lg:mt-0">
                            <GoogleMap markers={mappedStores} />
                        </div>
                    </div>
                </div>
            )}
        </SimpleLayout>
    );
};
