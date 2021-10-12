import { FormProvider, useForm } from 'react-hook-form';
import { FC } from 'react';
import ProductFilterGroup from './ProductFilterGroup';
import { ProductFilterStyled } from './ProductFilter.style';

const ProductFilter: FC = () => {
    const formProviderMethods = useForm({
        mode: 'onChange',
        defaultValues: {
            /**
             * These values get mapped into the initial checked state of checkboxes
             * true = checked
             * false = unchecked
             */
            checkboxDefault: false,
            checkboxRequired: false,
            checkboxChecked: true,
            checkboxDisabled: false,
            checkboxDisabledChecked: true,
            checkboxWithLink: false,
            minPrice: 0, // TODO PRG
            maxPrice: 1000, // TODO PRG
        },
    });

    /**
     * TODO PRG: add ProductFilterGroup according to API
     */
    return (
        <ProductFilterStyled>
            <FormProvider {...formProviderMethods}>
                <ProductFilterGroup title="Cena" type="price" isOpen={true} />
                <ProductFilterGroup title="Dostupnost" type="checkbox" />
                <ProductFilterGroup title="Příznaky" type="checkbox" />
                <ProductFilterGroup title="Barva" type="color" />
            </FormProvider>
        </ProductFilterStyled>
    );
};

/* @component */
export default ProductFilter;
