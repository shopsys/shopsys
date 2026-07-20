type ProductDetailTitleProps = {
    name: string;
    namePrefix: string | null;
    nameSuffix: string | null;
};

const ProductDetailPrefix: FC = ({ children }) => (
    <div className="mb-1 font-secondary text-text-disabled">{children}</div>
);

const ProductDetailHeading: FC = ({ children }) => <h1 className="wrap-anywhere">{children}</h1>;

export const ProductDetailTitle: FC<ProductDetailTitleProps> = ({ name, namePrefix, nameSuffix, className }) => (
    <div className={className}>
        {namePrefix && <ProductDetailPrefix>{namePrefix}</ProductDetailPrefix>}

        <ProductDetailHeading>
            {name} {nameSuffix}
        </ProductDetailHeading>
    </div>
);
