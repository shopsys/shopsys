import fileWalker from './fileWalker';

test('fileWalker test', done => {
    const mockCallback = (err, data) => {
        expect(err).toBeNull();
        expect(data).toHaveLength(2);
        done();
    };

    fileWalker('./assets/js/commands/translations/mocks/*.js', mockCallback, false);
});
