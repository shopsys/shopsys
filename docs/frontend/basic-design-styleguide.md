# Basic Design Styleguide

## Documentation
Styleguide - tool to make creating and maintaining styleguides easy for Shopsys Framework designs.
Contains all html elements necessary to create new design.

## Installation for already running project based on Shopsys Framework
You need to have Shopsys Framework installed in developer mode according to our [Installation Guide](./installation-guide.md)

1) run `php phing grunt` so you have all your styles compiled
2) open http://127.0.0.1:8000/_styleguide file in browser to see your styleguide

### How to add new section to styleguide
If you need to add your information to styleguide - edit `src/Shopsys/ShopBundle/Resources/views/Styleguide/styleguide.html.twig` which is simple twig file.

```
<section id="[your_section_id]" class="styleguide-module anchor">
    <h2 class="styleguide-module__title">[your_section_title]</h2>
    <h3 class="styleguide-module__title--small">[your_section_small_title]</h3>

    ... any html content ...

    <div class="styleguide-module__editor">
<textarea class="codemirror-html" id="html-list-simple">
... any html content ...
</textarea>
    </div>

    <div class="styleguide__info">
        Text information on blue background
    </div>
    <div class="styleguide__success">
        Text information on green background
    </div>
    <div class="styleguide__warning">
        Text information on orange background
    </div>
    <div class="styleguide__error">
        Text information on red background
    </div>
</section>
```

### Inspired by
<a href="https://hugeinc.github.io/styleguide/">https://hugeinc.github.io/styleguide/</a>
