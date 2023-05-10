import { FlagDetailProductsWrapper } from './FlagDetailProductsWrapper';
import { Heading } from 'components/Basic/Heading/Heading';
import { SortingBar } from 'components/Blocks/SortingBar/SortingBar';
import { Webline } from 'components/Layout/Webline/Webline';
import { FlagDetailFragmentApi } from 'graphql/generated';
import { useRemoveSortFromUrlIfDefault } from 'hooks/filter/useRemoveSortFromUrlIfDefault';
import { useRef } from 'react';

type FlagDetailContentProps = {
    flag: FlagDetailFragmentApi;
};

export const FlagDetailContent: FC<FlagDetailContentProps> = ({ flag }) => {
    const containerWrapRef = useRef<null | HTMLDivElement>(null);
    useRemoveSortFromUrlIfDefault(flag.products.orderingMode, flag.products.defaultOrderingMode);

    return (
        <>
            <Webline>
                <Heading type="h1">{flag.name}</Heading>
            </Webline>
            <Webline>
                <div ref={containerWrapRef}>
                    <SortingBar sorting={flag.products.orderingMode} totalCount={flag.products.totalCount} />
                    <FlagDetailProductsWrapper flag={flag} containerWrapRef={containerWrapRef} />
                </div>
            </Webline>
        </>
    );
};
