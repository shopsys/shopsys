import { BodyItemAvailabilityStyled, BodyItemStyled, BodyRowStyled } from './Body.style';
import clsx from 'clsx';
import { ProductPrice } from 'components/Blocks/Product/Price/ProductPrice';
import { ComparedProductFragmentApi } from 'graphql/generated';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { FC } from 'react';

type BodyProps = {
    productsCompare: ComparedProductFragmentApi[];
    parametersDataState: { name: string; values: string[] }[];
};

const Body: FC<BodyProps> = (props) => {
    const t = useTypedTranslationFunction();

    return (
        <tbody>
            <BodyRowStyled>
                <BodyItemStyled className="isTitle isSticky">
                    <div>{t('Price with VAT')}</div>
                </BodyItemStyled>
                {props.productsCompare.map((product) => {
                    return (
                        <BodyItemStyled key={`price-${product.uuid}`}>
                            <ProductPrice productPrice={product.price} />
                        </BodyItemStyled>
                    );
                })}
            </BodyRowStyled>
            <BodyRowStyled>
                <BodyItemStyled className="isTitle isSticky">{t('Availability')}</BodyItemStyled>
                {props.productsCompare.map((product) => {
                    return (
                        <BodyItemStyled key={`availability-${product.uuid}`}>
                            <BodyItemAvailabilityStyled
                                className={clsx(
                                    product.availability.status,
                                    product.stockQuantity < 1 && 'to-delivery',
                                )}
                            >
                                {product.availability.name}
                            </BodyItemAvailabilityStyled>
                        </BodyItemStyled>
                    );
                })}
            </BodyRowStyled>

            {props.parametersDataState.map((parameter, parameterIndex) => {
                return (
                    <BodyRowStyled key={`parameter-${parameterIndex}`}>
                        <BodyItemStyled className="isTitle isSticky">{parameter.name}</BodyItemStyled>

                        {parameter.values.map((value, valueIndex) => {
                            return (
                                <BodyItemStyled key={`parameter-${parameterIndex}-value-${valueIndex}`}>
                                    {value}
                                </BodyItemStyled>
                            );
                        })}
                    </BodyRowStyled>
                );
            })}
        </tbody>
    );
};

export default Body;
