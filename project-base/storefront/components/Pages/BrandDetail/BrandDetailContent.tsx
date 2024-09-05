import { BrandDetailProductsWrapper } from './BrandDetailProductsWrapper';
import { Image } from 'components/Basic/Image/Image';
import { UserText } from 'components/Basic/UserText/UserText';
import { DeferredFilterAndSortingBar } from 'components/Blocks/SortingBar/DeferredFilterAndSortingBar';
import { Webline } from 'components/Layout/Webline/Webline';
import { TypeBrandDetailFragment } from 'graphql/requests/brands/fragments/BrandDetailFragment.generated';
import { useRef } from 'react';

type BrandDetailContentProps = {
    brand: TypeBrandDetailFragment;
};

export const BrandDetailContent: FC<BrandDetailContentProps> = ({ brand }) => {
    const paginationScrollTargetRef = useRef<HTMLDivElement>(null);

    return (
        <>
            <Webline>
                <h1>{brand.seoH1 || brand.name}</h1>
                <div className="mb-5 flex w-full flex-col justify-start md:flex-row">
                    <div className="mr-5 min-w-[13.75rem] self-start">
                        <Image
                            alt={brand.mainImage?.name || brand.name}
                            height={220}
                            src={brand.mainImage?.url}
                            width={220}
                        />
                    </div>
                    <div className="self-start md:self-center [&>section]:text-base [&>section]:text-text">
                        {brand.description !== null ? <UserText htmlContent={brand.description} /> : null}
                    </div>
                </div>
            </Webline>
            <Webline>
                <div className="scroll-mt-5" ref={paginationScrollTargetRef}>
                    <DeferredFilterAndSortingBar
                        sorting={brand.products.orderingMode}
                        totalCount={brand.products.totalCount}
                    />
                    <BrandDetailProductsWrapper brand={brand} paginationScrollTargetRef={paginationScrollTargetRef} />
                </div>
            </Webline>
        </>
    );
};
