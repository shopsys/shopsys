import { BrandDetailProductsWrapper } from './BrandDetailProductsWrapper';
import { Image } from 'components/Basic/Image/Image';
import { UserText } from 'components/Basic/UserText/UserText';
import { SortingBar } from 'components/Blocks/SortingBar/SortingBar';
import { Webline } from 'components/Layout/Webline/Webline';
import { BrandDetailFragmentApi } from 'graphql/generated';
import { useRef } from 'react';

type BrandDetailContentProps = {
    brand: BrandDetailFragmentApi;
};

const TEST_IDENTIFIER = 'pages-branddetail-';

export const BrandDetailContent: FC<BrandDetailContentProps> = ({ brand }) => {
    const paginationScrollTargetRef = useRef<HTMLDivElement>(null);

    return (
        <>
            <Webline>
                <h1 className="mb-3">{brand.seoH1 !== null ? brand.seoH1 : brand.name}</h1>
                <div className="mb-5 flex w-full flex-col justify-start md:flex-row">
                    <div className="mr-5 min-w-[13.75rem] self-start" data-testid={TEST_IDENTIFIER + 'image'}>
                        <Image
                            alt={brand.mainImage?.name || brand.name}
                            height={220}
                            src={brand.mainImage?.url}
                            width={220}
                        />
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
                <div className="scroll-mt-5" ref={paginationScrollTargetRef}>
                    <SortingBar sorting={brand.products.orderingMode} totalCount={brand.products.totalCount} />
                    <BrandDetailProductsWrapper brand={brand} paginationScrollTargetRef={paginationScrollTargetRef} />
                </div>
            </Webline>
        </>
    );
};
