import { ProductPrice } from 'components/Blocks/Product/ProductPrice';
import { ComparedProductFragmentApi } from 'graphql/requests/products/fragments/ComparedProductFragment.generated';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { twJoin } from 'tailwind-merge';

type ProductComparisonBodyProps = {
    productsCompare: ComparedProductFragmentApi[];
    parametersDataState: { name: string; values: string[] }[];
};

export const ProductComparisonBody: FC<ProductComparisonBodyProps> = (props) => {
    const t = useTypedTranslationFunction();

    return (
        <tbody>
            <tr className="[&>td]:bg-white [&>td]:odd:bg-greyVeryLight">
                <BodyItem isSticky>
                    <div>{t('Price with VAT')}</div>
                </BodyItem>
                {props.productsCompare.map((product) => (
                    <BodyItem key={`price-${product.uuid}`}>
                        <ProductPrice productPrice={product.price} />
                    </BodyItem>
                ))}
            </tr>
            <tr className="[&>td]:bg-white [&>td]:odd:bg-greyVeryLight">
                <BodyItem isSticky>{t('Availability')}</BodyItem>
                {props.productsCompare.map((product) => (
                    <BodyItem key={`availability-${product.uuid}`}>
                        <div
                            className={twJoin(
                                'break-words text-sm font-bold sm:text-base',
                                product.availability.status,
                                product.stockQuantity < 1 && 'text-orange',
                            )}
                        >
                            {product.availability.name}
                        </div>
                    </BodyItem>
                ))}
            </tr>

            {props.parametersDataState.map((parameter, parameterIndex) => (
                <tr className="[&>td]:bg-white [&>td]:odd:bg-greyVeryLight" key={`parameter-${parameterIndex}`}>
                    <BodyItem isSticky>{parameter.name}</BodyItem>

                    {parameter.values.map((value, valueIndex) => (
                        <BodyItem key={`parameter-${parameterIndex}-value-${valueIndex}`}>{value}</BodyItem>
                    ))}
                </tr>
            ))}
        </tbody>
    );
};

const BodyItem: FC<{ isSticky?: boolean }> = ({ children, isSticky }) => (
    <td
        className={twJoin(
            'w-[182px] break-words bg-white p-3 text-sm sm:w-[207px] sm:px-5 sm:text-base',
            isSticky && 'sticky left-0 z-above text-base text-grey',
        )}
    >
        {children}
    </td>
);
