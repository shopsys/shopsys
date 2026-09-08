import { TypeProductInProductListFragment } from 'graphql/requests/productLists/fragments/ProductInProductListFragment.generated';

export type ComparisonParameter = {
    uuid: string;
    name: string;
    group: string | null;
    unit: string | null;
    values: (string[] | null)[];
    isDifferent: boolean;
};

export const getComparisonParameters = (
    products: TypeProductInProductListFragment[],
    parameterSourceProducts: TypeProductInProductListFragment[] = products,
): ComparisonParameter[] => {
    const visibleParameterUuids = new Set(
        products.flatMap((product) => product.parameters.map((parameter) => parameter.uuid)),
    );
    const parameters = new Map<string, TypeProductInProductListFragment['parameters'][number]>();
    parameterSourceProducts.forEach((product) => {
        product.parameters.forEach((parameter) => {
            if (visibleParameterUuids.has(parameter.uuid)) {
                parameters.set(parameter.uuid, parameter);
            }
        });
    });

    return Array.from(parameters.values()).map((parameter) => {
        const values = products.map((product) => {
            const match = product.parameters.find((item) => item.uuid === parameter.uuid);
            return match?.values.length ? match.values.map((value) => value.text) : null;
        });
        const comparableValues = values.map((value) =>
            value === null ? null : JSON.stringify(Array.from(new Set(value)).sort()),
        );

        return {
            uuid: parameter.uuid,
            name: parameter.name,
            group: parameter.group,
            unit: parameter.unit?.name ?? null,
            values,
            isDifferent: new Set(comparableValues).size > 1,
        };
    });
};
