import Translator from 'bazinga-translator';

class DummyTranslations {
    constructor () {
        Translator.trans('trans in constructor');
        Translator.transChoice('{0}transChoice in constructor{1}transChoice in constructor');
    }

    method () {
        Translator.trans('trans in method');
        Translator.transChoice('{0}transChoice in method{1}transChoice in method', 0);

        const callback = () => {
            Translator.trans('trans in callback in method');
            Translator.transChoice('{0}transChoice in callback in method{1}transChoice in callback in method', 0);
        };

        callback();
    }

    static staticMethod () {
        Translator.trans('trans in static method');
        Translator.transChoice('{0}transChoice in static method{1}transChoice in static method', 0);

        const callback = () => {
            Translator.trans('trans in callback in static method');
            Translator.transChoice('{0}transChoice in callback in static method{1}transChoice in callback in static method', 0);
        };

        callback();
    }
}

Translator.trans('trans out of class');
Translator.transChoice('{0}transChoice out of class{1}transChoice out of class', 0);

const simpleFunction = () => {
    Translator.trans('trans in simple function');
    Translator.transChoice('{0}transChoice in simple function{1}transChoice in simple function', 0);
};

simpleFunction();

// eslint-disable-next-line no-new
new DummyTranslations();
