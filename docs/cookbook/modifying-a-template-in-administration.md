# Modifying a Template in Administration

In this tutorial, we'll show you how to proceed if you need to modify a twig template in the administration.
We will demonstrate this procedure with the example "How to display a product transfer status on the product detail page in the administration".

## Adding the information into the template

This tutorial assumes that the product already contains the transfer status attribute.
If you need to extend the product with a new attribute, see the tutorial [Adding new attribute to an entity](./adding-new-attribute-to-an-entity.md).

Modifying the bundle templates that are located in the vendor can be done via overriding these templates.

*If you need to extend or modify the form itself, it is not necessary to use the overriding and consequently to lose an upgradeability.
For the extensions of the forms, see [Enable administrator to edit the `extId` field](./adding-new-attribute-to-an-entity.md#enable-administrator-to-edit-the-extId-field).*

#### The first step is to create a copy of the original twig template that you want to modify.

Because you are trying to override the template located in

```text
vendor/shopsys/framework/src/Resources/views/Admin/Content/Product/detail.html.twig
```
therefore, the copy must be located in
```text
templates/bundles/ShopsysFrameworkBundle/Admin/Content/Product/detail.html.twig
```

See especially the new directory `ShopsysFrameworkBundle` whose title is based on the name of the bundle with original template.
Thanks to this exact location, your new copy of the template will be used instead of the original template from the FrameworkBundle during the rendering process.
At this point, you just need to modify your copy of the template in such a way that product transfer status will be displayed on the page.

!!! note
    If you want to change only some block you can override the original template. For that, you need to use `extends` macro with an exclamation mark to prevent template cycling. For example: `{% extends '@!ShopsysFramework/Admin/Content/Product/detail.html.twig' %}`
    More information can be found in [official Symfony documentation](https://symfony.com/doc/current/bundles/override.html#templates)

#### The second step is the modification of the copy itself

The template before the modification:

```twig
    ...
    {% block main_content %}

        {{ form_start(form) }}
            {{ form_errors(form) }}

            {% embed '@ShopsysFramework/Admin/Inline/FixedBar/fixedBar.html.twig' %}
                {% block fixed_bar_content %}
                    <a href="{{ url('admin_product_list') }}" class="btn-link-style">{{ 'Back to overview'|trans }}</a>
                    {{ form_save(product|default(null), form) }}
                {% endblock %}
            {% endembed %}

        {{ form_end(form) }}

    {% endblock %}
    ...
```

View in the administration before the modification:

![Admin product detail before](img/modifying-a-template-product-before.png)

Now, add the simple condition, wrapped into the divs for a prettier look, into `templates/bundles/ShopsysFrameworkBundle/Admin/Content/Product/detail.html.twig` to display the product transfer status

```twig
    <div class="form-line">
        <div class="form-line__line form-line__line--no-space">
            <div class="form-line__item form-line__item--text">

                {% if product.isTransferred  %}
                    {{ 'Product is already transferred into IS.'|trans }}
                {% else %}
                    {{ 'Product is not yet transferred into IS.'|trans }}
                {% endif %}

            </div>
        </div>
    </div>
```

The template after the modification:

```twig
    ...
    {% block main_content %}
        <div class="form-line">
            <div class="form-line__line form-line__line--no-space">
                <div class="form-line__item form-line__item--text">
                    {% if product.isTransferred  %}
                        {{ 'Product is already transferred into IS.'|trans }}
                    {% else %}
                        {{ 'Product is not yet transferred into IS.'|trans }}
                    {% endif %}
                </div>
            </div>
        </div>

        {{ form_start(form) }}
            {{ form_errors(form) }}

            {% embed '@ShopsysFramework/Admin/Inline/FixedBar/fixedBar.html.twig' %}
                {% block fixed_bar_content %}
                    <a href="{{ url('admin_product_list') }}" class="btn-link-style">{{ 'Back to overview'|trans }}</a>
                    {{ form_save(product|default(null), form) }}
                {% endblock %}
            {% endembed %}

        {{ form_end(form) }}

    {% endblock %}
    ...
```

View in the administration after the modification:

![Admin product detail after](img/modifying-a-template-product-after.png)

## Conclusion

On a practical example, we have shown you how to extend the twig template in the administration.
Using this way of overriding templates, you can edit any Symfony application template, see [How to Override any Part of a Bundle](https://symfony.com/doc/3.4/templating/overriding.html).
