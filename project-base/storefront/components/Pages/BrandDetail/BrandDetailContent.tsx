import { BrandDetailProductsWrapper } from './BrandDetailProductsWrapper';
import { Heading } from 'components/Basic/Heading/Heading';
import { Image } from 'components/Basic/Image/Image';
import { SortingBar } from 'components/Blocks/SortingBar/SortingBar';
import { UserText } from 'components/Basic/UserText/UserText';
import { Webline } from 'components/Layout/Webline/Webline';
import { useRef } from 'react';
import { BrandDetailFragmentApi } from 'graphql/requests/brands/fragments/BrandDetailFragment.generated';

type BrandDetailContentProps = {
    brand: BrandDetailFragmentApi;
};

const TEST_IDENTIFIER = 'pages-branddetail-';

export const BrandDetailContent: FC<BrandDetailContentProps> = ({ brand }) => {
    const paginationScrollTargetRef = useRef<HTMLDivElement>(null);

    return (
        <>
            <Webline>
                <Heading type="h1">{brand.seoH1 !== null ? brand.seoH1 : brand.name}</Heading>
                <div className="mb-5 flex w-full flex-col justify-start md:flex-row">
                    <div className="mr-5 min-w-[13.75rem] self-start" data-testid={TEST_IDENTIFIER + 'image'}>
                        <Image image={brand.mainImage} type="default" alt={brand.mainImage?.name || brand.name} />
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
                <div ref={paginationScrollTargetRef} className="scroll-mt-5">
                    <SortingBar sorting={brand.products.orderingMode} totalCount={brand.products.totalCount} />
                    <BrandDetailProductsWrapper brand={brand} paginationScrollTargetRef={paginationScrollTargetRef} />
                </div>
            </Webline>
        </>
    );
};
