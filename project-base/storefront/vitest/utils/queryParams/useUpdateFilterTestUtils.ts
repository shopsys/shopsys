import { TypeProductOrderingModeEnum } from 'graphql/types';
import type { Mock } from 'vitest';

type ResetUseUpdateFilterTestMocksOptions = {
    mockSeoSensitiveFiltersGetter: Mock;
    useRouterMock: Mock;
    useSessionStoreMock: Mock;
    mockPush: Mock;
    categoryPathname: string;
    categoryUrl: string;
};

export const resetUseUpdateFilterTestMocks = ({
    mockSeoSensitiveFiltersGetter,
    useRouterMock,
    useSessionStoreMock,
    mockPush,
    categoryPathname,
    categoryUrl,
}: ResetUseUpdateFilterTestMocksOptions) => {
    mockSeoSensitiveFiltersGetter.mockImplementation(() => ({
        SORT: true,
        AVAILABILITY: false,
        PRICE: false,
        FLAGS: true,
        PARAMETERS: {
            CHECKBOX: true,
            SLIDER: false,
        },
    }));
    useRouterMock.mockImplementation(() => ({
        pathname: categoryPathname,
        asPath: categoryUrl,
        push: mockPush,
        query: {},
    }));
    useSessionStoreMock.mockImplementation((selector) => {
        return selector({
            defaultProductFiltersMap: {
                flags: new Set(),
                sort: TypeProductOrderingModeEnum.Priority,
                parameters: new Map(),
            },
            originalCategorySlug: null,
        });
    });
};
