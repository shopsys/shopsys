# Indtroduction to LESS
This document serves for describing specific behavior of CSS pre-processors LESS.

## Import files
In Shopsys framework we implement LESS by dividing styles into many LESS components. Every component has its own file. File name is same as component name. Getting all these components into one CSS file we accomplish by `@import` command.

In example bellow we can see how looks syntax of this command:

```css
@import 'path/to/directory/component-filename.less';
```

#### Using @import
Best way to import all related files is create one file, for example named as `main.less`. This file will contain only `@import` commands. Keep in mind where do you place this file. Imported path depends on where this file is placed.

###### Unexcepted behavior
There is one thing which you should keep in mind. When you try to import file which does not exist in given file path, compiler will try to find missing file in root directories of files, where is used `@import`.

Let us show this at example. Assuming you have folder structure and files as is shown below:
```
│── root-main.less
│── some-component.less
└─── B
    └── b-main.less
```

`B/b-main.less`:
```css
@import "some-component.less";
```

`some-component.less`:
```css
.some-component {
    color: red;
}
```

`root-main.less`:
```css
@import "B/b-main.less";
```

Result CSS of this example will be:
```css
.some-component {
    color: red;
}
```
As a explanation of this behavior, given in example above, is that compiler is trying to find file `some-component.less` firstly in folder where is placed `b-main.less`, then in directory of `root-main.less`. When it could not find required file in any directory, then it will thrown an error during compiling.



###### Example 1 - Importing files from current folder and its subfolders
Let us have for this example following folder structure:
```
└── common
   └─── core
   |   └── variables.less
   └─── layout
   |   └── header.less
   │── helpers.less
   └── main.less
```

For import all files in folder `common` there will be exist file `main.less` with following code:

```css
/* Import helper classes from current directory */
@import 'helpers.less';

/* Import all global variables from subdirectory core */
@import 'core/variables.less';

/* Import styles defined for header */
@import 'layout/header.less';
```

###### Example 2 - Importing files from another directory
Let us have for this example following folder structure:
```
└─── common
|   └─── core
|   |   └── variables.less
|   └─── layout
|   |   └── header.less
|   │── helpers.less
|   └── main.less
└─── domain2
    └─── core
    |   └── variables.less
    └─── layout
    |   └── footer.less
    └── main.less
```
We can see two files named `main.less` located in two different folders:
- `common/main.less` will import only that type of files which defines common styles for all domains

- `domain2/main.less` will import files which extend, add or modify styles for *domain2*

Now we want to extend styles for *domain2* by changing default colors defined in `core/variables.less` and add styles for footer.
Code below shows up how would `domain2/main.less` looks like:
```css
@import '../common/main.less';

/* In order to extend, create or modify behavior of CSS
 * styles defined in directory common we have to import
 * styles related to domain2 after importing main.less
 * from directory common.
 */
@import 'core/variable.less';

@import 'layout/footer.less';
```
