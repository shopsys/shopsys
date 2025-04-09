import { Webline } from 'components/Layout/Webline/Webline';

type FilteredProductsWrapperProps = {
    paginationScrollTargetRef: React.RefObject<HTMLDivElement>;
    children: React.ReactNode;
};

export const FilteredProductsWrapper: FC<FilteredProductsWrapperProps> = ({ children, paginationScrollTargetRef }) => {
    return (
        <Webline>
            <div
                className="vl:flex-row vl:flex-wrap vl:gap-4 flex scroll-mt-5 flex-col"
                ref={paginationScrollTargetRef}
            >
                {children}
            </div>
        </Webline>
    );
};
