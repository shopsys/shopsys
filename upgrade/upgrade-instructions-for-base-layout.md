# Upgrade Instructions for new base layout
There is a new base html layout with horizontal menu and product filter placed in left panel.

**Notice: If you have your custom design you can skip this all task about twig files**

- remove left panel web layout and add horizontal menu ([#1540](https://github.com/shopsys/shopsys/pull/1540))
    - because of removing left panel, we also removed unnecessary advert position called `leftSidebar`. You can remove it by creating migration and update data fixtures. If you want to remove it, please make sure that all stuff linked to old position is moved to a new position.
    - remove configuration for `leftSidebar` from `images.yml` file
        ```diff
                additionalSizes:
                    - {width: 440, height: ~, media: "(max-width: 479px)"}
                    - {width: 730, height: ~, media: "(max-width: 1023px)"}
        -   -   name: leftSidebar
        -       width: 240
        -       height: ~
        -       crop: false
        -       occurrence: 'Front-end: Advertising in the left panel under the category tree'
        -       additionalSizes:
        -           - {width: 440, height: ~, media: "(max-width: 479px)"}
        -           - {width: 730, height: ~, media: "(max-width: 768px)"}
        ```
    - because we don't have left panel on frontend anymore we have to center banner slider (or change its width to 100% - don't forget to change image size) [src/Resources/styles/front/common/components/box/slider.less](https://github.com/shopsys/shopsys/blob/master/project-base/src/Resources/styles/front/common/components/box/slider.less)
        ```diff
          @box-slider-width: @web-width - @web-panel-width - 2*@web-padding;
          @box-slider-point-size: 8px;
        + @box-slider-bottom-gap: 30px;

          .box-slider {
              display: none;

              @media @query-lg {
                  display: block;
                  position: relative;
                  margin-bottom: @margin-gap;
                  width: @box-slider-width;
                  max-width: 100%;
        +         margin: 0 auto @box-slider-bottom-gap auto;
                  visibility: hidden;
        ```
    - change left menu to horizontal menu [src/Resources/styles/front/common/components/list/menu.less](https://github.com/shopsys/shopsys/blob/master/project-base/src/Resources/styles/front/common/components/list/menu.less)
        ```diff
          @list-menu-padding-right: 25px;
          @list-menu-arrow-size: 25px;
        + @list-menu-height: 30px;

          // mobile + tablet version
          @media @query-tablet {
              .list-menu {
        -         background: @list-menu-mobile-bg;
        +         background-color: @list-menu-mobile-bg;

          @media @query-lg {
              .list-menu {
                  .reset-ul();
        -         margin-bottom: @margin-gap;
        +         display: none;
        +         position: absolute;
        +         width: 100%;
        +         left: 0;
        +         top: @list-menu-height;
        +         z-index: 9;

        -         &__item {
        -             display: block;
        +         &--root {
        +             position: relative;
        +             display: flex;
        +             flex-direction: row;
        +             width: 100%;
        +             top: 0;
        +             margin-bottom: @margin-gap;

        -             border-top: 1px solid @color-border;
        -             background: @color-light;
        +             background-color: @color-light;
        +         }

        -             &:first-child {
        -                 border-top: 0;
        +         &--dropdown {
        +             position: relative;
        +             top: 0px;
        +         }

        +         &__item {
        +             background-color: @color-light;

        +             .list-menu--root & {
        +                 display: flex;
        +                 flex-direction: column;
        +                 align-items: flex-start;
        +             }

                      &__link {
        -                 display: block;

                      & & {
                          margin-bottom: 0;

                          border-top: 1px solid @color-border;
        +                 background-color: @color-light;
                      }
        ```

    - find and change layout in all twig files and wrap all content to `.web__line` and `.web__container` elements
        ```diff
        - {% extends 'Front/Layout/layoutWithPanel.html.twig' %}
        + {% extends 'Front/Layout/layoutWithoutPanel.html.twig' %}
        ```

        Because we removed panel, we have to wrap all content to `.web__line` and `.web__container` elements
        ```diff
        +    <div class="web__line">
        +        <div class="web__container">
                     ... old content ...
        +        </div>
        +    </div>
        ```

        - check all these changes in these files:
            - [templates/Front/Content/Brand/list.html.twig](https://github.com/shopsys/shopsys/blob/master/project-base/templates/Front/Content/Brand/list.html.twig)
            - [templates/Front/Content/ContactForm/contactForm.html.twig](https://github.com/shopsys/shopsys/blob/master/project-base/templates/Front/Content/ContactForm/contactForm.html.twig)
            - [templates/Front/Content/ContactForm/index.html.twig](https://github.com/shopsys/shopsys/blob/master/project-base/templates/Front/Content/ContactForm/index.html.twig)
            - [templates/Front/Content/Product/detail.html.twig](https://github.com/shopsys/shopsys/blob/master/project-base/templates/Front/Content/Product/detail.html.twig)
            - [templates/Front/Content/Product/list.html.twig](https://github.com/shopsys/shopsys/blob/master/project-base/templates/Front/Content/Product/list.html.twig)
            - [templates/Front/Content/Product/listByBrand.html.twig](https://github.com/shopsys/shopsys/blob/master/project-base/templates/Front/Content/Product/listByBrand.html.twig)
            - [templates/Front/Content/Product/search.html.twig](https://github.com/shopsys/shopsys/blob/master/project-base/templates/Front/Content/Product/search.html.twig)

        - in these files we need to change only first line and change page layout:
            - [templates/Front/Content/Error/error.html.twig](https://github.com/shopsys/shopsys/blob/master/project-base/templates/Front/Content/Error/error.html.twig)
            - [templates/Front/Content/Default/index.html.twig](https://github.com/shopsys/shopsys/blob/master/project-base/templates/Front/Content/Default/index.html.twig)

        - move left panel under header [templates/Front/Layout/layout.html.twig](https://github.com/shopsys/shopsys/blob/master/project-base/templates/Front/Layout/layout.html.twig)
            ```diff
              <div class="web__in">
                  <div class="web__header">
                      <div class="web__line">
                          <div class="web__container">
                              {{ block('header') }}
                          </div>
                      </div>
                  </div>
            +     <div class="web__line">
            +         <div class="web__container">
            +             {{ render(controller('App\\Controller\\Front\\CategoryController:panelAction', { request: app.request } )) }}
            +         </div>
            +     </div>
            ```

      - add slidedown behaviour to horizontal menu [templates/Front/Content/Category/panel.html.twig](https://github.com/shopsys/shopsys/blob/master/project-base/templates/Front/Content/Category/panel.html.twig)
          ```diff
            {% if categoriesWithLazyLoadedVisibleChildren|length > 0 %}
          -     <ul class="js-category-list list-menu dont-print {% if isFirstLevel %}list-menu--root{% endif %}" {% if isFirstLevel %}id="js-categories"{% endif %}>

          +     <ul class="js-category-list list-menu dont-print
          +         {% if isFirstLevel %} list-menu--root{% endif %}
          +         {% if categoriesWithLazyLoadedVisibleChildren[0].category.level > 2 %} list-menu--dropdown{% endif %}"
          +         {% if isFirstLevel %}id="js-categories"{% endif %}>
          ```
          ```diff
            {{ categoryWithLazyLoadedVisibleChildren.category.name }}
            {% if categoryWithLazyLoadedVisibleChildren.hasChildren %}
          -    <i class="list-menu__item__control svg svg-arrow js-category-collapse-control {% if categoryWithLazyLoadedVisibleChildren.category in openCategories %}open{% endif %}" data-url="{{ url('front_category_branch', { parentCategoryId: categoryWithLazyLoadedVisibleChildren.category.id }) }}"></i>
          +    <i class="list-menu__item__control svg svg-arrow js-category-collapse-control" data-url="{{ url('front_category_branch', { parentCategoryId: categoryWithLazyLoadedVisibleChildren.category.id }) }}"></i>
            {% endif %}
          ```
        
- move product list filter to left panel ([#1544](https://github.com/shopsys/shopsys/pull/1544))
    - add new js file with product filter behaviour [src/Resources/scripts/frontend/product/productFilterBox.js](https://github.com/shopsys/shopsys/blob/master/project-base/src/Resources/scripts/frontend/product/productFilterToggler.js)

    - replace box filter less file with new one [src/Resources/styles/front/common/components/box/filter.less](https://github.com/shopsys/shopsys/blob/master/project-base/src/Resources/styles/front/common/components/box/filter.less)

    - update box list less component [src/Resources/styles/front/common/components/box/list.less](https://github.com/shopsys/shopsys/blob/master/project-base/src/Resources/styles/front/common/components/box/list.less)
        ```diff
          .box-list {
        +     display: flex;
        +     flex-direction: column;
        +
        +     @media @query-lg {
        +         flex-direction: row;
        +     }
        +
        +     &__panel {
        +         width: 100%;
        +
        +         @media @query-lg {
        +             width: @web-panel-width;
        +             padding-right: @web-panel-gap;
        +         }
        +     }
        +
        +     &__content {
        +        display: flex;
        +        flex-direction: column;
        +     }
        +
              &__description {
        ```

    - update in paging comoponent for new layout [src/Resources/styles/front/common/components/in/paging.less](https://github.com/shopsys/shopsys/blob/master/project-base/src/Resources/styles/front/common/components/in/paging.less)
        ```diff
          &__control {
             flex: 3;
             text-align: center;
             font-size: 0;

             @media @query-md {
        -        flex: 3;
        +        flex: 2;
        +        text-align: right;
             }

             &__item {
        ```

    - delete panel atributes from layout component [src/Resources/styles/front/common/layout/layout.less](https://github.com/shopsys/shopsys/blob/master/project-base/src/Resources/styles/front/common/layout/layout.less)
        ```diff
        - &__panel {
        -     width: 100%;
        -
        -     @media @query-lg {
        -         display: block;
        -         order: 1;
        -         flex-shrink: 0;
        -         width: @web-panel-width;
        -         padding-right: @web-panel-gap;
        -     }
        - }
        ```

    - update filter macro and set defualt behaviour of left panel according to this file [templates/Front/Content/Product/filterFormMacro.html.twig](https://github.com/shopsys/shopsys/blob/master/project-base/templates/Front/Content/Product/filterFormMacro.html.twig) (it moves `.box-list` stragiht above paging and adds box filter panel)

    - update product search according to this file [templates/Front/Content/Product/search.html.twig](https://github.com/shopsys/shopsys/blob/master/project-base/templates/Front/Content/Product/search.html.twig) (it adds `.box-list` and adds box filter panel)
