import { FlagDetailProductsWrapper } from './FlagDetailProductsWrapper';
import { DeferredFilterAndSortingBar } from 'components/Blocks/SortingBar/DeferredFilterAndSortingBar';
import { Webline } from 'components/Layout/Webline/Webline';
import { TypeFlagDetailFragment } from 'graphql/requests/flags/fragments/FlagDetailFragment.generated';
import { useRef } from 'react';

type FlagDetailContentProps = {
    flag: TypeFlagDetailFragment;
};

export const FlagDetailContent: FC<FlagDetailContentProps> = ({ flag }) => {
    const paginationScrollTargetRef = useRef<HTMLDivElement>(null);

    return (
        <>
            <Webline>
                <h1>{flag.name}</h1>
            </Webline>
            <Webline>
                <div className="scroll-mt-5" ref={paginationScrollTargetRef}>
                    <DeferredFilterAndSortingBar
                        sorting={flag.products.orderingMode}
                        totalCount={flag.products.totalCount}
                    />
                    <FlagDetailProductsWrapper flag={flag} paginationScrollTargetRef={paginationScrollTargetRef} />
                </div>
            </Webline>
        </>
    );
};
