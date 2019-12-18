var Translator = require('bazinga-translator');

Translator.trans('trans test');

Translator.transChoice('transChoice test', 5);

Translator.trans('trans test with domain', {}, 'testDomain');

Translator.transChoice('transChoice test with domain', 5, [], 'testDomain');

Translator.trans('concatenated' + ' ' + 'message');
