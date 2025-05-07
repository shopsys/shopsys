import { Webline } from 'components/Layout/Webline/Webline';

type FilteredProductsWrapperProps = {
    children: React.ReactNode;
};

export const FilteredProductsWrapper: FC<FilteredProductsWrapperProps> = ({ children }) => {
    return (
        <Webline>
            <div className="vl:flex-row vl:flex-wrap vl:gap-4 flex scroll-mt-5 flex-col">{children}</div>
        </Webline>
    );
};
