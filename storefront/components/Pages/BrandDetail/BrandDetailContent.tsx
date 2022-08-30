import { Heading } from 'components/Basic/Heading/Heading';
import { Image } from 'components/Basic/Image/Image';
import { Pagination } from 'components/Blocks/Pagination/Pagination';
import { ProductsList } from 'components/Blocks/Product/ProductsList/ProductsList';
import { SortingBar } from 'components/Blocks/SortingBar/SortingBar';
import { UserText } from 'components/Helpers/UserText/UserText';
import { Webline } from 'components/Layout/Webline/Webline';
import {
    BrandDetailImageStyled,
    BrandDetailStyled,
    BrandDetailTextStyled,
} from 'components/Pages/BrandDetail/BrandDetailContent.style';
import { getUrlWithoutGetParameters } from 'helpers/parsing/getUrlWithoutGetParameters';
import { useRemoveSortFromUrlIfDefault } from 'hooks/filter/useRemoveSortFromUrlIfDefault';
import { useGtmBrandProductListView } from 'hooks/gtm/useGtmBrandProductListView';
import { useRouter } from 'next/router';
import { FC, useRef } from 'react';
import { BrandDetailType } from 'types/brand';

type BrandDetailContentProps = {
    brand: BrandDetailType;
    fetching: boolean;
};

const TEST_IDENTIFIER = 'pages-branddetail-';

export const BrandDetailContent: FC<BrandDetailContentProps> = ({ brand, fetching }) => {
    const containerWrapRef = useRef<null | HTMLDivElement>(null);
    useRemoveSortFromUrlIfDefault(brand.productConnection.orderingMode, brand.productConnection.defaultOrderingMode);
    const router = useRouter();
    useGtmBrandProductListView(brand, getUrlWithoutGetParameters(router.asPath), fetching);

    return (
        <>
            <Webline>
                <Heading type={'h1'}>{brand.seoH1 !== null ? brand.seoH1 : brand.name}</Heading>
                <BrandDetailStyled>
                    <BrandDetailImageStyled data-testid={TEST_IDENTIFIER + 'image'}>
                        <Image image={brand.image} type="default" alt={brand.name} />
                    </BrandDetailImageStyled>
                    <BrandDetailTextStyled data-testid={TEST_IDENTIFIER + 'description'}>
                        {brand.description !== null ? <UserText htmlContent={brand.description} /> : null}
                    </BrandDetailTextStyled>
                </BrandDetailStyled>
            </Webline>
            <Webline>
                <div ref={containerWrapRef}>
                    <SortingBar
                        sorting={brand.productConnection.orderingMode}
                        totalCount={brand.productConnection.totalCount}
                    />
                    {brand.productConnection.products.length !== 0 && (
                        <ProductsList products={brand.productConnection.products} gtmListName="brand" />
                    )}
                    <Pagination totalCount={brand.productConnection.totalCount} containerWrapRef={containerWrapRef} />
                </div>
            </Webline>
        </>
    );
};
