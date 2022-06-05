import { FilterFormParameterType } from 'types/productFilter';

export const getIndexOfParameterValue = (
    parametersValue: FilterFormParameterType[],
    indexOfParameter: number,
    parameterValueUuid: string,
): number => parametersValue[indexOfParameter]?.values.findIndex((item) => item.uuid === parameterValueUuid);
