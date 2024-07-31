import { ProductPrice } from 'components/Blocks/Product/ProductPrice';
import { TypeProductInProductListFragment } from 'graphql/requests/productLists/fragments/ProductInProductListFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import { twJoin } from 'tailwind-merge';

type ProductComparisonBodyProps = {
    comparedProducts: TypeProductInProductListFragment[];
    parametersDataState: { name: string; unit: string | undefined; values: string[] }[];
};

export const ProductComparisonBody: FC<ProductComparisonBodyProps> = ({ comparedProducts, parametersDataState }) => {
    const { t } = useTranslation();

    return (
        <tbody>
            <tr className="[&>td]:bg-white [&>td]:odd:bg-whiteSnow">
                <BodyItem isSticky>
                    <div>{t('Price with VAT')}</div>
                </BodyItem>
                {comparedProducts.map((product) => (
                    <BodyItem key={`price-${product.uuid}`}>
                        <ProductPrice productPrice={product.price} />
                    </BodyItem>
                ))}
            </tr>
            <tr className="[&>td]:bg-white [&>td]:odd:bg-whiteSnow">
                <BodyItem isSticky>{t('Availability')}</BodyItem>
                {comparedProducts.map((product) => (
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

            {parametersDataState.map((parameter, parameterIndex) => (
                <tr key={`parameter-${parameterIndex}`} className="[&>td]:bg-white [&>td]:odd:bg-whiteSnow">
                    <BodyItem isSticky>{parameter.name}</BodyItem>

                    {parameter.values.map((value, valueIndex) => (
                        <BodyItem key={`parameter-${parameterIndex}-value-${valueIndex}`}>
                            {value}
                            {parameter.unit !== undefined && value !== '-' ? ` ${parameter.unit}` : ''}
                        </BodyItem>
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
            isSticky && 'sticky left-0 z-above text-base text-skyBlue',
        )}
    >
        {children}
    </td>
);
