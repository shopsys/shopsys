import { FilterActions, FilterCallbacks, FilterState } from './types';
import produce from 'immer';
import { Reducer } from 'react';

const setMinimalPrice = (state: FilterState, payload: FilterCallbacks['setMinimalPrice']): FilterState => {
    return produce(state, (draft) => {
        draft.selected.minimalPrice = payload;
    });
};

const setMaximalPrice = (state: FilterState, payload: FilterCallbacks['setMaximalPrice']): FilterState => {
    return produce(state, (draft) => {
        draft.selected.maximalPrice = payload;
    });
};

const setOnlyInStock = (state: FilterState, payload: FilterCallbacks['setOnlyInStock']): FilterState => {
    return produce(state, (draft) => {
        draft.selected.onlyInStock = payload;
    });
};

const setBrands = (state: FilterState, payload: FilterCallbacks['setBrands']): FilterState => {
    const { value, index } = payload;
    return produce(state, (draft) => {
        draft.selected.brands[index].checked = value.checked;
    });
};

const setFlags = (state: FilterState, payload: FilterCallbacks['setFlags']): FilterState => {
    const { value, index } = payload;
    return produce(state, (draft) => {
        draft.selected.flags[index].checked = value.checked;
    });
};

const setParameter = (state: FilterState, payload: FilterCallbacks['setParameter']): FilterState => {
    const { value, parameterIndex, valueIndex } = payload;
    return produce(state, (draft) => {
        draft.selected.parameters[parameterIndex].values[valueIndex].checked = value.checked;
    });
};

const setSliderParameter = (state: FilterState, payload: FilterCallbacks['setSliderParameter']): FilterState => {
    const { value, type, index } = payload;
    return produce(state, (draft) => {
        draft.selected.parameters[index][type] = value;
    });
};

const uncheckBrand = (state: FilterState, payload: FilterCallbacks['uncheckBrand']): FilterState => {
    return produce(state, (draft) => {
        const index = state.options.brands.findIndex((i) => i.brand.uuid === payload);
        if (index !== -1) {
            draft.selected.brands[index].checked = false;
        }
    });
};

const uncheckFlag = (state: FilterState, payload: FilterCallbacks['uncheckFlag']): FilterState => {
    return produce(state, (draft) => {
        const index = state.options.flags.findIndex((i) => i.flag.uuid === payload);
        if (index !== -1) {
            draft.selected.flags[index].checked = false;
        }
    });
};

const uncheckParameter = (state: FilterState, payload: FilterCallbacks['uncheckParameter']): FilterState => {
    const { uuid, valueUuid } = payload;
    return produce(state, (draft) => {
        const indexOfParameter = state.options.parameters?.findIndex((i) => i.uuid === uuid);
        if (indexOfParameter !== undefined && indexOfParameter !== -1) {
            const parameters = state.options.parameters;
            if (parameters) {
                const parameter = parameters[indexOfParameter];
                const indexOfValue =
                    'values' in parameter ? parameter.values.findIndex((i) => i.uuid === valueUuid) : -1;
                if (indexOfValue !== -1) {
                    draft.selected.parameters[indexOfParameter].values[indexOfValue].checked = false;
                }
            }
        }
    });
};

const uncheckSliderParameter = (
    state: FilterState,
    payload: FilterCallbacks['uncheckSliderParameter'],
): FilterState => {
    return produce(state, (draft) => {
        const index = state.options.parameters?.findIndex((i) => i.uuid === payload);
        if (index !== undefined && index !== -1) {
            draft.selected.parameters[index].minimalValue = null;
            draft.selected.parameters[index].maximalValue = null;
        }
    });
};

const resetPrices = (state: FilterState): FilterState => {
    return produce(state, (draft) => {
        draft.selected.minimalPrice = state.options.minimalPrice;
        draft.selected.maximalPrice = state.options.maximalPrice;
    });
};

const resetAllParameters = (state: FilterState): FilterState => {
    return produce(state, (draft) => {
        draft.selected.minimalPrice = state.options.minimalPrice;
        draft.selected.maximalPrice = state.options.maximalPrice;
        draft.selected.onlyInStock = false;
        draft.selected.brands = state.options.brands.map((i) => ({ ...i.brand, checked: false }));
        draft.selected.flags = state.options.flags.map((i) => ({ ...i.flag, checked: false }));
        draft.selected.parameters =
            state.options.parameters?.map((item) => ({
                parameterName: item.name,
                selectedValue: 'selectedValue' in item ? item.selectedValue : null,
                parameterUuid: item.uuid,
                unit: 'unit' in item ? item.unit : null,
                isCollapsed: item.isCollapsed,
                minimalValue: null,
                maximalValue: null,
                values:
                    'values' in item
                        ? item.values.map((value) => ({
                              ...value,
                              rgbHex: 'rgbHex' in value ? value.rgbHex : null,
                              unit: null,
                              checked: false,
                          }))
                        : [],
            })) ?? [];
    });
};

const setFilterOptions = (state: FilterState, payload: FilterCallbacks['setFilterOptions']): FilterState => {
    return produce(state, (draft) => {
        draft.options = payload;
    });
};

export const filterReducer: Reducer<FilterState, FilterActions> = (state, action): FilterState => {
    switch (action.type) {
        case 'setMinimalPrice':
            return setMinimalPrice(state, action.payload);
        case 'setMaximalPrice':
            return setMaximalPrice(state, action.payload);
        case 'setOnlyInStock':
            return setOnlyInStock(state, action.payload);
        case 'setBrands':
            return setBrands(state, action.payload);
        case 'setFlags':
            return setFlags(state, action.payload);
        case 'setParameter':
            return setParameter(state, action.payload);
        case 'setSliderParameter':
            return setSliderParameter(state, action.payload);
        case 'uncheckBrand':
            return uncheckBrand(state, action.payload);
        case 'uncheckFlag':
            return uncheckFlag(state, action.payload);
        case 'uncheckParameter':
            return uncheckParameter(state, action.payload);
        case 'uncheckSliderParameter':
            return uncheckSliderParameter(state, action.payload);
        case 'resetPrices':
            return resetPrices(state);
        case 'resetAllParameters':
            return resetAllParameters(state);
        case 'setFilterOptions':
            return setFilterOptions(state, action.payload);
        default:
            return state;
    }
};
