import { ProductPrice } from 'components/Blocks/Product/ProductPrice';
import { TypeProductInProductListFragment } from 'graphql/requests/productLists/fragments/ProductInProductListFragment.generated';
import { TypeAvailabilityStatusEnum } from 'graphql/types';
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
            <tr className="[&>td]:bg-table-bg-default [&>td]:odd:bg-table-bg-contrast">
                <BodyItem isSticky>
                    <div>{t('Price with VAT')}</div>
                </BodyItem>
                {comparedProducts.map((product) => (
                    <BodyItem key={`price-${product.uuid}`}>
                        <ProductPrice placeholder="-" productPrice={product.price} />
                    </BodyItem>
                ))}
            </tr>

            <tr className="[&>td]:bg-table-bg-default [&>td]:odd:bg-table-bg-contrast">
                <BodyItem isSticky>{t('Availability')}</BodyItem>
                {comparedProducts
                    .filter((product) => !product.isSellingDenied)
                    .map((product) => (
                        <BodyItem key={`availability-${product.uuid}`}>
                            <div
                                className={twJoin(
                                    'text-sm font-bold break-words sm:text-base',
                                    product.availability.status === TypeAvailabilityStatusEnum.InStock &&
                                        'text-availability-in-stock',
                                    product.availability.status === TypeAvailabilityStatusEnum.OutOfStock &&
                                        'text-availability-out-of-stock',
                                )}
                            >
                                {product.availability.name}
                            </div>
                        </BodyItem>
                    ))}
            </tr>

            {parametersDataState.map((parameter, parameterIndex) => (
                <tr
                    key={`parameter-${parameterIndex}`}
                    className="[&>td]:bg-table-bg-default [&>td]:odd:bg-table-bg-contrast"
                >
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
            'bg-table-bg-default w-[182px] p-3 text-sm break-words sm:w-[207px] sm:px-5 sm:text-base',
            isSticky && 'z-above text-text-accent sticky left-0 text-base',
        )}
    >
        {children}
    </td>
);
