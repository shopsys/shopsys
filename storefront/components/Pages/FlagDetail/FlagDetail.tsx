import Heading from 'components/Basic/Heading';
import Pagination from 'components/Blocks/Pagination';
import ProductsList from 'components/Blocks/Product/List/ProductsList';
import SortingBar from 'components/Blocks/SortingBar';
import Webline from 'components/Layout/Webline';
import { useRemoveSortFromUrlIfDefault } from 'hooks/filter/UseRemoveSortFromUrlIfDefault';
import { FC, useRef } from 'react';
import { FlagDetailType } from 'types/flag';

type FlagDetailProps = {
    flag: FlagDetailType;
};

const FlagDetail: FC<FlagDetailProps> = ({ flag }) => {
    const containerWrapRef = useRef<null | HTMLDivElement>(null);
    useRemoveSortFromUrlIfDefault(flag.productConnection.orderingMode, flag.productConnection.defaultOrderingMode);

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
                        <ProductsList products={flag.productConnection.products} gtmListName="flag" />
                    )}
                    <Pagination totalCount={flag.productConnection.totalCount} containerWrapRef={containerWrapRef} />
                </div>
            </Webline>
        </>
    );
};

export default FlagDetail;
