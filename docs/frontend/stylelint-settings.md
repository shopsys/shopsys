# StyleLint Settings
This document describes stylelint setting according linter [stylelint.io](https://stylelint.io/).

## Editor plugins
You can try to find stylelint plugin in your editor package manager. For more info please [visit stylelint.io user guide](https://stylelint.io/user-guide/complementary-tools#editor-plugins)

## Check and fix all files
If you want to fix all files in your command line, you can use this command:

``` sh
php phing stylelint-fix
```


## Used stylelint rules

All rules are defined at `.stylelintrc`:

- [block-no-empty](https://stylelint.io/user-guide/rules/block-no-empty) : true
- [block-closing-brace-empty-line-before](https://stylelint.io/user-guide/rules/block-closing-brace-empty-line-before) : "never"
- [block-opening-brace-space-before](https://stylelint.io/user-guide/rules/block-closing-brace-empty-line-before) : "always"
- [color-no-invalid-hex](https://stylelint.io/user-guide/rules/color-no-invalid-hex) : true
- [color-hex-case](https://stylelint.io/user-guide/rules/color-hex-case) : "lower"
- [comment-empty-line-before](https://stylelint.io/user-guide/rules/comment-empty-line-before) : "always"
- [declaration-colon-space-after](https://stylelint.io/user-guide/rules/declaration-colon-space-after) : "always"
- [declaration-block-no-duplicate-properties](https://stylelint.io/user-guide/rules/declaration-block-no-duplicate-properties) : true
- [function-calc-no-unspaced-operator](https://stylelint.io/user-guide/rules/function-calc-no-unspaced-operator) : true
- [indentation](https://stylelint.io/user-guide/rules/indentation) : 4
- [length-zero-no-unit](https://stylelint.io/user-guide/rules/length-zero-no-unit) : true
- [max-empty-lines](https://stylelint.io/user-guide/rules/max-empty-lines) : 2
- [media-feature-name-case](https://stylelint.io/user-guide/rules/media-feature-name-case) : "lower"
- [no-extra-semicolons](https://stylelint.io/user-guide/rules/no-extra-semicolons) : true
- [property-blacklist](https://stylelint.io/user-guide/rules/property-blacklist) : [ "widht", "marign", "pading", "blcok" ]
- [property-case](https://stylelint.io/user-guide/rules/property-case) : "lower"
- [rule-empty-line-before](https://stylelint.io/user-guide/rules/rule-empty-line-before) : "always"
- [unit-case](https://stylelint.io/user-guide/rules/unit-case) : "lower"
- [unit-whitelist](https://stylelint.io/user-guide/rules/unit-whitelist) : ["em", "rem", "%", "px", "s", "ms", "deg", "vh"]

## Excluded files
You can define and exclude unneeded files at `.stylelintignore` file. By default we exclude generated and third party plugin files. These files are excluded during fixing standards in command line.
