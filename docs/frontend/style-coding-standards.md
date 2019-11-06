# Style Coding Standards
In order to keep the styles unified, we use automated tools for checking coding standards. You can find set of rules in .stylelintrc file at project base.

## Editor plugins
You can find set of editor plugins described at
[stylelint.io](https://stylelint.io/user-guide/complementary-tools#editor-plugins).

## Standards check and autofix
If you will install plugin to your editor, it will automatically warn you during coding if something is wrong.

In gruntfile.js there is new watch task, which checks all not generated files.
``` sh
grunt standards
```

If you want to automatically fix all your files with our rules, go to command line and run:

``` sh
npm install stylelint --save -dev
stylelint /styles/**/*.less --fix
```
