# Guidelines for Writing Documentation

* Documentation is distributed with the source code of Shopsys Framework inside [docs](https://github.com/shopsys/shopsys/tree/7.3/docs/) directory in the project root.
* Documentation is written in [Markdown format](https://github.com/adam-p/markdown-here/wiki/Markdown-Cheatsheet).
* Output HTML is rendered from the `*.md` files using [mkdocs](https://www.mkdocs.org/) and hosted on [readthedocs](https://readthedocs.org/).
    * The docs live at [https://docs.shopsys.com](https://docs.shopsys.com).
    * When using Docker, you can see the rendered docs locally on `http://127.0.0.1:1300`.
        * See `mkdocs` container defined in `docker-compose.yml`.
    * You can even access the rendered docs under `/documentation/` sub-folder for any branch that is built on our Jenkins CI server.
    * The main [`mkdocs.yml`](https://github.com/shopsys/shopsys/blob/7.3/mkdocs.yml) configuration file is located in the monorepo root.
* All documentation files should be named same as first heading (in lowercase, non-alphanumeric characters replaced by dash).
* References to project files and classes have to be absolute links to the GitHub in proper version - e.g. `[app/config/config.yml](https://github.com/shopsys/shopsys/blob/7.3/project-base/app/config/config.yml)`.
    * there is an exception for files [CHANGELOG](https://github.com/shopsys/shopsys/blob/master/UPGRADE.md), [UPGRADE](https://github.com/shopsys/shopsys/blob/master/UPGRADE.md), and [Open Source License Acknowledgements and Third-Party Copyrights](https://github.com/shopsys/shopsys/blob/master/open-source-license-acknowledgements-and-third-party-copyrights.md) - these files should be always linked in the latest (i.e. `master`) version.
* References among the docs files must be relative - e.g. `[read this article](../introduction/using-form-types.md)`.
* All words in a title except conjunctions (and, or, but...), articles (a, an, the), and short prepositions (in, to, of...) should be capitalized. Other headings should not be capitalized.
* Each sub-folder in the `docs` folder should contain `index.md` file with links to all the other articles in the sub-folder as well as `navigation.yml` file with the menu configuration that should be consistent with the `index.md`.
* If using a list in the text, you need to add an empty line above the list definition, otherwise it is not rendered properly.
* Rendering of multi-line code-blocks in the lists does not work properly so if you need to add such thing in your article, consider using headlines instead of bullet points in your text flow, or just do not indent the code-blocks at all in your list.
* Enabled `mkdocs` plugins and extensions:
    * [`toc`](https://python-markdown.github.io/extensions/toc/)
        * You can use `[TOC]` tag to generate a table of contents for your article.
    * [`admonition`](https://python-markdown.github.io/extensions/admonition/)
        * You can add nicely rendered blocks with notes, warnings, tips, etc. using `!!! note/warning/...` syntax.
        * You can see all the supported types in the [rST documentation](http://docutils.sourceforge.net/docs/ref/rst/directives.html#specific-admonitions).
    * [`awesome-pages`](https://github.com/lukasgeiter/mkdocs-awesome-pages-plugin)
        * Enables us configuring navigation menu of the `docs` folder and it's sub-folders using `navigation.yml` files.
    * `search`
        * The name speaks for itself, it enables the readers searching in the docs.
