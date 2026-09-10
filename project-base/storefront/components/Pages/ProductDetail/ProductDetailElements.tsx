type ProductDetailHeadingProps = {
    heading: string | null;
    name: string;
    namePrefix: string | null;
    nameSuffix: string | null;
};

const ProductDetailPrefix: FC = ({ children }) => (
    <div className="mb-1 font-secondary text-text-disabled">{children}</div>
);

const ProductDetailH1: FC = ({ children }) => <h1 className="wrap-anywhere">{children}</h1>;

/**
 * The SEO heading set in the administration replaces the whole name including its prefix and suffix
 */
export const ProductDetailHeading: FC<ProductDetailHeadingProps> = ({
    heading,
    name,
    namePrefix,
    nameSuffix,
    className,
}) => (
    <div className={className}>
        {heading ? (
            <ProductDetailH1>{heading}</ProductDetailH1>
        ) : (
            <>
                {namePrefix && <ProductDetailPrefix>{namePrefix}</ProductDetailPrefix>}

                <ProductDetailH1>
                    {name} {nameSuffix}
                </ProductDetailH1>
            </>
        )}
    </div>
);
