import { FC, useRef } from 'react';
import { FlagDetailType } from 'types/flag';
import Heading from 'components/Basic/Heading';
import Pagination from 'components/Blocks/Pagination';
import ProductsList from 'components/Blocks/Product/List/ProductsList';
import SortingBar from 'components/Blocks/SortingBar';
import Webline from 'components/Layout/Webline';

type FlagDetailProps = {
    flag: FlagDetailType;
};

const FlagDetail: FC<FlagDetailProps> = (props) => {
    const containerWrapRef = useRef<null | HTMLDivElement>(null);

    return (
        <>
            <Webline>
                <Heading type={'h1'}>{props.flag.name}</Heading>
            </Webline>
            <Webline>
                <div ref={containerWrapRef}>
                    <SortingBar totalCount={props.flag.products.totalCount} />
                    {props.flag.products.edges.length !== 0 && (
                        <ProductsList products={props.flag.products.edges.map((edge) => edge.node)} />
                    )}
                    <Pagination totalCount={props.flag.products.totalCount} containerWrapRef={containerWrapRef} />
                </div>
            </Webline>
        </>
    );
};

export default FlagDetail;
