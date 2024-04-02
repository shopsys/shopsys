import { ParametersType } from 'types/productFilter';

export const getIndexOfParameterValue = (
    parametersValue: ParametersType[],
    indexOfParameter: number,
    parameterValueUuid: string,
): number => {
    const parameter: ParametersType | undefined = parametersValue[indexOfParameter];

    // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition
    return parameter !== undefined && 'values' in parameter
        ? parameter.values.findIndex((item) => item.uuid === parameterValueUuid)
        : -1;
};
