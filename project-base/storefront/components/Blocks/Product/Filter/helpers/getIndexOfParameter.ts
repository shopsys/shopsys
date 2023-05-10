import { ParametersType } from 'types/productFilter';

export const getIndexOfParameter = (parametersValue: ParametersType[], parameterUuid: string): number =>
    parametersValue.findIndex((item) => item.uuid === parameterUuid);
