# Guidelines for Writing Documentation

* Documentation is distributed with the source code of Shopsys Platform inside [docs](https://github.com/shopsys/shopsys/tree/master/docs/) directory in the project root.
* Documentation is written in [Markdown format](https://github.com/adam-p/markdown-here/wiki/Markdown-Cheatsheet).
* Output HTML is rendered from the `*.md` files using [MkDocs](https://www.mkdocs.org/) and hosted on [Read the Docs](https://readthedocs.org/).
    * The docs live at [https://docs.shopsys.com](https://docs.shopsys.com).
    * You can see the rendered docs locally on `http://127.0.0.1:1300` when using Docker.
        * See `mkdocs` container defined in `docker-compose.yml`.
    * You can even access the rendered docs under `/documentation/` sub-folder for any branch that is built on our CI server.
    * The main [`mkdocs.yml`](https://github.com/shopsys/shopsys/blob/master/mkdocs.yml) configuration file is located in the monorepo root.
* All documentation files should be named the same as the first heading (in lowercase, non-alphanumeric characters replaced by dash).
* References to project files and classes have to be absolute links to the GitHub in the proper version - e.g., `[config/parameters_common.yaml](https://github.com/shopsys/shopsys/blob/master/project-base/config/parameters_common.yaml)`.
    * there is an exception for files [CHANGELOG](https://github.com/shopsys/shopsys/blob/master/UPGRADE.md) and [UPGRADE](https://github.com/shopsys/shopsys/blob/master/UPGRADE.md) - these files should always be linked in the latest (i.e. `master`) version.
* References among the docs files must be relative - e.g., `[read this article](../introduction/using-form-types.md)`.
* All words in a title except conjunctions (and, or, but, etc.), articles (a, an, the), and short prepositions (in, to, of, etc.) should be capitalized. Other headings should not be capitalized.
* Each sub-folder in the `docs` folder should contain `index.md` file with links to all the other articles in the sub-folder as well as `navigation.yml` file with the menu configuration that should be consistent with the `index.md`.
* If using a list in the text, you must add an empty line above the list definition. Otherwise, it is not rendered properly.
* Rendering of multi-line code-blocks in the lists does not work properly, so if you need to add such a thing in your article, consider using headlines instead of bullet points in your text flow, or just do not indent the code-blocks at all in your list.
* Enabled MkDocs plugins and extensions:
    * [`toc`](https://python-markdown.github.io/extensions/toc/)
        * You can use `[TOC]` tag to generate a table of contents for your article.
    * [`admonition`](https://python-markdown.github.io/extensions/admonition/)
        * You can add nicely rendered blocks with notes, warnings, tips, etc. using `!!! note/warning/...` syntax.
        * You can see all the supported types in the [rST documentation](http://docutils.sourceforge.net/docs/ref/rst/directives.html#specific-admonitions).
    * [`awesome-pages`](https://github.com/lukasgeiter/mkdocs-awesome-pages-plugin)
        * Enables us to configure the navigation menu of the `docs` folder and it's sub-folders using `navigation.yml` files.
    * `search`
        * The name speaks for itself. It enables the readers searching in the docs.
