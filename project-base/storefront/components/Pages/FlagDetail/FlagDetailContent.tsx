import { FlagDetailProductsWrapper } from './FlagDetailProductsWrapper';
import { Heading } from 'components/Basic/Heading/Heading';
import { SortingBar } from 'components/Blocks/SortingBar/SortingBar';
import { Webline } from 'components/Layout/Webline/Webline';
import { FlagDetailFragmentApi } from 'graphql/generated';
import { useRef } from 'react';

type FlagDetailContentProps = {
    flag: FlagDetailFragmentApi;
};

export const FlagDetailContent: FC<FlagDetailContentProps> = ({ flag }) => {
    const paginationScrollTargetRef = useRef<HTMLDivElement>(null);

    return (
        <>
            <Webline>
                <Heading type="h1">{flag.name}</Heading>
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
