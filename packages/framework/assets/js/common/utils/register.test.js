import Register from './register';

const mockCallback = jest.fn();

const register = new Register();

test.each([
    [mockCallback, 'name', 300, { callbackName: 'name', callPriority: 300, callback: mockCallback }],
    [mockCallback, 300, 'name', { callbackName: 'name', callPriority: 300, callback: mockCallback }],
    [mockCallback, null, null, { callbackName: null, callPriority: 500, callback: mockCallback }],
    [mockCallback, 300, null, { callbackName: null, callPriority: 300, callback: mockCallback }],
    [mockCallback, undefined, undefined, { callbackName: null, callPriority: 500, callback: mockCallback }],
    [mockCallback, 300, undefined, { callbackName: null, callPriority: 300, callback: mockCallback }],
    [mockCallback, () => {}, () => {}, { callbackName: null, callPriority: 500, callback: mockCallback }],
    [mockCallback, 300, () => {}, { callbackName: null, callPriority: 300, callback: mockCallback }]
])(
    'registerCallback correct parse parameters',
    (callback, arg1, arg2, expected) => {
        register.registerCallback(callback, arg1, arg2);
        expect(register.callbackQueue[0]).toStrictEqual(expected);
        register.callbackQueue = [];
    }
);

test('registerCallback correct parse parameters without parameters', () => {
    register.registerCallback(mockCallback);
    expect(register.callbackQueue[0]).toStrictEqual({ callbackName: null, callPriority: 500, callback: mockCallback });
    register.callbackQueue = [];
});

test('registerCallback correct parse parameters with first parameter is priority', () => {
    register.registerCallback(mockCallback, 300);
    expect(register.callbackQueue[0]).toStrictEqual({ callbackName: null, callPriority: 300, callback: mockCallback });
    register.callbackQueue = [];
});

test('registerCallback correct parse parameters with first parameter is callback name', () => {
    register.registerCallback(mockCallback, 'name');
    expect(register.callbackQueue[0]).toStrictEqual({ callbackName: 'name', callPriority: 500, callback: mockCallback });
    register.callbackQueue = [];
});

test('registerCallback callback is called', () => {
    register.registerCallback(mockCallback, 'name');
    register.registerNewContent(null);
    expect(mockCallback.mock.calls.length).toBe(1);
    register.callbackQueue = [];
});
