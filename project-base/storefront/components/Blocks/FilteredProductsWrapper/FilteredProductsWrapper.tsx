import { Webline } from 'components/Layout/Webline/Webline';

type FilteredProductsWrapperProps = {
    children: React.ReactNode;
};

export const FilteredProductsWrapper: FC<FilteredProductsWrapperProps> = ({ children }) => {
    return (
        <Webline>
            <div className="flex scroll-mt-5 vl:flex-row flex-col vl:flex-wrap vl:gap-4">{children}</div>
        </Webline>
    );
};
