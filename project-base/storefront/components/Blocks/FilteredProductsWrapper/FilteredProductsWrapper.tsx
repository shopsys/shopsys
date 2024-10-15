type FilteredProductsWrapperProps = {
    ref: React.RefObject<HTMLDivElement>;
    children: React.ReactNode;
};

export const FilteredProductsWrapper: FC<FilteredProductsWrapperProps> = ({ children, ref }) => {
    return (
        <div className="mb-8 flex scroll-mt-5 flex-col vl:mb-10 vl:flex-row vl:flex-wrap vl:gap-4" ref={ref}>
            {children}
        </div>
    );
};
