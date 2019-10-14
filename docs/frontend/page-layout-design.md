# Page layout design
Page layout is divided to three main parts
- web__header
- web__main
- web__footer

## Weblines and containers
Each part described above should contain `web__line` - this is full width div which wraps every line.
Inside every `web__line` it must be `web__container` - it has set `web-width` and makes web content centered
in div with `web-width` width.


### Weblines
`web__main` is devided to rows. We call this rows as `web__lines`.  If you use design without left panel you can see on homepage these weblines:
- slider
- popular categories
- special offers

We use `web__lines` to change background with class modifications. E.g `web__line web__line--grey`.

### Containers
`web__container` is centered div straight in `web__line` with width set by variable `web-width` and it usually has small padding. This layout setting allows you confortably change order of these lines, switch on/off lines when you need it.

You can set `web-width` in variables.less file.

## Examples
Basic webline.

```html
<div class="web__line">
    <div class="web__container">
        <h2>Title</h2>
        ...
        content
        ...
    </div>
</div>
```

Webline with grey background.

```html
<div class="web__line web__line--grey">
    <div class="web__container">
        <h2>Title</h2>
        ...
        content
        ...
    </div>
</div>
```

Container without side gabs.

```html
<div class="web__line">
    <div class="web__container web__container--no-padding">
        <h2>Title</h2>
        ...
        content
        ...
    </div>
</div>
```
