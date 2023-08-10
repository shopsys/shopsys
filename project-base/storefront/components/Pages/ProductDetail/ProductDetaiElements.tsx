export const ProductDetail: FC = ({ children }) => (
    <div className="mb-5 flex flex-col gap-6 lg:flex-row">{children}</div>
);

export const ProductDetailInfo: FC = ({ children }) => <div className="mb-4 flex-1">{children}</div>;

export const ProductDetailPrefix: FC = ({ children, dataTestId }) => (
    <div className="mb-1 text-greyLight" data-testid={dataTestId}>
        {children}
    </div>
);

export const ProductDetailHeading: FC = ({ children, dataTestId }) => (
    <h1 className="mb-2 text-2xl font-bold text-black" data-testid={dataTestId}>
        {children}
    </h1>
);

export const ProductDetailCode: FC = ({ children, dataTestId }) => (
    <div className="mb-5 text-greyLight" data-testid={dataTestId}>
        {children}
    </div>
);
