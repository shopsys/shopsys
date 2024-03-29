# Hover Intent

This JS component is used to add timeout for hover over and hover out events.
This component uses [hoverIntent plugin](http://briancherne.github.io/jquery-hoverIntent/) from jQuery.

## Plugin settings

You can set default values in file `assets/js/frontend/components/hoverIntent.js` or you can set variables manually by data attributes.

This plugin is binded to elements with class `.js-hover-intent`

### Force click

In some scenarios you need to add hover functionality and simulate click on another element. You can define it by `data-hover-intent-force-click="true"` and data element with `data-hover-intent-force-click-element=".js-element-to-click-class"`. I makes hover effect with defined interval a calls click event on element with class `.js-element-to-click-class`.

### Javascript default settings

```javascript
var interval = 200;
var timeout = 200;
var classForOpen = 'open';
var forceClick = false;
var forceClickElement = '';
var linkOnMobile = false;
```

### Html element settings

```html
<div
    class="js-hover-intent"
    data-hover-intent-interval="200"
    data-hover-intent-timeout="200"
    data-hover-intent-class-for-open="open"
    data-hover-intent-force-click="false"
    data-hover-intent-force-click-element=""
    data-hover-intent-link-on-mobile="false"
></div>
```
