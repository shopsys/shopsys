import { FilterFormParameterType } from 'types/productFilter';

export const getIndexOfParameter = (parametersValue: FilterFormParameterType[], parameterUuid: string): number =>
    parametersValue.findIndex((item) => item.parameterUuid === parameterUuid);
