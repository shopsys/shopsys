type FilteredProductsWrapperProps = {
    paginationScrollTargetRef: React.RefObject<HTMLDivElement>;
    children: React.ReactNode;
};

export const FilteredProductsWrapper: FC<FilteredProductsWrapperProps> = ({ children, paginationScrollTargetRef }) => {
    return (
        <div
            className="vl:mb-10 vl:flex-row vl:flex-wrap vl:gap-4 mb-8 flex scroll-mt-5 flex-col"
            ref={paginationScrollTargetRef}
        >
            {children}
        </div>
    );
};
