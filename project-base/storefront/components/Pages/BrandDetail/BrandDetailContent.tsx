import { BrandDetailProductsWrapper } from './BrandDetailProductsWrapper';
import { Heading } from 'components/Basic/Heading/Heading';
import { Image } from 'components/Basic/Image/Image';
import { SortingBar } from 'components/Blocks/SortingBar/SortingBar';
import { UserText } from 'components/Helpers/UserText/UserText';
import { Webline } from 'components/Layout/Webline/Webline';
import { BrandDetailFragmentApi } from 'graphql/generated';
import { getFirstImageOrNull } from 'helpers/mappers/image';
import { useRemoveSortFromUrlIfDefault } from 'hooks/filter/useRemoveSortFromUrlIfDefault';
import { useRef } from 'react';

type BrandDetailContentProps = {
    brand: BrandDetailFragmentApi;
};

const TEST_IDENTIFIER = 'pages-branddetail-';

export const BrandDetailContent: FC<BrandDetailContentProps> = ({ brand }) => {
    const containerWrapRef = useRef<null | HTMLDivElement>(null);
    useRemoveSortFromUrlIfDefault(brand.products.orderingMode, brand.products.defaultOrderingMode);

    const brandImage = getFirstImageOrNull(brand.images);

    return (
        <>
            <Webline>
                <Heading type="h1">{brand.seoH1 !== null ? brand.seoH1 : brand.name}</Heading>
                <div className="mb-5 flex w-full flex-col justify-start md:flex-row">
                    <div className="mr-5 min-w-[13.75rem] self-start" data-testid={TEST_IDENTIFIER + 'image'}>
                        <Image image={brandImage} type="default" alt={brandImage?.name || brand.name} />
                    </div>
                    <div
                        className="self-start  md:self-center [&>section]:text-base [&>section]:text-dark"
                        data-testid={TEST_IDENTIFIER + 'description'}
                    >
                        {brand.description !== null ? <UserText htmlContent={brand.description} /> : null}
                    </div>
                </div>
            </Webline>
            <Webline>
                <div ref={containerWrapRef}>
                    <SortingBar sorting={brand.products.orderingMode} totalCount={brand.products.totalCount} />
                    <BrandDetailProductsWrapper brand={brand} containerWrapRef={containerWrapRef} />
                </div>
            </Webline>
        </>
    );
};
