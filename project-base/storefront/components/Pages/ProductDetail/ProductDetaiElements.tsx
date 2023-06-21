export const ProductDetail: FC = ({ children }) => (
    <div className="mb-5 flex flex-col flex-wrap lg:flex-row">{children}</div>
);

export const ProductDetailImage: FC = ({ children, dataTestId }) => (
    <div
        className="lg:w-[calc(100%-346px)] vl:w-[calc(100%-512px)] [&>div]:relative [&>div]:mb-5 [&>div]:flex [&>div]:w-full [&>div]:flex-row [&>div]:items-start [&>div]:justify-start [&>div]:overflow-hidden lg:[&>div]:rounded-xl"
        data-testid={dataTestId}
    >
        {children}
    </div>
);

export const ProductDetailInfo: FC = ({ children }) => (
    <div className="mb-4 w-full lg:mb-8 lg:max-w-sm lg:pl-6 vl:max-w-lg">{children}</div>
);

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
