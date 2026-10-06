# Npm and Rsbuild

## Introduction to npm, Rsbuild and Symfony Reprise

A [npm](https://www.npmjs.com/) is a package manager it allows you to install javascripts packages from other developers and to eventually publish your own packages.

A [Rsbuild](https://rsbuild.rs/) is a bundler for javascript and friends.
Packs many modules into a few bundled assets and allows Code Splitting for loading parts of the application on demand.

A [Symfony Reprise](https://github.com/symfony/reprise) is the Symfony integration layer for modern bundlers - it generates the `entrypoints.json` and `manifest.json` files that the `reprise_entry_*` Twig functions read, and points the templates to the running dev server during development.

## What do we use them for?

We use npm to manage and install frontend packages.

To compile the code into something the browser understands we bundle the code through Rsbuild.
These build/compile operations are provided as npm script to make them easy to run.

We configure the bundler in `rsbuild.config.ts`, where the Symfony Reprise plugin takes care of the Symfony integration.

## How do we use them?

When working with javascripts and friends packages you should have a `package.json` file in the root directory of your project.
This file declares all required dependencies to run your package (in PHP, this is similar to a `composer.json` file).

To install all dependencies you should run `npm install` in the root directory of your project.
This installs all third party dependencies in the `/node_modules` directory (in PHP, this is similar to running composer).
Every time you pull down new code you should ensure that all dependencies are installed or you will get errors such as `Error Cannot find module 'foo'` when you try to build javascript files.
Phing target `npm` downloads packages and run build script.
To add a new library, use the `npm install` command (for example, `npm install counterup2`).

Once a dependency is installed you can use it in a JS file in your application.
For example, if you install `counterup2` you can import and use it:

```js
// assets/js/admin/components/CounterUp.js
import counterUp from 'counterup2';
// ...

export default class CounterUp {
    static init() {
        document.querySelectorAll('.js-counter').forEach((counterItem) => {
            counterUp(counterItem, {
                duration: 1000,
                delay: 10,
            });
        });
    }
}

// ...
```

When compiling your application the process is clever enough to understand when a dependency has already been imported from a different file - meaning that everything is ultimately only ever imported once.
However, you should import dependencies into each file to ensure that that particular file will work independently.

If you want to add a new component that will listen to a certain event (for example), you have to import the component in the main file `assets/js/admin/admin.js`.
The addition works just like a component installed over npm except that relative paths are used.

```js
// assets/js/admin/admin.js
import './components/CounterUp';
// ...
```

When we are editing a javascript and friends files, the change must go through the bundler (Rsbuild).
All javascript and friends files are built using the `npm run build` command.
But it would be impractical if we had to run a command in the console with every change.
Therefore we can use `npm run watch` for development.
This command starts the Rsbuild dev server and Reprise points the templates to it automatically.
Changed styles are swapped in the browser in place, and a change in JavaScript reloads the page automatically.
The assets are served by the dev server only, so after you stop it, run `npm run dev` or `npm run build` to get the assets back.

To build the assets once without minification and with source maps (e.g. for debugging), run `npm run dev`.

## Translations

Translations are exported by the `npm` phing target before the build; the plain `npm run watch` does not re-export them.
You can manually generate translations using the `npm run trans` command. The resulting json translation file is created in the `assets/js/translations.json` and the administration works with this json file.
How to work with translation you can read [translation](../introduction/translations.md) article.

## Some use cases

### I want to edit existing javascripts

- you have to run `npm run watch` in the project root. You can run it in docker or locally (when you have installed npm)
- you can edit files
- (you may notice changes in the console)
- you can see the changes in the browser (the page reloads automatically)

### I want to add new javascript file to admin

- you have to run `npm run watch` in the project root. You can run it in docker or locally (when you have installed npm)
- you can create new javascript file (path of new file is `assets/js/admin/myNewFile.js`)
- you can use this new file in some other file (`import ./admin/myNewFile.js`)
- or, when file contains global event listener, import new file in `assets/js/admin/admin.js` (`import ./myNewFile.js`)

### I want to add new package from npm repository

- you have to stop `npm run watch` (if it is running)
- you can add package via npm `npm install <package-name>`
- you have to run `npm run watch` in the project root. You can run it in docker or locally (when you have installed npm)
- you can use new package (`import <package-name>`) in some file
- you can see the changes in the browser (the page reloads automatically)

### I want to override method from @shopsys/framework package

For example, we can override method `onChange` of the `CategoryTreeSorting` class from `@shopsys/framework/js/admin/components/CategoryTreeSorting.js`.

- you have to run `npm run watch` in the project root. You can run it in docker or locally (when you have installed npm)
- you have to import `CategoryTreeSorting` in `assets/js/admin/admin.js`

```js
import CategoryTreeSorting from 'framework/admin/components/CategoryTreeSorting';
// ...
```

- you can prepare new method

```js
const myOverriddenOnChange = function () {
    console.log('Hello my overridden onChange method.');
};
```

- you have to replace the original method with the new one

```js
CategoryTreeSorting.prototype.onChange = myOverriddenOnChange;
```

- you can see the changes in the browser (the page reloads automatically)

Full example might look like this:

```js
import CategoryTreeSorting from 'framework/admin/components/CategoryTreeSorting';

const myOverriddenOnChange = function () {
    console.log('Hello my overridden onChange method.');
};

CategoryTreeSorting.prototype.onChange = myOverriddenOnChange;
```

This principle is called [Monkey Patching](https://www.sitepoint.com/pragmatic-monkey-patching/).

### I want to override class from @shopsys/framework package

You can use ES6 syntax to override class.
You certainly know key word `extend`.
You can use it in javascript's world now.

```js
import CategoryTreeSorting from 'framework/admin/components/CategoryTreeSorting';
import Register from 'framework/common/utils/Register';

class MyCategoryTreeSorting extends CategoryTreeSorting {
    constructor($rootTree, $saveButton) {
        super($rootTree, $saveButton);
        console.log('override constructor');
    }
    onChange() {
        super.onChange();
        console.log('on change');
    }
    static init($container) {
        const $rootTree = $container.filterAllNodes('#js-category-tree-sorting > .js-category-tree-items');
        const $saveButton = $container.filterAllNodes('#js-category-tree-sorting-save-button');
        if ($rootTree.length > 0 && $saveButton.length > 0) {
            // eslint-disable-next-line no-new
            new MyCategoryTreeSorting($rootTree, $saveButton);
        }
    }
}
```

If you override framework's javascript class, you will have to change registered callback of the original class init to your implementation.
This is because js doesn't have global container that known that we overridden the original class.

Register new callback may look like this:

```js
new Register().replaceCallback('CategoryTreeSorting.init', MyCategoryTreeSorting.init);
```

You can remove registered callback using `removeCallback` method.

```js
new Register().removeCallback('CategoryTreeSorting.init');
```
