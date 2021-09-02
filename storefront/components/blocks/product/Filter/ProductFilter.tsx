import { FormProvider, useForm } from 'react-hook-form';
import ProductFilterGroup from './ProductFilterGroup';
import { ProductFilterStyled } from './ProductFilter.style';
import { ReactElement } from 'react';

const ProductFilter = (): ReactElement => {
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
        },
        criteriaMode: 'firstError',
        shouldFocusError: true,
    });

    return (
        <>
            <ProductFilterStyled>
                <FormProvider {...formProviderMethods}>
                    <ProductFilterGroup title="Cena" type="price" />
                    <ProductFilterGroup title="Dostupnost" type="checkbox" />
                    <ProductFilterGroup title="Příznaky" type="checkbox" />
                    <ProductFilterGroup title="Barva" type="color" />
                </FormProvider>
            </ProductFilterStyled>
        </>
    );
};

/* @component */
export default ProductFilter;
