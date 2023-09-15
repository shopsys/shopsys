export const ProductDetailPrefix: FC = ({ children, dataTestId }) => (
    <div className="text-greyLight" data-testid={dataTestId}>
        {children}
    </div>
);

export const ProductDetailHeading: FC = ({ children, dataTestId }) => (
    <h1 className="text-2xl font-bold text-black" data-testid={dataTestId}>
        {children}
    </h1>
);

export const ProductDetailCode: FC = ({ children, dataTestId }) => (
    <div className="text-greyLight" data-testid={dataTestId}>
        {children}
    </div>
);
