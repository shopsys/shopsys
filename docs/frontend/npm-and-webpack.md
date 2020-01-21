# Npm and webpack

## Introduction to npm, webpack and webpack encore

A [npm](https://www.npmjs.com/) is a package manager it allows you to install javascripts packages from other developers and to eventually publish your own packages.

A [webpack](https://webpack.js.org/) is a bundler for javascript and friends.
Packs many modules into a few bundled assets and allows Code Splitting for loading parts of the application on demand.

A [webpack encore](https://github.com/symfony/webpack-encore) is powerful API for processing & compiling assets built around Webpack.

## What do we use them for?

We use npm to manage and install frontend packages.
We use npm also for maintain common scripts at [@shopsys/framework](https://www.npmjs.com/org/shopsys).

To compile the code into something the browser understands we bundle the code through Webpack.
These build/compile operations are provided as npm script to make them easy to run.

We configure a webpack with a webpack encore.

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
// assets/js/frontend/components/counterUpInit.js
import counterUp from 'counterup2';

export default function counterUpInit () {
    document.querySelectorAll('.js-counter').forEach(counterItem => {
        counterUp(counterItem, {
            // ...
        });
    });
}
```

When compiling your application the process is clever enough to understand when a dependency has already been imported from a different file - meaning that everything is ultimately only ever imported once.
However, you should import dependencies into each file to ensure that that particular file will work independently.

If you want to add a new component that will listen to a certain event (for example), you have to import the component in the main file.
For frontend, this is the `assets/js/frontend/frontend.js` file, for the administration is the `assets/js/admin/admin.js` file.
The addition works just like a component installed over npm except that relative paths are used.

```js
    // assets/js/frontend/frontend.js
    import './components/counterUpInit';
    // ...
```

When we are editing a javascript and friends files, the change must go through the bundler (webpack).
All javascript and friends files are built using the `npm run build` command.
But it would be impractical if we had to run a command in the console with every change.
Therefore we can use `npm run watch` for development.
This command checks if a file has changed and if it does, changes are propagated into the resulting bundle.
The `npm run watch` command launches the webpack in development mode, which means creating source maps to help you debug your project.

## Constants and translations

In previous versions, the constants were automatically replaced from the backend to the frontend.
This feature has been removed.
Used constants have been moved to utils `assets/js/js/utils/constants.js`.
It is up to you whether you have constants in this file or in individual files.
We think that synchronization of frontend and backend constants is not necessary, but this point can be reopened in the future.

By contrast, translations are included in the watch command, and with every change in the js file, the webpack finds the appropriate translations.
You can manually generate translations using the `npm run trans` command. The resulting json translation file is created in the `assets/js/translations.json` and frontend works with this json file.
How to work with translation you can read [translation](../introduction/translations.md) article.

## Some use cases

### I want to edit existing javascripts

- you have to run `npm run watch` in the project root. You can run it in docker or locally (when you have installed npm)
- you can edit files
- (you may notice changes in the console)
- you can test changes (after page reload)

### I want to add new javascript file to frontend

- you have to run `npm run watch` in the project root. You can run it in docker or locally (when you have installed npm)
- you can create new javascript file (path of new file is `assets/js/frontend/myNewFile.js`)
- you can use this new file in some other file (`import ./frontend/myNewFile.js`)
- or, when file contains global event listener, import new file in `assets/js/frontend/frontend.js` (`import ./myNewFile.js`)

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
- you can test changes (after page reload)

### I want to override function from @shopsys/framework common package

For example, we can override method `showFormErrorsWindowOnFrontend` from `@shopsys/framework/common/validation/customizeBundle.js` on frontend.

- you have to run `npm run watch` in the project root. You can run it in docker or locally (when you have installed npm)
- you have to import `CustomizeBundle` in `assets/js/frontend/frontend.js`
```js
import CustomizeBundle from 'framework/common/validation/customizeBundle';
// ...
```
- you can prepare new function
```js
const myOverridedShowFormErrorsWindow = (container) => {
    console.log('Hello my overrided showFormErrorsWindow method.');
}
```
- you have to replace the original method with the new one
```js
CustomizeBundle.showFormErrorsWindow = myOverridedShowFormErrorsWindow;
```
- you can test changes (after page reload)

Full example might look like this:
```js
import CustomizeBundle from 'framework/common/validation/customizeBundle';

const myOverridedShowFormErrorsWindow = (container) => {
    console.log('Hello my overrided showFormErrorsWindow method.');
}

CustomizeBundle.showFormErrorsWindow = myOverridedShowFormErrorsWindow;
```

This principle is called [Monkey Patching](https://www.sitepoint.com/pragmatic-monkey-patching/).
