# Creating a Multidomain Design

This guide shows you, how to distinguish your multiple domains by using custom styles and/or Twig templates.
If you want to know the basic technologies we use for the frontend implementation, you can read [Design Implementation and Customization article](../frontend/design-implementation-and-customization.md).

## Model scenarios

### Scenario 1 - I want to use red color for links on my 2nd domain

This is very easy as there are already prepared `less` files for the second domain in `domain2` folder
that is configured for usage by `styles_directory` parameter in [`domains.yml`](https://github.com/shopsys/shopsys/blob/9.0/project-base/config/domains.yml).

Edit `src/Resources/styles/front/domain2/core/variables.less`:

```diff
- @color-link: @color-green;
+ @color-link: @color-red;
```

Generate CSS files from LESS using Grunt
```sh
php phing grunt
```

!!! hint
    If you are not familiar with LESS and how it deals with file imports, see [the separate article](../frontend/introduction-to-less.md).

!!! hint
    If you are not familiar with `phing`, there is [a separate article](../introduction/console-commands-for-application-management-phing-targets.md) about it as well.

### Scenario 2 - I want to change layout in left panel on my 2nd domain

In the left panel, by default, there is a category tree and an advert box.
Let us say we want to change the elements so the advert box goes first, then the category tree.

Open [`domains.yml`](https://github.com/shopsys/shopsys/blob/9.0/project-base/config/domains.yml) and set `design_id` parameter for your 2nd domain.

```diff
   domains:
       -   id: 1
           name: shopsys
           locale: en

       -   id: 2
           name: 2.shopsys
           locale: cs
           styles_directory: domain2
+          design_id: my-design
```

Duplicate [`layoutWithPanel.html.twig`](https://github.com/shopsys/shopsys/blob/9.0/project-base/templates/Front/Layout/layoutWithPanel.html.twig)
and name the new file `layoutWithPanel.my-design.html.twig`. The new file must be in the same folder as the original one.

In your new `layoutWithPanel.my-design.html.twig`, re-order the elements in the div element with class `web__main__panel`:

```twig
    <div class="web__main__panel">
        {{ render(controller('App\\Controller\\Front\\AdvertController:boxAction', {'positionName' : 'leftSidebar'})) }}

        {{ render(controller('App\\Controller\\Front\\CategoryController:panelAction', { request: app.request } )) }}

        {% block panel_content %}{% endblock %}
    </div>
```

## Final thoughts

Since there are two independent parameters for using custom styles and Twig templates,
you are able to combine them arbitrarily to achieve a multidomain design that suits your needs.
E.g. you can have 2 color sets and 3 distinct layouts, and then 6 domains with all the possible combinations.

It is important to keep in mind that second (and any other than the first) domain is not covered by tests so be aware when using different templates.
