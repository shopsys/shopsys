# Unit tests

[TOC]

Storefront unit tests are written using the [Vitest](https://vitest.dev/) testing library. It has a very similar API to Jest, so anybody with a skill in Vitest or Jest should be able to write them with ease. However, in order to follow a specific guideline and a set of standards, below you can see a _cookbook_ that should help you write unit tests for this codebase.

## Snapshot tests

To test components and their rendered form, we use snapshot tests. These tests are powerful because of their simplicity and how easily they can discover basic bugs. They are also a great tool for regression testing. However, there are two main things which need to be handled correctly when working with snapshot tests. You can read more about them below.

### Multiple snapshots per file

By default, Vitest creates one snapshot per test file. This is sometimes not what you want. In order to change this behavior, you can define your own snapshot by using the `toMatchFileSnapshot` method, for which you can define the name of the snapshot file. This is how it can look in your test suites.

```tsx
describe('MyComponent snapshot tests', () => {
    test('render MyComponent with some props', () => {
        const component = render(<MyComponent ... />);

        expect(component).toMatchFileSnapshot('snap-1.test.tsx.snap');
    });

    test('render MyComponent with other props', () => {
        const component = render(<MyComponent ... />);

        expect(component).toMatchFileSnapshot('snap-2.test.tsx.snap');
    });
```

Also, keep in mind that Vitest generates its snapshot in a specific format. For this, the files need to have a specific type extension. As you might have noticed in the example above, the file extension is `.test.tsx.snap`.

### Updating outdated snapshot

If you change a component that is tested by snapshot tests, these should fail. This is a wanted behavior. However, once you check that your changes are, in fact, correct, you would want to update these snapshots in order to tell Vitest this is the new correct state of a given component. In order to do that, you can simply run the following command

```bash
pnpm test--update
```

## Config

Before diving deep into the cookbook for Storefront unit tests, there are some interesting vitest config options that should be explained.

You can read more about the config options in the [Vitest docs](https://vitest.dev/config/).

```js
export default defineConfig({
    // tsConfigPaths allows us to test our codebase which uses absolute imports based on the TS basePath
    plugins: [react(), tsconfigPaths()],
    test: {
        environment: 'jsdom',
        rootDir: './',
        // testMatch tells vitest where to search for tests
        testMatch: ['vitest/**/*.test.js'],
        // the two options below take care of clearing and preparing your mocks for every test
        clearMocks: true,
        restoreMocks: true,
    },
    resolve: {
        // these are the directories which are loaded for our tests
        // all directories which are included (even indirectly) in our tests should be added here
        moduleDirectories: [
            'node_modules',
            'components',
            'connectors',
            'graphql',
            'helpers',
            'hooks',
            'pages',
            'store',
            'styles',
            'typeHelpers',
            'types',
            'urql',
            'utils',
        ],
    },
});
```

## Cookbook

### The environment of this cookbook

In this cookbook, we will work with a couple of pseudo files, with which some common scenarios will be modeled. In the place of these files, you can put any module or a third-party library. The logic should be identical.

**File _foo.tsx_**

```tsx
export const getFoo = () => 'foo';
```

**File _bar.tsx_**

```tsx
import { getFoo } from './foo';

export const getBar = () => getFoo();
```

**File _partially-used-file.tsx_**

```tsx
/**
 * This file is only partially used in order to correctly
 * show how to mock this type of modules. The purpose of
 * this file will be evident later once mocking of partially
 * used files or modules will be explained.
 */
export const getFooBar = () => 'foobar';

export const EXPORTED_CONSTANT = {
    FOO: 'bar',
} as const;

export const UNUSED_CONSTANT = 'foobar';
```

**File _with-exported-variable.tsx_**

```tsx
import { EXPORTED_CONSTANT } from 'partially-used-file';

export const getExportedVariable = () => EXPORTED_CONSTANT.FOO;
```

**File _with-module.tsx_**

```tsx
/**
 * useSessionStore is used because it does not return
 * a value directly, but uses a selector. The implementation
 * of the useSessionStore function can change in time, but
 * for the purpose of this cookbook, it is enough if you imagine
 * any exported function that needs an anonymous function
 * (a selector) to work properly.
 */
import { useSessionStore } from 'store/useSessionStore';

export const useModuleValue = () => {
    const domainConfig = useSessionStore((s) => s.domainConfig);

    return domainConfig.currencyCode;
};
```

### How to mock different scenarios

Before showing different mocking scenarios, there are some common known pitfals with mocking which might be a problem for you as well. Below is a list with a simple solution:

- **If I test function foo and want to mock a function or constant bar from the same module, it will not work as needed** - If this is the case, you should find another way of testing, either by moving one of these files, or by avoiding the mock altogether (for example using IoC and parameters)

#### 1. Default mock of a function

This approach is helpful if you want to mock an exported function in a specific way which stays consistent across the file. If you want this mock function to return the same value for all your test suites inside this file, this is how you do it.

Later we will see how to modify this default behavior for a specific test.

```tsx
import { getBar } from './bar';
import { expect, test, vi } from 'vitest';

// default mock of a function
vi.mock('./foo', () => ({ getFoo: vi.fn(() => 'foo default mock') }));

// test uses default mock, does not need mock override
test('test using default function mock', () => {
    // as you can see above, the getBar function uses the getFoo function internally
    expect(getBar()).toBe('foo default mock');
});
```

#### 2. Overridden mock of a function

If, for some reason, there are tests which are not well-served by your default function mock, you can override it.

```tsx
import { getBar } from './bar';
// the mocked function now needs to be imported
import { getFoo } from './foo';
import { expect, Mock, test, vi } from 'vitest';

// default mock of a function
vi.mock('./foo', () => ({ getFoo: vi.fn(() => 'foo default mock') }));

// test uses modified behavior of the mock, needs mock override
test('test using overridden function mock', () => {
    // type assertion is needed to hack typescript and allow vitest methods
    (getFoo as Mock).mockImplementation(() => 'bar');
    expect(getBar()).toBe('bar');
});
```

#### 3. Default mock of a module

If you need to mock a module or an external package, you can do it the following way. However, keep in mind that by mocking it like this, you mock the entire behavior of the module. This means that if the module exports 3 functions and you only mock 1, the other 2 are unavailable in your tests. If this is not what you want, check out mocks of partially mocked modules below.

```tsx
import { useRouter } from 'next/router';
import { expect, Mock, test, vi } from 'vitest';

// default mock of the next/router module
vi.mock('next/router', () => ({
    // next/router now only contains the useRouter hook
    useRouter: vi.fn(() => ({
        // useRouter now only contains these two properties
        asPath: '/original',
        // your mocks can even have a different interface
        push: vi.fn(() => 'mock push'),
    })),
}));

test('test using default module mock', async () => {
    // type assertion is needed if the interface of the function changes
    expect((useRouter as Mock)().push()).toBe('mock push');
});
```

#### 4. Overridden mock of a module

Similar to the examples with exported functions, if you want to override the default mock, you can do it the following way.

```tsx
import { useRouter } from 'next/router';
import { expect, Mock, test, vi } from 'vitest';

// default mock of the next/router module
vi.mock('next/router', () => ({
    useRouter: vi.fn(() => ({
        asPath: '/original',
        push: vi.fn(() => 'mock push'),
    })),
}));

test('test using overridden module mock', () => {
    (useRouter as Mock).mockImplementation(() => ({
        asPath: '/overridden',
        push: vi.fn(() => 'overridden mock push'),
    }));

    expect(useRouter().asPath).toBe('/overridden');
    expect((useRouter as Mock)().push()).toBe('overridden mock push');
});
```

#### 5. Default mock of a function which uses an anonymous function (selector)

Some libraries, or even your own code, can export functions which need an anonymous function, a so-called selector, to correctly return a value. One of these examples is the useSessionStore hook which we use in the following manner:

```ts
const foo = useSessionStore((s) => s.foo);
```

These functions cannot be mocked as straightforwardly as the functions in the example above. Below, you can see an example of a mock which mocks such a function in the correct way.

```tsx
import { vi } from 'vitest';

vi.mock('store/useSessionStore', () => ({
    // selector is used when the mocked function accepts an anonymous function which then returns data
    useSessionStore: vi.fn((selector) => {
        return selector({
            domainConfig: {
                currencyCode: 'USD',
            },
        });
    }),
}));
```

#### 6. Default mock of a function which uses an anonymous function (selector)

As in all of the examples above, you can also override a mock of a function which uses a selector. Below, you can see an example of how to do so.

```tsx
import { expect, Mock, test, vi } from 'vitest';
import { useSessionStore } from 'store/useSessionStore';

vi.mock('store/useSessionStore', () => ({
    useSessionStore: vi.fn((selector) => {
        return selector({
            domainConfig: {
                currencyCode: 'USD',
            },
        });
    }),
}));

test('test using overridden module mock which is called in another file', () => {
    (useSessionStore as unknown as Mock).mockImplementation((selector) => {
        return selector({
            domainConfig: {
                currencyCode: 'CZK',
            },
        });
    });

    expect(useModuleValue()).toBe('CZK');
});
```

#### 7. Partial mock of a module

It is often the case that a module exports a wide range of functions and variables. These can then be used in your code and your tests. If the case is that you only want to mock a part of the module and keep the rest of the code intact, this cannot be done in a naive way. The correct way of partially mocking a module can be seen below.

```tsx
import { vi } from 'vitest';

// by storing this mock in a constant, it can be easily overridden in a specific test
const mockGetFooBar = vi.fn(() => 'default foobar mock');

vi.mock('./partially-used-file', async (importOriginal) => {
    const actualModuleContents = await importOriginal<any>();

    return {
        // the rest of the module stays in place, only the getFooBar method is mocked
        ...actualModuleContents,
        getFooBar: mockGetFooBar,
    };
});
```

#### 8. Default mock of an exported variable

If you want to mock a variable, not a function, you will still have to treat it as a function in a way. Specifically, you will not mock the variable itself, but its getter. This way, it can also be overridden in specific tests. However, if you do not care about the possibility of mock override in different tests, you can also do it in a simple way like this:

```ts
import { vi } from 'vitest';

vi.mock('./partially-used-file', () => ({
    EXPORTED_CONSTANT: {
        FOO: 'mocked bar',
    },
}));
```

But as mentioned above, the more robust way to do it is this:

```ts
import { vi } from 'vitest';

const mockExportedConstantGetter = vi.fn(() => ({ FOO: 'mocked bar' }));
vi.mock('./partially-used-file', () => ({
    get EXPORTED_CONSTANT() {
        return mockExportedConstantGetter;
    },
}));
```

#### 9. Overridden mock of an exported variable

With the approach from the previous example, we can easily override a getter of an exported variable for the needs of a specific test.

```tsx
import { vi } from 'vitest';
import { getExportedVariable } from './with-exported-variable';

const mockExportedConstantGetter = vi.fn(() => ({ FOO: 'mocked bar' }));
vi.mock('./partially-used-file', () => ({
    get EXPORTED_CONSTANT() {
        return mockExportedConstantGetter;
    },
}));

test('test using overridden mock of an exported variable', async () => {
    mockExportedConstantGetter.mockImplementation(() => ({
        FOO: 'overridden mocked bar',
    }));
    // this function gets the value from the exported variable
    expect(getExportedVariable()).toBe('overridden mocked bar');
});
```

#### 10. Testing asychronous hooks inside components

Sometimes you want to test asynchronous code in components using hooks (e.g. calling an API). In a case like that, there are a couple of things which will make your life easier.

```tsx
test('created client (and URQL) do not filter out Redis cache directive on the client (in component)', async () => {
    (isServer as Mock).mockImplementation(() => false);

    // You can define multiple components inside your tests, if you need nesting
    const UrqlWrapper: FC = ({ children }) => {
        const publicGraphqlEndpoint = TEST_URL;

        return (
            <Provider
                value={createClient({
                    // You can mock the t function like this (naively)
                    t: () => 'foo' as any,
                    ssrExchange: ssrExchange(),
                    publicGraphqlEndpoint,
                    redisClient: mockRedisClient,
                })}
            >
                {children}
            </Provider>
        );
    };

    const InnerComponentWithUrqlClient: FC = () => {
        useQuery({
            query: QUERY_OBJECT,
        });

        return null;
    };

    // Render is your friend once you want to run your component logic
    render(
        <UrqlWrapper>
            <InnerComponentWithUrqlClient />
        </UrqlWrapper>,
    );

    // waitFor from the React Testing Library allows you to wait for async events caused by code in your components
    await waitFor(() => {
        // Inside you can still expect using vitest
        expect(mockRequestWithFetcher).toBeCalledWith(
            'http://test.ts/graphql/',
            expect.objectContaining({ body: REQUEST_BODY }),
        );
    });
});
```

You also want to clean up after yourself and your async component tests, as otherwise it can affect other tests. To do it, you can use the cleanup function from the React Testing Library.

```tsx
describe('createClient test', () => {
    afterEach(cleanup);
    ...
    test( ...
```

## Advanced Testing Patterns for Complex Components

This section provides comprehensive guidance for implementing and maintaining test suites for complex interactive components like filters, sorting, and other user interface elements.

### The Minimal Mocking Philosophy

Our testing approach prioritizes **real component integration** over heavy mocking to ensure authentic user experience testing and catch integration issues that traditional mocking might miss.

#### Core Principles

**What to Mock (Essential Only):**

- **State Management**: Core state hooks that would require complex setup
- **External Dependencies**: Third-party libraries and APIs
- **Animation Libraries**: Performance-heavy animations (using `vi.importActual` pattern)
- **Browser APIs**: When testing in isolation from browser environment

**What NOT to Mock (Use Real Components):**

- **UI Components**: Buttons, inputs, dropdowns, checkboxes, sliders
- **Utility Functions**: Internal helpers and custom hooks
- **Icon Components**: SVG icons and visual elements
- **Native Browser APIs**: Focus management, keyboard events, form interactions

#### Implementation Patterns

```tsx
// ✅ Essential mocks only - state management
vi.mock('utils/queryParams/useUpdateFilterQuery', () => ({
    useUpdateFilterQuery: () => ({ updateFilterQuery: mockUpdateFunction }),
}));

// ✅ Partial mocking with vi.importActual
vi.mock('framer-motion', async () => {
    const actual = await vi.importActual('framer-motion');
    return {
        ...actual,
        AnimatePresence: ({ children }) => <>{children}</>,
        motion: {
            div: ({ children, ...props }) => <div {...props}>{children}</div>,
        },
    };
});

// ✅ Provider setup for real component testing
const TestWrapper = ({ children }) => (
    <DomainConfigProvider domainConfig={mockDomainConfig}>
        <AuthorizationProvider>{children}</AuthorizationProvider>
    </DomainConfigProvider>
);

// ✅ Test with real component selectors
const checkboxes = screen.getAllByRole('checkbox'); // Real DOM elements
const buttons = screen.getAllByRole('button'); // Actual component structure
```

### Testing Utilities Architecture

Our testing utilities are organized into three specialized modules for maximum reusability and maintainability:

#### Keyboard Navigation Utilities (`keyboard-navigation.ts`)

Essential functions for testing keyboard accessibility and navigation patterns:

```tsx
// Basic navigation actions
await navigateWithTab(targetElement);
await navigateWithArrows(elementArray, 'ArrowDown');
await pressEnterKey();
await pressSpaceKey();
await pressEscapeKey();

// Advanced navigation helpers
const element = await navigateToElement('button', 'Submit');
const input = await navigateToElementByLabel('Email Address');
await typeAndNavigate(input, 'test@example.com', 'Tab');

// Validation functions
assertElementHasFocus(element);
await testTabNavigation(expectedElements);
await testFormKeyboardNavigation(formElements);
```

#### Filter Testing Utilities (`filter-testing.ts`)

Specialized functions for testing filtering and search functionality:

```tsx
// Basic filter operations
await applyPriceFilter(minValue, maxValue);
await applyMultipleFilters({ price: { min: 100, max: 500 }, brands: ['Apple'] });
await resetAllFilters();

// Advanced filter testing
await applyPriceFilterWithSlider(200, 800);
await testSliderEdgeCases(minBoundary, maxBoundary);
await waitForFilterApplication();

// Performance and error testing
const duration = await measureFilterPerformance(filterAction);
await simulateFilterError('network');
```

#### Accessibility Testing Utilities (`a11y-testing.ts`)

Comprehensive accessibility validation functions:

```tsx
// Core accessibility tests
await testKeyboardNavigation(container);
testAriaLabels(container);
await testFocusManagement(triggerElement, expectedFocusElement);
testColorContrast(element);

// Advanced accessibility validation
await testTabOrder(expectedTabSequence);
testFormAccessibility(formElement);
await testModalAccessibility(openButton, modalElement);
await testDropdownAccessibility(trigger, menuItems);

// Complete test suite runner
const results = await runAccessibilityTestSuite(component);
```

### Comprehensive Accessibility Testing Patterns

Every interactive component should include thorough accessibility testing covering all interaction methods:

#### Keyboard Navigation Requirements

```tsx
describe('Component accessibility', () => {
    test('should support complete keyboard navigation', async () => {
        renderComponent();

        // Tab navigation
        const focusableElements = getFocusableElements();
        for (const element of focusableElements) {
            await navigateWithTab(element);
            expect(element).toHaveFocus();
        }

        // Arrow key navigation (for grouped elements)
        const checkboxes = screen.getAllByRole('checkbox');
        await navigateWithArrows(checkboxes, 'ArrowDown');

        // Action keys
        await pressSpaceKey(); // Toggle selection
        await pressEnterKey(); // Activate buttons
        await pressEscapeKey(); // Cancel/close operations
    });
});
```

#### ARIA and Screen Reader Support

```tsx
test('should provide proper ARIA labels and announcements', () => {
    renderComponent();

    // Validate ARIA attributes
    testAriaLabels();

    // Check specific accessibility attributes
    const filterGroup = screen.getByRole('group');
    expect(filterGroup).toHaveAttribute('aria-label');

    const checkboxes = screen.getAllByRole('checkbox');
    checkboxes.forEach((checkbox) => {
        expect(checkbox).toHaveAccessibleName();
    });
});
```

#### Focus Management Validation

```tsx
test('should manage focus properly during interactions', async () => {
    renderComponent();

    const trigger = screen.getByRole('button', { name: 'Open Filter' });
    const firstOption = screen.getByRole('checkbox', { name: 'First Option' });

    // Test focus movement
    await testFocusManagement(trigger, firstOption);

    // Test focus restoration
    await pressEscapeKey();
    expect(trigger).toHaveFocus();
});
```

### Component-Specific Testing Patterns

#### Filter Component Testing

```tsx
describe('FilterGroupGeneric', () => {
    test('should handle checkbox selection workflow', async () => {
        renderComponent();

        const checkboxes = screen.getAllByRole('checkbox');

        // Multi-selection scenario
        await pressSpaceKey(checkboxes[0]);
        await pressSpaceKey(checkboxes[1]);

        expect(checkboxes[0]).toBeChecked();
        expect(checkboxes[1]).toBeChecked();

        // Show more/less functionality
        const showMoreButton = screen.getByRole('button', { name: /show more/i });
        await pressEnterKey(showMoreButton);

        expect(screen.getAllByRole('checkbox')).toHaveLength(
            expect.any(Number), // Validate expanded list
        );
    });
});
```

#### Slider Component Testing

```tsx
describe('RangeSlider', () => {
    test('should support keyboard value adjustment', async () => {
        renderComponent();

        const minInput = screen.getByLabelText('Minimum value');
        const maxInput = screen.getByLabelText('Maximum value');

        // Type and navigate workflow
        await typeAndNavigate(minInput, '100', 'Tab');
        await typeAndNavigate(maxInput, '500', 'Enter');

        expect(minInput).toHaveValue(100);
        expect(maxInput).toHaveValue(500);
    });
});
```

#### Dropdown Component Testing

```tsx
describe('SortingBar', () => {
    test('should provide complete keyboard dropdown navigation', async () => {
        renderComponent();

        const trigger = screen.getByRole('button');

        // Open dropdown
        await pressEnterKey(trigger);

        const options = screen.getAllByRole('link');

        // Navigate options
        await navigateWithArrows(options, 'ArrowDown');
        expect(options[1]).toHaveFocus();

        // Select option
        await pressEnterKey();

        expect(mockUpdateSortQuery).toHaveBeenCalled();
    });
});
```

### Testing Best Practices and Standards

#### Selector Strategy

- **Prefer semantic selectors**: `screen.getByRole('button')`, `screen.getByLabelText()`
- **Avoid test IDs**: Use `data-testid` only when semantic selectors are insufficient
- **User-centric approach**: Test how users interact with components, not implementation details

#### Test Organization

```tsx
describe('ComponentName', () => {
    // Setup and cleanup
    beforeEach(() => {
        vi.clearAllMocks();
    });

    afterEach(() => {
        cleanup();
    });

    describe('Basic functionality', () => {
        // Core feature tests
    });

    describe('Accessibility', () => {
        // Keyboard navigation and ARIA tests
    });

    describe('Performance', () => {
        // Large dataset and performance tests
    });

    describe('Integration', () => {
        // Cross-component and state tests
    });

    describe('Edge cases', () => {
        // Error conditions and boundary tests
    });
});
```

#### Mock Data Strategy

Create realistic, reusable mock data that reflects production scenarios:

```tsx
const mockFilterData = {
    brands: [
        { uuid: 'brand-1', name: 'Apple', count: 25 },
        { uuid: 'brand-2', name: 'Samsung', count: 18 },
        // ... realistic dataset
    ],
    parameters: [
        { uuid: 'param-1', name: 'Color', type: 'color', values: [...] },
        { uuid: 'param-2', name: 'Size', type: 'checkbox', values: [...] },
        // ... various parameter types
    ],
};
```

This comprehensive testing approach ensures robust, accessible, and maintainable components while providing clear patterns for implementing new test suites efficiently.
