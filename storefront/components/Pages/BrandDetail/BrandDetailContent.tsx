import { BrandDetailProductsWrapper } from './BrandDetailProductsWrapper';
import { Heading } from 'components/Basic/Heading/Heading';
import { Image } from 'components/Basic/Image/Image';
import { PaginationProvider } from 'components/Blocks/Pagination/PaginationProvider';
import { SortingBar } from 'components/Blocks/SortingBar/SortingBar';
import { UserText } from 'components/Helpers/UserText/UserText';
import { Webline } from 'components/Layout/Webline/Webline';
import {
    BrandDetailImageStyled,
    BrandDetailStyled,
    BrandDetailTextStyled,
} from 'components/Pages/BrandDetail/BrandDetailContent.style';
import { getNewPagination } from 'helpers/pagination/getNewPagination';
import { parsePageNumberFromQuery } from 'helpers/pagination/parsePageNumberFromQuery';
import { PAGE_QUERY_PARAMETER_NAME } from 'helpers/queryParams/queryParamNames';
import { useRemoveSortFromUrlIfDefault } from 'hooks/filter/useRemoveSortFromUrlIfDefault';
import { useRouter } from 'next/router';
import { FC, useRef } from 'react';
import { BrandDetailType } from 'types/brand';

type BrandDetailContentProps = {
    brand: BrandDetailType;
};

const TEST_IDENTIFIER = 'pages-branddetail-';

export const BrandDetailContent: FC<BrandDetailContentProps> = ({ brand }) => {
    const containerWrapRef = useRef<null | HTMLDivElement>(null);
    const router = useRouter();
    const currentPage = parsePageNumberFromQuery(router.query[PAGE_QUERY_PARAMETER_NAME]);
    useRemoveSortFromUrlIfDefault(brand.productConnection.orderingMode, brand.productConnection.defaultOrderingMode);

    return (
        <PaginationProvider key={brand.uuid} {...getNewPagination(currentPage)}>
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
                    <BrandDetailProductsWrapper brand={brand} containerWrapRef={containerWrapRef} />
                </div>
            </Webline>
        </PaginationProvider>
    );
};
