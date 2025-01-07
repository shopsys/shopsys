# Creating a Multidomain Design

This guide shows you, how to distinguish your multiple domains by using custom styles and/or Twig templates.
To know the basic technologies we use for frontend implementation, you can read [Design Implementation and Customization article](../frontend/design-implementation-and-customization.md).

## Model scenarios

### Scenario 1 - I want to change the layout in the footer on my 2nd domain

In the footer, among other things, are on the right side contact phone number and email, a list of articles and a link to the contact page.
Let us say we want to change the elements so the list of articles goes first, then the link to the contact page, and the phone number and email are last.

Open [`domains.yaml`](https://github.com/shopsys/shopsys/blob/master/project-base/config/domains.yaml) and set `design_id` parameter for your 2nd domain.

```diff
   domains:
       -   id: 1
           name: shopsys
           locale: en

       -   id: 2
           name: 2.shopsys
           locale: cs
+          design_id: my-design
```

Duplicate [`footer.html.twig`](https://github.com/shopsys/shopsys/blob/master/project-base/templates/Front/Layout/footer.html.twig)
and name the new file `footer.my-design.html.twig`. The new file must be in the same folder as the original one.

In your new `footer.my-design.html.twig`, re-order the elements in the div element with class `footer__bottom__articles`:

```twig
    <div class="footer__bottom__articles">
        <a class="menu__item__link" href="{{ url('front_contact') }}">{{ 'Contact'|trans }}</a>
        {{ getShopInfoPhoneNumber() }}
        {{ getShopInfoEmail() }}
        {{ render(controller('App\\Controller\\Front\\ArticleController::footerAction')) }}
    </div>
```

## Final thoughts

Since there are two independent parameters for using custom styles and Twig templates,
you are able to combine them arbitrarily to achieve a multidomain design that suits your needs.
E.g. you can have 2 color sets and 3 distinct layouts, and then 6 domains with all the possible combinations.

It is important to keep in mind that tests do not cover any other than the first domain, so be aware when using different templates.
