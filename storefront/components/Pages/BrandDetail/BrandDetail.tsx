import Heading from 'components/Basic/Heading';
import Image from 'components/Basic/Image';
import Pagination from 'components/Blocks/Pagination';
import ProductsList from 'components/Blocks/Product/List/ProductsList';
import SortingBar from 'components/Blocks/SortingBar';
import UserText from 'components/Helpers/UserText';
import Webline from 'components/Layout/Webline';
import {
    BrandDetailImageStyled,
    BrandDetailStyled,
    BrandDetailTextStyled,
} from 'components/Pages/BrandDetail/BrandDetail.style';
import { FC, useRef } from 'react';
import { BrandDetailType } from 'types/brand';

type BrandDetailProps = {
    brand: BrandDetailType;
};

const BrandDetail: FC<BrandDetailProps> = (props) => {
    const testIdentifier = 'pages-branddetail-';

    const containerWrapRef = useRef<null | HTMLDivElement>(null);

    return (
        <>
            <Webline>
                <Heading type={'h1'}>{props.brand.seoH1 !== null ? props.brand.seoH1 : props.brand.name}</Heading>
                <BrandDetailStyled>
                    <BrandDetailImageStyled data-testid={testIdentifier + 'image'}>
                        <Image image={props.brand.image} type="default" alt={props.brand.name} />
                    </BrandDetailImageStyled>
                    <BrandDetailTextStyled data-testid={testIdentifier + 'description'}>
                        {props.brand.description !== null ? <UserText htmlContent={props.brand.description} /> : null}
                    </BrandDetailTextStyled>
                </BrandDetailStyled>
            </Webline>
            <Webline>
                <div ref={containerWrapRef}>
                    <SortingBar
                        sorting={props.brand.productConnection.orderingMode}
                        totalCount={props.brand.productConnection.totalCount}
                    />
                    {props.brand.productConnection.products.length !== 0 && (
                        <ProductsList products={props.brand.productConnection.products} gtmListName="brand" />
                    )}
                    <Pagination
                        totalCount={props.brand.productConnection.totalCount}
                        containerWrapRef={containerWrapRef}
                    />
                </div>
            </Webline>
        </>
    );
};

export default BrandDetail;
