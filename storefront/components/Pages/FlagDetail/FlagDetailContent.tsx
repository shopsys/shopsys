import { Heading } from 'components/Basic/Heading/Heading';
import { Pagination } from 'components/Blocks/Pagination/Pagination';
import { ProductsList } from 'components/Blocks/Product/ProductsList/ProductsList';
import { SortingBar } from 'components/Blocks/SortingBar/SortingBar';
import { Webline } from 'components/Layout/Webline/Webline';
import { getUrlWithoutGetParameters } from 'helpers/parsing/getUrlWithoutGetParameters';
import { useRemoveSortFromUrlIfDefault } from 'hooks/filter/useRemoveSortFromUrlIfDefault';
import { useGtmFlagProductListView } from 'hooks/gtm/useGtmFlagProductListView';
import { useRouter } from 'next/router';
import { FC, useRef } from 'react';
import { FlagDetailType } from 'types/flag';

type FlagDetailContentProps = {
    flag: FlagDetailType;
    fetching: boolean;
};

export const FlagDetailContent: FC<FlagDetailContentProps> = ({ flag, fetching }) => {
    const containerWrapRef = useRef<null | HTMLDivElement>(null);
    useRemoveSortFromUrlIfDefault(flag.productConnection.orderingMode, flag.productConnection.defaultOrderingMode);
    const router = useRouter();
    useGtmFlagProductListView(flag, getUrlWithoutGetParameters(router.asPath), fetching);

    return (
        <>
            <Webline>
                <Heading type={'h1'}>{flag.name}</Heading>
            </Webline>
            <Webline>
                <div ref={containerWrapRef}>
                    <SortingBar
                        sorting={flag.productConnection.orderingMode}
                        totalCount={flag.productConnection.totalCount}
                    />
                    {flag.productConnection.products.length !== 0 && (
                        <ProductsList
                            products={flag.productConnection.products}
                            gtmListName="flag"
                            fetching={fetching}
                        />
                    )}
                    <Pagination totalCount={flag.productConnection.totalCount} containerWrapRef={containerWrapRef} />
                </div>
            </Webline>
        </>
    );
};
