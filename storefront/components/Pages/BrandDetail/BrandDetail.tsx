import {
    BrandDetailImageStyled,
    BrandDetailStyled,
    BrandDetailTextStyled,
} from 'components/Pages/BrandDetail/BrandDetail.style';
import { BrandDetailType } from 'types/brand';
import { FC } from 'react';
import Heading from 'components/Basic/Heading';
import Image from 'components/Basic/Image';
import Pagination from 'components/Blocks/Pagination';
import ProductsList from 'components/Blocks/Product/List/ProductsList';
import SortingBar from 'components/Blocks/SortingBar';
import UserText from 'components/Helpers/UserText';
import Webline from 'components/Layout/Webline';

type BrandDetailProps = {
    brand: BrandDetailType;
};

const BrandDetail: FC<BrandDetailProps> = (props) => {
    return (
        <>
            <Webline>
                <Heading type={'h1'}>{props.brand.seoH1 !== null ? props.brand.seoH1 : props.brand.name}</Heading>
                <BrandDetailStyled>
                    <BrandDetailImageStyled>
                        <Image image={props.brand.image} alt={props.brand.name} />
                    </BrandDetailImageStyled>
                    <BrandDetailTextStyled>
                        {props.brand.description !== null ? <UserText htmlContent={props.brand.description} /> : null}
                    </BrandDetailTextStyled>
                </BrandDetailStyled>
            </Webline>
            <Webline>
                <SortingBar totalCount={props.brand.products.totalCount} />
                {props.brand.products.edges.length !== 0 && (
                    <ProductsList products={props.brand.products.edges.map((edge) => edge.node)} />
                )}
                <Pagination totalCount={props.brand.products.totalCount} />
            </Webline>
        </>
    );
};

export default BrandDetail;
