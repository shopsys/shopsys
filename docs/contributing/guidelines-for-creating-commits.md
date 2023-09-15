# Guidelines for Creating Commits
We care about a clean and understandable history of changes as it, among other advantages, makes cherry-picking and merging to your Core much easier.

Underestimating the importance of maintaining a clean git history can lead to many problems. For example, it could be more difficult to understand changes in source code and their context.

## What makes a good commit
* **It is atomic** - It contains only related modifications (similar to the Single Responsibility Principle).
* **It is a functional unit** - It contains all related modifications and tests and it should not break anything (that means, all tests pass). When you follow this rule, you can be sure that your application is functional at any revision point.

## Commit messages
We agreed on a unified form of commit messages and have written down a few rules, so understanding a commit context is much easier.
This way, we are able to understand how the specific commit changed the application without even looking into source code changes.

### Common rules
* Commit messages should be short and brief. However, if you need to include some details in the commit message, write a short summary on the first line, leave one blank line, and then write the more detailed explanation, usually in the form of a list.
* If you have much information to share, write a short summary of the modification on the first line, then write a more detailed description below. Always try to include all relevant notes.

``` markdown
administrator is now not allowed to put h1 headings to wysiwyg

- done because of SEO - there should always be only one h1 tag
- allowed to use all default format tags except h1
- see http://docs.ckeditor.com/#!/api/CKEDITOR.config-cfg-format_tags
```

* Message should contain the information **what** changed and **why**.
* If your commit message is too long, it might be a good idea to split it into several commits.
* The first letter of a commit message is in lowercase, except when the first word is a proper name (e.g., class name).
* When a commit is related only to a specific part (admin, docs, design) or only to one class or file (composer.json, services.yaml), it should be prefixed with that name.
* Present tense is used for describing the change in application behavior. It is helpful to use words to define time, such as "now", to describe the current state. Otherwise, the message could be misunderstood as a description of an error that was fixed.

``` markdown
admin: product list now displays name instead of ID
```

* Past tense is used for describing the specific change made in the code, such as renaming, adding classes, and simple modifications.

``` markdown
docs: added rule about title capitalization in Guidelines for Writing Documentation
```

``` markdown
OrderFlowFacade: removed unused uses
```

* Never start the message with the phrase "fix:". Again, it prevents developers from getting confused between a description of the fixed error and a description of the current state.
* Method or function name should always be followed by parentheses.
* Property or variable name should always be prefixed with a dollar sign.
* Merge commits and commits created using the *Squash and merge* method (see [Merging to Master on GitHub](./merging-on-github.md)) should always contain the PR number in parentheses:

``` markdown
updated packages versions in package.json (#755)
```

### Rules for specific use cases
#### Simple modification
* e.g., fixing a typo or incorrect annotation, renaming a local variable.
* Since these modifications do not influence application behavior, you should use short and simple messages.

``` markdown
typo
```

``` markdown
annotation fix
```

``` markdown
renamed variable
```

#### Renaming methods and properties
* Commit message should contain the name of the affected class, and it should be obvious what was the previous state and what is the current state.

``` markdown
ProductFacade: renamed method bar() to baz()
```

``` markdown
ProductFacade: renamed property $name to $title
```

``` markdown
ProductFacade: renamed getBy*() to getProductBy*()
```

#### Adding classes, properties or tests
* These usually need more information and should contain the reason why you added them.
``` markdown
Product: added property $weight

- needed to make transport availability dependent on the total weight of the products in cart
```
