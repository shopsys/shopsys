import { FlagDetailProductsWrapper } from './FlagDetailProductsWrapper';
import { SortingBar } from 'components/Blocks/SortingBar/SortingBar';
import { Webline } from 'components/Layout/Webline/Webline';
import { FlagDetailFragment } from 'graphql/requests/flags/fragments/FlagDetailFragment.generated';
import { useRef } from 'react';

type FlagDetailContentProps = {
    flag: FlagDetailFragment;
};

export const FlagDetailContent: FC<FlagDetailContentProps> = ({ flag }) => {
    const paginationScrollTargetRef = useRef<HTMLDivElement>(null);

    return (
        <>
            <Webline>
                <h1 className="mb-3">{flag.name}</h1>
            </Webline>
            <Webline>
                <div className="scroll-mt-5" ref={paginationScrollTargetRef}>
                    <SortingBar sorting={flag.products.orderingMode} totalCount={flag.products.totalCount} />
                    <FlagDetailProductsWrapper flag={flag} paginationScrollTargetRef={paginationScrollTargetRef} />
                </div>
            </Webline>
        </>
    );
};
