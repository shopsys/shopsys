# Necessary steps to build application 

- Update your `composer.json`
```diff
     "arvenil/ninja-mutex": "^0.4.1",
-    "bmatzner/jquery-bundle": "^2.2.2",
-    "bmatzner/jquery-ui-bundle": "^1.10.3",
     "commerceguys/intl": "^1.0.0",
     "symfony-cmf/routing-bundle": "^2.0.3",
-    "symfony/assetic-bundle": "^2.8.2",
     "symfony/debug": "^3.4",
     "symfony/web-server-bundle": "^3.4",
+    "symfony/webpack-encore-bundle": "^1.7",
     "symfony/workflow": "^3.4",

```

- Run `composer update`

- Replace your `config/package/assets.yaml` file with following content
```yaml
framework:
    assets:
        json_manifest_path: '%kernel.project_dir%/web/build/manifest.json'
```

- Replace your `config/package/webpack_encore.yaml` file with following content
```yaml
webpack_encore:
    output_path: '%kernel.project_dir%/web/build'
```

- Move all scripts from `src/Resources/scripts` to `assets/js`

- Remove folder `assets/js/plugins` (plugins are loaded with npm)

- Update your `package.json`
```diff
     "name": "shopsys",
+    "scripts": {
+        "dev-server": "encore dev-server",
+        "dev": "encore dev",
+        "watch": "encore dev --watch",
+        "build": "encore production --progress",
+        "trans": "./assets/js/bin/trans.js",
+        "trans:dump": "./assets/js/bin/transDump.js",
+        "tests": "npm run tests:unit",
+        "tests:unit": "jest"
+    },
+    "dependencies": {
+        "@shopsys/framework": "9.0.0-dev.1",
+        "codemirror": "^5.49.2",
+        "glob": "^7.1.6",
+        "jquery-ui": "^1.12.1",
+        "jquery-ui-touch-punch": "^0.2.3",
+        "jquery.cookie": "^1.4.1",
+        "minilazyload": "^2.3.3",
+        "slick-carousel": "1.6.0"
+    },
     "devDependencies": {
+        "@babel/core": "^7.8.3",
+        "@babel/parser": "^7.7.7",
+        "@babel/traverse": "^7.7.4",
+        "@babel/preset-env": "^7.8.3",
+        "@symfony/webpack-encore": "^0.28.0",
         "autoprefixer": "^9.4.4",
+        "babel-jest": "^25.1.0",
+        "copy-webpack-plugin": "^5.1.1",
+        "core-js": "^3.0.0",
         "es6-promise": "^4.2.5",
         "eslint": "^5.12.0",
+        "event-hooks-webpack-plugin": "^2.1.5",
+        "jest": "^25.1.0",
         "grunt": "^1.0.3",
         "jit-grunt": "^0.10.0",
+        "regenerator-runtime": "^0.13.2",
         "stylelint": "^11.1.1",
-        "time-grunt": "^1.4.0"
+        "time-grunt": "^1.4.0",
+        "webpack-notifier": "^1.6.0"
```

- Create file `babel.config.js` with this content
```js
    module.exports = {
        presets: [
            [
                '@babel/preset-env',
                {
                    targets: {
                        node: 'current',
                    },
                },
            ],
        ],
    };
```

- Run `npm install` or `./phing npm-install-dependencies`

- Replace file `webpack.config.js` from [GitHub](https://github.com/shopsys/shopsys/blob/9.0/project-base/webpack.config.js) into your project root
- Copy file [`/frontend/validation/validationInit.js`](https://github.com/shopsys/shopsys/blob/9.0/project-base/assets/js/frontend/validation/validationInit.js) into your `assets/js`
- Copy folder [`assets/js/commands`](https://github.com/shopsys/shopsys/tree/9.0/project-base/assets/js/commands) into your `assets/js` folder
- Copy folder [`assets/js/bin`](https://github.com/shopsys/shopsys/tree/9.0/project-base/assets/js/bin) into your assets/js folder
- Copy folder [`assets/js/utils`](https://github.com/shopsys/shopsys/tree/9.0/project-base/assets/js/utils) into your assets/js folder 

- Create new js file `./assets/js/frontend.js` and import into it all your frontend javascripts (find inspiration on [GitHub](https://github.com/shopsys/shopsys/blob/9.0/project-base/assets/js/frontend.js))

- Rename folder `assets/js/custom_admin` to `assets/js/admin`

- Create file `assets/js/admin/admin.js` with this content
```js
import 'framework/admin';
```

- Create file `assets/js/jquery.js` with this content
```js
import 'framework/admin/jquery';
```

- Update your `base.html.twig` template
```diff
     <link rel="stylesheet" type="text/css" href="{{ asset('assets/frontend/styles/index' ~ getDomain().id ~ '_' ~ getCssVersion() ~ '.css') }}" media="screen, projection">
     <link rel="stylesheet" type="text/css" href="{{ asset('assets/frontend/styles/print' ~ getDomain().id ~ '_' ~ getCssVersion() ~ '.css') }}" media="print">
 
-    {# bootstrap/tooltip.js must be imported before bootstrap/popover.js #}
+    {{ encore_entry_script_tags('jquery') }}

     {{ importJavascripts([
-        'bundles/bmatznerjquery/js/jquery.min.js',
-        'bundles/bmatznerjquery/js/jquery-migrate.js',
-        'bundles/fpjsformvalidator/js/fp_js_validator.js',
+        'bundles/fpjsformvalidator/js/fp_js_validator.js'
     ]) }}

     {{ js_validator_config() }}
     {{ init_js_validation() }}

     {% block html_body %}{% endblock %}
 
-    {% if app.environment == 'dev' %}
-        <script async src="//localhost:35729/livereload.js"></script>
-    {% endif %}
-
-
-    {{ importJavascripts([
-        'frontend/plugins/*.js',
-        'common/components/escape.js',
-        'common/components/keyCodes.js',
-        'common/register.js',
-        'common/bootstrap/tooltip.js',
-        'common/bootstrap/popover.js',
-        'common/plugins/*.js',
-        'frontend/validation/form/*.js',
-        'common/validation/*.js',
-        'frontend/cart/*.js',
-        'frontend/order/*.js',
-        'frontend/product/*.js',
-        'common/*.js',
-        'frontend/*.js',
-        'common/components/*.js',
-        'frontend/components/*.js',
-    ]) }}
+    {{ encore_entry_script_tags('app') }}
 
     {% block javascripts_bottom %}{% endblock %}
 </body>
```

- Update your `.gitignore` file
```diff
+ /assets/js/translations.json
```

- Remove folder `tests/App/Functional/Component/Javascript`

# Necessary steps to build javascripts

We refactored all javascripts in [#1545](https://github.com/shopsys/shopsys/pull/1545).
You have two ways how to perform upgrade.
The first way is incorporate changes from framework's common javascripts into your files.
Those changes are described in diffs on next lines.
The second way is to download all new javascripts from [`assets/js`](https://github.com/shopsys/shopsys/blob/9.0/project-base/assets/js) and put your changes into it.
You can found git diff command after individual files.

We recommend the second way although it is more demanding because new javascripts will be used as source for next upgrade notes.

- Remove file `assets/js/frontend/responsive.js`

- Update your `assets/js/frontend/cart/cartBox.js`
```diff
+ import Ajax from 'framework/common/utils/ajax';
+ import Register from 'framework/common/utils/register';

  (function ($) {

-     Shopsys = window.Shopsys || {};
+     const Shopsys = window.Shopsys || {};

      Shopsys.cartBox.reload = function (event) {
-         Shopsys.ajax({
+         Ajax.ajax({
              loaderElement: '#js-cart-box',
              ...
          });
      };

-     Shopsys.register.registerCallback(Shopsys.cartBox.init);
+     (new Register()).registerCallback(Shopsys.cartBox.init);
})(jQuery)
```

Or you can see full changes between your old file and new file
```sh
git diff master:src/Resources/scripts/frontend/cart/cartBox.js assets/js/frontend/cart/cartBox.js
```

- Update your `assets/js/frontend/cart/cartRecalculator.js`
```diff
+ import Ajax from 'framework/common/utils/ajax';
+ import Timeout from 'framework/common/utils/timeout';
+ import Register from 'framework/common/utils/register';
+ import constant from '../utils/constant';
+ import { KeyCodes } from 'framework/common/utils/keyCodes';

  (function ($) {
 
-     Shopsys = window.Shopsys || {};
+     const Shopsys = window.Shopsys || {};
 
      Shopsys.cartRecalculator.init = function ($container) {
          function reloadWithDelay (delay) {
-             Shopsys.timeout.setTimeoutAndClearPrevious(
+             Timeout.setTimeoutAndClearPrevious(
                  ...
              )
          }

          $container.filterAllNodes('.js-cart-item .js-spinbox-input')
              .keydown(function (event) {
-                 if (event.keyCode === Shopsys.keyCodes.ENTER) {
+                 if (event.keyCode === KeyCodes.ENTER) {
                      ...
                  }
              });

      Shopsys.cartRecalculator.reload = function () {
          var formData = $('.js-cart-form').serializeArray();
          formData.push({
-             name: Shopsys.constant('\\App\\Controller\\Front\\CartController::RECALCULATE_ONLY_PARAMETER_NAME'),
+             name: constant('\\App\\Controller\\Front\\CartController::RECALCULATE_ONLY_PARAMETER_NAME'),
              value: 1
          });
 
-         Shopsys.ajax({
+         Ajax.ajax({
              ...
              success: function (html) { 
                  ...
-                 Shopsys.register.registerNewContent($mainContent);
-                 Shopsys.register.registerNewContent($cartBox);
+                 (new Register()).registerNewContent($mainContent);
+                 (new Register()).registerNewContent($cartBox);
              }
          });
      };
 
-     Shopsys.register.registerCallback(Shopsys.cartRecalculator.init);
+     (new Register()).registerCallback(Shopsys.cartRecalculator.init);
  })(jQuery);
```

Or you can see full changes between your old file and new file
```sh
git diff master:src/Resources/scripts/frontend/cart/cartRecalculator.js assets/js/frontend/cart/cartRecalculator.js
```

- Update your `assets/js/frontend/categoryPanel.js`
```diff
+ import Ajax from 'framework/common/utils/ajax';
+ import Register from 'framework/common/utils/register';
+ import Responsive from './utils/responsive';

  (function ($) {
 
-     Shopsys = window.Shopsys || {};
+     const Shopsys = window.Shopsys || {};
      Shopsys.categoryPanel = Shopsys.categoryPanel || {};
 
      Shopsys.categoryPanel.init = function ($container) {
          $container.filterAllNodes('.js-category-collapse-control')
             .on('click', onCategoryCollapseControlClick);
+
+         if (!Responsive.isDesktopVersion()) {
+             $container.filterAllNodes('.js-category-collapse-control').each((index, element) => {
+                 if ($(element).parent().siblings('.js-category-list-placeholder').length === 0) {
+                     $(element).addClass('open');
+                 }
+             });
+         }
      };
  
      function loadCategoryItemContent ($categoryItem, url) {
-         Shopsys.ajax({
+         Ajax.ajax({
              $categoryListPlaceholder.replaceWith($categoryList);
              $categoryList.hide().slideDown('fast');
 
-             Shopsys.register.registerNewContent($categoryList);
+             (new Register()).registerNewContent($categoryList);
          }
      });

-     Shopsys.register.registerCallback(Shopsys.categoryPanel.init);
+     (new Register()).registerCallback(Shopsys.categoryPanel.init);
 
  })(jQuery);
```

Or you can see full changes between your old file and new file
```sh
git diff master:src/Resources/scripts/frontend/categoryPanel.js assets/js/frontend/categoryPanel.js
```

- Update your `assets/js/frontend/components/ajaxMoreLoader.js`
```diff
+ import 'framework/common/components';
+ import Ajax from 'framework/common/utils/ajax';
+ import Register from 'framework/common/utils/register';
+ import Translator from 'bazinga-translator';

  (function ($) {
 
-     Shopsys = window.Shopsys || {};
+     const Shopsys = window.Shopsys || {};

      var optionsDefaults = {
          buttonTextCallback: function (loadNextCount) {
-             return Shopsys.translator.transChoice(
+             return Translator.transChoice(
          }
          ....
      }

      Shopsys.AjaxMoreLoader = function ($wrapper, options) {
          var onClickLoadMoreButton = function () {
-             Shopsys.ajax({
+             Ajax.ajax({
                  ...
              });
          }
      }
 
-     Shopsys.register.registerCallback(function ($container) {
+     (new Register()).registerCallback(function ($container) {
          ...
      });
  })(jQuery);

```

Or you can see full changes between your old file and new file
```sh
git diff master:src/Resources/scripts/frontend/components/ajaxMoreLoader.js assets/js/frontend/components/ajaxMoreLoader.js
```

- Update your `assets/js/frontend/components/popup.js`
```diff
+import Register from 'framework/common/utils/register';
 
- Shopsys = window.Shopsys || {};

  (function () {
 
-     Shopsys.register.registerCallback(function ($container) {
+     new Register()).registerCallback(function ($container) {
          ...
      });
  })();

```

Or you can see full changes between your old file and new file
```sh
git diff master:src/Resources/scripts/frontend/components/popup.js assets/js/frontend/components/popup.js
```

- Update your `assets/js/frontend/components/responsiveTabs.js`
```diff
+ import HybridTabs from 'framework/common/utils/hybridTabs';
+ import Register from 'framework/common/utils/register';
+ import Responsive from '../utils/responsive';

  (function ($) {
-     Shopsys = window.Shopsys || {};
 
-     Shopsys.register.registerCallback(function ($container) {
+     (new Register()).registerCallback(function ($container) {
          $container.filterAllNodes('.js-responsive-tabs').each(function () {
-             var hybridTabs = new Shopsys.hybridTabs.HybridTabs($(this));
+             const hybridTabs = new HybridTabs($(this));
+             const responsive = new Responsive();

              hybridTabs.init(getHybridTabsModeForCurrentResponsiveMode());
 
-             Shopsys.responsive.registerOnLayoutChange(function () {
+             responsive.registerOnLayoutChange(function () {
                  hybridTabs.setTabsMode(getHybridTabsModeForCurrentResponsiveMode());
              });
 
              function getHybridTabsModeForCurrentResponsiveMode () {
-                 if (Shopsys.responsive.isDesktopVersion()) {
-                     return Shopsys.hybridTabs.TABS_MODE_SINGLE;
+                 if (Responsive.isDesktopVersion()) {
+                     return HybridTabs.TABS_MODE_SINGLE;
                  } else {
-                     return Shopsys.hybridTabs.TABS_MODE_MULTIPLE;
+                     return HybridTabs.TABS_MODE_MULTIPLE;
                  }
              }
          });
      });
  })(jQuery);
```

Or you can see full changes between your old file and new file
```sh
git diff master:src/Resources/scripts/frontend/components/responsiveTabs.js assets/js/frontend/components/responsiveTabs.js
```

- Update your `assets/js/frontend/form.js` 
```diff
+ import Register from 'framework/common/utils/register';

  (function ($) {

-     Shopsys = window.Shopsys || {};
+     const Shopsys = window.Shopsys || {};
 
      Shopsys.form.disableDoubleSubmit = function ($container) {
      });
 
-     Shopsys.register.registerCallback(Shopsys.form.disableDoubleSubmit, Shopsys.register.CALL_PRIORITY_HIGH);
+     (new Register()).registerCallback(Shopsys.form.disableDoubleSubmit, Register.CALL_PRIORITY_HIGH);
  }((jQuery);
```

Or you can see full changes between your old file and new file
```sh
git diff master:src/Resources/scripts/frontend/form.js assets/js/frontend/form.js
```

- Update your `assets/js/frontend/honeyPot.js`
```diff
+ import Register from 'framework/common/utils/register';

  (function ($) {
 
-     Shopsys = window.Shopsys || {};
+     const Shopsys = window.Shopsys || {};
 
-     Shopsys.register.registerCallback(function ($container) {
+     (new Register()).registerCallback(function ($container) {
          $container.filterAllNodes('.js-honey').hide();
      });
  })(jQuery);
```

Or you can see full changes between your old file and new file
```sh
git diff master:src/Resources/scripts/frontend/honeyPot.js assets/js/frontend/honeyPot.js
```

- Update your `assets/js/frontend/lazyLoadInit.js` 
```diff
+ import MiniLazyload from 'minilazyload';
+ import Register from 'framework/common/utils/register';

  (function ($) { 
-     Shopsys = window.Shopsys || {};
+     const Shopsys = window.Shopsys || {};

+     (new Register()).registerCallback(Shopsys.lazyLoadCall.inContainer);

  })(jQuery);
```

Or you can see full changes between your old file and new file
```sh
git diff master:src/Resources/scripts/frontend/lazyLoadInit.js assets/js/frontend/lazyLoadInit.js
```

- Update your `assets/js/frontend/login.js`
 ```diff
+ import Ajax from 'framework/common/utils/ajax';
+ import Register from 'framework/common/utils/register';
+ import { createLoaderOverlay, showLoaderOverlay } from 'framework/common/utils/loaderOverlay';
+ import Window from './utils/window';
+ import Translator from 'bazinga-translator';

  (function ($) {

-     Shopsys = window.Shopsys || {};
+     const Shopsys = window.Shopsys || {};
      Shopsys.login = Shopsys.login || {};
 
      Shopsys.login.Login = function () {

          function showWindow (event) {
-             Shopsys.ajax({
+             Ajax.ajax({
                  url: $(this).data('url'),
                  type: 'POST',
                  success: function (data) {
-                     var $window = Shopsys.window({
+                     new Window({
                          content: data,
                          ...
                  }
              });
          }
 
          function onSubmit () {
-             Shopsys.ajax({
+             Ajax.ajax({
                  loaderElement: '.js-front-login-window',
                  ...
                  success: function (data) {
                      if (data.success === true) {
-                         var $loaderOverlay = Shopsys.loaderOverlay.createLoaderOverlay('.js-front-login-window');
-                         Shopsys.loaderOverlay.showLoaderOverlay($loaderOverlay);
+                         var $loaderOverlay = createLoaderOverlay('.js-front-login-window');
+                         showLoaderOverlay($loaderOverlay);
 
                          document.location = data.urlToRedirect;
                      } else {
                          var $validationErrors = $('.js-window-validation-errors');
                          if ($validationErrors.hasClass('display-none')) {
                              $validationErrors
-                                 .text(Shopsys.translator.trans('This account doesn\'t exist or password is incorrect'))
+                                 .text(Translator.trans('This account doesn\'t exist or password is incorrect'))
                                  .show();
                          }
                      }
                  }
              });
          }
      }
 
-     Shopsys.register.registerCallback(function ($container) {
+     (new Register()).registerCallback(function ($container) {
          ...
      });
  })(jQuery);
```

Or you can see full changes between your old file and new file
```sh
git diff master:src/Resources/scripts/frontend/login.js assets/js/frontend/login.js
```

- Update your `assets/js/frontend/newsletterSubscriptionForm.js`
```diff
+ import Ajax from 'framework/common/utils/ajax';
+ import Register from 'framework/common/utils/register';
+ import Window from './utils/window';
+ import Translator from 'bazinga-translator';

  (function ($) {
 
-     Shopsys = window.Shopsys || {};
+     const Shopsys = window.Shopsys || {};
 
-     Shopsys.register.registerCallback(function ($container) {
+     (new Register()).registerCallback(function ($container) {
          ...
      });
 
      Shopsys.newsletterSubscriptionForm.ajaxSubmit = function () {
-         Shopsys.ajax({
+         Ajax.ajax({
              loaderElement: 'body',
              ...
          })
      }

-     Shopsys.register.registerNewContent($newContent);
+     (new Register()).registerNewContent($newContent);
         if ($newContent.data('success')) {
             $emailInput.val('');
 
-            Shopsys.window({
-                content: Shopsys.translator.trans('You have been successfully subscribed to our newsletter.'),
+            new Window({
+                content: Translator.trans('You have been successfully subscribed to our newsletter.'),
                 buttonCancel: true,
-                textCancel: Shopsys.translator.trans('Close')
+                textCancel: Translator.trans('Close')
             });
         }
     };
```

Or you can see full changes between your old file and new file
```sh
git diff master:src/Resources/scripts/frontend/newsletterSubscriptionForm.js assets/js/frontend/newsletterSubscriptionForm.js
```

- Update your `assets/js/frontend/order/order.js`
```diff
+ import Register from 'framework/common/utils/register';

  (function ($) {
 
-     Shopsys = window.Shopsys || {};
+     const Shopsys = window.Shopsys || {};
 
      Shopsys.order.paymentTransportRelations = [];

      Shopsys.order.init = function ($container) {
          ...

+         const paymentTransportRelations = $('.js-payment-transport-relations');
+         if (paymentTransportRelations.length > 0) {
+             paymentTransportRelations.data('relations').forEach(item => {
+                 Shopsys.order.addPaymentTransportRelation(item.paymentId, item.transportId);
+             });
+         }

          $transportInputs.change(Shopsys.order.onTransportChange);
          ...
      };
 
-     Shopsys.register.registerCallback(Shopsys.order.init);
+     (new Register()).registerCallback(Shopsys.order.init);
  })(jQuery);
```

Or you can see full changes between your old file and new file
```sh
git diff master:src/Resources/scripts/frontend/order/order.js assets/js/frontend/order/order.js
```

- Update your `assets/js/frontend/order/orderRememberData.js`
```diff
+ import Ajax from 'framework/common/utils/ajax';
+ import Register from 'framework/common/utils/register';

  (function ($) {
 
-     Shopsys = window.Shopsys || {};
+     const Shopsys = window.Shopsys || {};

      Shopsys.orderRememberData.saveData = function () {
          clearTimeout(Shopsys.orderRememberData.delayedSaveDataTimer);
          var $orderForm = $('#js-order-form');
-         Shopsys.ajaxPendingCall('Shopsys.orderRememberData.saveData', {
+         Ajax.ajaxPendingCall('Shopsys.orderRememberData.saveData', {
              type: 'POST',
              ...
          });
      };
 
-     Shopsys.register.registerCallback(Shopsys.orderRememberData.init);
+     (new Register()).registerCallback(Shopsys.orderRememberData.init);
  })(jQuery);

```

Or you can see full changes between your old file and new file
```sh
git diff master:src/Resources/scripts/frontend/order/orderRememberData.js assets/js/frontend/order/orderRememberData.js
```

- Update your `assets/js/frontend/order/preview.js`
```diff
+ import 'framework/common/components';
+ import Ajax from 'framework/common/utils/ajax';
+ import Register from 'framework/common/utils/register';

  (function ($) {

-     Shopsys = window.Shopsys || {};
+     const Shopsys = window.Shopsys || {};

      Shopsys.orderPreview.loadOrderPreview = function () {
          ...
-         Shopsys.ajaxPendingCall('Shopsys.orderPreview.loadOrderPreview', {
+         Ajax.ajaxPendingCall('Shopsys.orderPreview.loadOrderPreview', {
              loaderElement: '#js-order-preview',
              ...
              success: function (data) {
                  ...
-                 Shopsys.register.registerNewContent($orderPreview);
+                 (new Register()).registerNewContent($orderPreview);
              }
          });
      };
 
-     Shopsys.register.registerCallback(Shopsys.orderPreview.init);
+     (new Register()).registerCallback(Shopsys.orderPreview.init);
 })(jQuery);
```

Or you can see full changes between your old file and new file
```sh
git diff master:src/Resources/scripts/frontend/order/preview.js assets/js/frontend/order/preview.js
```

- Update your `assets/js/frontend/product/addProduct.js`
```diff
+ import 'framework/common/components';
+ import Ajax from 'framework/common/utils/ajax';
+ import Register from 'framework/common/utils/register';
+ import Window from '../utils/window';
+ import Translator from 'bazinga-translator';

  (function ($) {
 
-     Shopsys = window.Shopsys || {};
+     const Shopsys = window.Shopsys || {};
 
      Shopsys.addProduct.ajaxSubmit = function (event) {
-         Shopsys.ajax({
+         Ajax.ajax({
              url: $(this).data('ajax-url'),
              ...
          });
      }

      Shopsys.addProduct.onSuccess = function (data) {
          ...
          if (buttonContinueUrl !== undefined) {
-             Shopsys.window({
+             new Window({
                  content: data,
                  ...
-                 textContinue: Shopsys.translator.trans('Go to cart'),
+                 textContinue: Translator.trans('Go to cart'),
                  ...
              });
              $('#js-cart-box').trigger('reload');
          } else {
-             Shopsys.window({
+             new Window({
                  content: data,
                  ...
-                 textCancel: Shopsys.translator.trans('Close'),
+                 textCancel: Translator.trans('Close'),
                  ...
              });
          }
      };

      Shopsys.addProduct.onError = function (jqXHR) {
          if (jqXHR.status !== 0) {
-             Shopsys.window({
-                 content: Shopsys.translator.trans('Operation failed')
+             new Window({
+                 content: Translator.trans('Operation failed')
              });
          }
      };
 
-     Shopsys.register.registerCallback(Shopsys.addProduct.init);
+     (new Register()).registerCallback(Shopsys.addProduct.init);
 
  })(jQuery);
```

Or you can see full changes between your old file and new file
```sh
git diff master:src/Resources/scripts/frontend/product/addProduct.js assets/js/frontend/product/addProduct.js
```

- Update your `assets/js/frontend/product/gallery.js`
```diff
+ import 'magnific-popup';
+ import 'slick-carousel';
+ import Responsive from '../utils/responsive';

  (function ($) {

-     Shopsys = window.Shopsys || {};
+     const Shopsys = window.Shopsys || {};

      Shopsys.productDetail.init = function () {
          $gallery.filterAllNodes('.js-gallery-slides').slick({
              ...
              responsive: [
                  {
-                     breakpoint: Shopsys.responsive.XS,
+                     breakpoint: Responsive.XS,
                      ...
                  },
                  {
-                     breakpoint: Shopsys.responsive.MD,
+                     breakpoint: Responsive.MD,
                      ...
                  },
                  {
-                     breakpoint: Shopsys.responsive.LG,
+                     breakpoint: Responsive.LG,
                      ...
                  },
                  {
-                     breakpoint: Shopsys.responsive.VL,
+                     breakpoint: Responsive.VL,
                      ...
                 }
          });
      };
  })(jQuery);
```

Or you can see full changes between your old file and new file
```sh
git diff master:src/Resources/scripts/frontend/product/gallery.js assets/js/frontend/product/gallery.js
```

- Update your `assets/js/frontend/product/productList.js`
```diff
+ import 'jquery.cookie';
+ import 'framework/common/components';
+ import Register from 'framework/common/utils/register';
+ import Translator from 'bazinga-translator';

  (function ($) {

-     Shopsys = window.Shopsys || {};
+     const Shopsys = window.Shopsys || {};

-     Shopsys.register.registerCallback(function ($container) {
+     (new Register()).registerCallback(function ($container) {
          ...
          $container.filterAllNodes('.js-product-list-with-paginator').each(function () {
              var ajaxMoreLoader = new Shopsys.AjaxMoreLoader($(this), {
                  buttonTextCallback: function (loadNextCount) {
-                     return Shopsys.translator.transChoice(
+                     return Translator.transChoice(
                          ...
                      );
                  });
              });
          });
      });
  })(jQuery);
```

Or you can see full changes between your old file and new file
```sh
git diff master:src/Resources/scripts/frontend/product/productList.js assets/js/frontend/product/productList.js
```

- Update your `assets/js/frontend/promoCode.js`
```diff
+ import Ajax from 'framework/common/utils/ajax';
+ import Window from './utils/window';
+ import Register from 'framework/common/utils/register';
+ import constant from './utils/constant';
+ import { KeyCodes } from 'framework/common/utils/keyCodes';
+ import Translator from 'bazinga-translator';

  (function ($) {

-     Shopsys = Shopsys || {};
+     const Shopsys = window.Shopsys || {};
 
      Shopsys.promoCode.PromoCode = function ($container) {

          this.init = function () {
              $promoCodeSubmitButton.click(applyPromoCode);
              $promoCodeInput.keypress(function (event) {
-                 if (event.keyCode === Shopsys.keyCodes.ENTER) {
+                 if (event.keyCode === KeyCodes.ENTER) {
                      ...
                  }
              });
          };

          var applyPromoCode = function () {
              var code = $promoCodeInput.val();
              if (code !== '') {
                  var data = {};
-                 data[Shopsys.constant('\\App\\Controller\\Front\\PromoCodeController::PROMO_CODE_PARAMETER')] = code;
-                 Shopsys.ajax({
+                 data[constant('\\App\\Controller\\Front\\PromoCodeController::PROMO_CODE_PARAMETER')] = code;
+                 Ajax.ajax({
                      loaderElement: '#js-promo-code-submit-button',
                      ...
                  });
              } else {
-                 Shopsys.window({
-                     content: Shopsys.translator.trans('Please enter promo code.')
+                 new Window({
+                     content: Translator.trans('Please enter promo code.')
                  });
              }
          };

          var onApplyPromoCode = function (response) {
              if (response.result === true) {
                  document.location.reload();
              } else {
-                 Shopsys.window({
+                 new Window({
                      content: response.message
                  });
              }
          };
      };
 
-     Shopsys.register.registerCallback(function ($container) {
+     (new Register()).registerCallback(function ($container) {
          var promoCode = new Shopsys.promoCode.PromoCode($container);
          promoCode.init();
      });
  })(jQuery);
```

Or you can see full changes between your old file and new file
```sh
git diff master:src/Resources/scripts/frontend/promoCode.js assets/js/frontend/promoCode.js
```

- Update your `assets/js/frontend/rangeSlider.js`
```diff
+ import 'jquery-ui/slider';
+ import { parseNumber, formatDecimalNumber } from 'framework/common/utils/number';
+ import Register from 'framework/common/utils/register';

  (function ($) {

-     Shopsys = Shopsys || {};
+     const Shopsys = window.Shopsys || {};

      Shopsys.rangeSlider.RangeSlider = function ($sliderElement) {
          this.init = function () {
              ...
              slide: function (event, ui) {
                  var minimumSliderValue = getValueFromStep(ui.values[0]);
                  var maximumSliderValue = getValueFromStep(ui.values[1]);
-                 $minimumInput.val(minimumSliderValue != minimalValue ? Shopsys.number.formatDecimalNumber(minimumSliderValue, 2) : '');
-                 $maximumInput.val(maximumSliderValue != maximalValue ? Shopsys.number.formatDecimalNumber(maximumSliderValue, 2) : '');
+                 $minimumInput.val(minimumSliderValue != minimalValue ? formatDecimalNumber(minimumSliderValue, 2) : '');
+                 $maximumInput.val(maximumSliderValue != maximalValue ? formatDecimalNumber(maximumSliderValue, 2) : '');
              },
              ...
          };
 
          function updateSliderMinimum () {
-             var value = Shopsys.number.parseNumber($minimumInput.val()) || minimalValue;
+             var value = parseNumber($minimumInput.val()) || minimalValue;
              var step = getStepFromValue(value);
              $sliderElement.slider('values', 0, step);
          }

          function updateSliderMaximum () {
-             var value = Shopsys.number.parseNumber($maximumInput.val()) || maximalValue;
+             var value = parseNumber($maximumInput.val()) || maximalValue;
              var step = getStepFromValue(value);
              $sliderElement.slider('values', 1, step);
          }
      };
 
-     Shopsys.register.registerCallback(function ($container) {
+     (new Register()).registerCallback(function ($container) {
          ...
      });
  })(jQuery);
```

Or you can see full changes between your old file and new file
```sh
git diff master:src/Resources/scripts/frontend/rangeSlider.js assets/js/frontend/rangeSlider.js
```

- Update your `assets/js/frontend/spinbox.js`
```diff
+ import Ajax from 'framework/common/utils/ajax';
+ import Register from 'framework/common/utils/register';
+ import Translator from 'bazinga-translator';
+ import Timeout from 'framework/common/utils/timeout';
+ import Window from './utils/window';

  (function ($) {

-     Shopsys = window.Shopsys || {};
+     const Shopsys = window.Shopsys || {};

      Shopsys.spinbox.init = function ($container) {

          self.validateStepAfterTimeout = function (event) {
-             Shopsys.timeout.setTimeoutAndClearPrevious(
+             Timeout.setTimeoutAndClearPrevious(
                  'spinbox',
                  ...
              );
          }

         self.validateStep = function (event) {
              ...
-             Shopsys.ajax({
+             Ajax.ajax({
                  ...
                  success: function (data) {
                      ...
                      if (value <= 0 || (value - min) % stepSize !== 0) {
                         ...
-                         new Shopsys.spinbox.Window(self, {
-                             text: Shopsys.translator.trans(
+                         new Window(self, {
+                             text: Translator.trans(
                                  'Entered quantity <b>%value%</b> must be at least <b>%minimumQuantity%</b> and then divisible by <b>%stepSize%</b>.'
                              )
                          });
                      }
                  }
              )};
          }
      };
 
-     Shopsys.register.registerCallback(Shopsys.spinbox.init, Shopsys.register.CALL_PRIORITY_HIGH);
+     (new Register()).registerCallback(Shopsys.spinbox.init, Register.CALL_PRIORITY_HIGH);
 })(jQuery);
```

Or you can see full changes between your old file and new file
```sh
git diff master:src/Resources/scripts/frontend/spinbox.js assets/js/frontend/spinbox.js
```

- Update your `assets/js/frontend/validation/form/customer.js`
```diff
+ import constant from '../../utils/constant';

  (function ($) {
      $(document).ready(function () {
          $customerDeliveryAddressForm.jsFormValidator({
              'groups': function () {
-                 var groups = [Shopsys.constant('\\Shopsys\\FrameworkBundle\\Form\\ValidationGroup::VALIDATION_GROUP_DEFAULT')];
+                 var groups = [constant('\\Shopsys\\FrameworkBundle\\Form\\ValidationGroup::VALIDATION_GROUP_DEFAULT')];
                  if ($customerDeliveryAddressForm.find('#customer_form_deliveryAddressData_addressFilled').is(':checked')) {
-                     groups.push(Shopsys.constant('\\App\\Form\\Front\\Customer\\DeliveryAddressFormType::VALIDATION_GROUP_DIFFERENT_DELIVERY_ADDRESS'));
+                     groups.push(constant('\\App\\Form\\Front\\Customer\\DeliveryAddressFormType::VALIDATION_GROUP_DIFFERENT_DELIVERY_ADDRESS'));
                  }
                  return groups;
              }
          });
          $customerBillingAddressForm.jsFormValidator({
              'groups': function () {
-                 var groups = [Shopsys.constant('\\Shopsys\\FrameworkBundle\\Form\\ValidationGroup::VALIDATION_GROUP_DEFAULT')];
+                 var groups = [constant('\\Shopsys\\FrameworkBundle\\Form\\ValidationGroup::VALIDATION_GROUP_DEFAULT')];
                  if ($customerBillingAddressForm.find('#customer_form_billingAddressData_companyCustomer').is(':checked')) {
-                     groups.push(Shopsys.constant('\\App\\Form\\Front\\Customer\\BillingAddressFormType::VALIDATION_GROUP_COMPANY_CUSTOMER'));
+                     groups.push(constant('\\App\\Form\\Front\\Customer\\BillingAddressFormType::VALIDATION_GROUP_COMPANY_CUSTOMER'));
                  }
                   return groups;
              }
          });
      });
  })(jQuery);
```

Or you can see full changes between your old file and new file
```sh
git diff master:src/Resources/scripts/frontend/validation/form/customer.js assets/js/frontend/validation/form/customer.js
```

- Update your `assets/js/frontend/validation/form/order.js`
```diff
+ import constant from '../../utils/constant';

  (function ($) {
      $(document).ready(function () {
 
-         var $transportAndPaymentForm = $('#transport_and_payment_form');
+         var $transportAndPaymentForm = $('#transport_and_payment_form');
          $transportAndPaymentForm.jsFormValidator({
              callbacks: {
                  validateTransportPaymentRelation: function () {
              }
         });
 
-         var $orderPersonalInfoForm = $('form[name="order_personal_info_form"]');
+         var $orderPersonalInfoForm = window.$('form[name="order_personal_info_form"]');
          $orderPersonalInfoForm.jsFormValidator({
             'groups': function () {
 
-                 var groups = [Shopsys.constant('\\Shopsys\\FrameworkBundle\\Form\\ValidationGroup::VALIDATION_GROUP_DEFAULT')];
+                 var groups = [constant('\\Shopsys\\FrameworkBundle\\Form\\ValidationGroup::VALIDATION_GROUP_DEFAULT')];
                  if ($orderPersonalInfoForm.find('#order_personal_info_form_deliveryAddressFilled').is(':checked')) {
-                     groups.push(Shopsys.constant('\\App\\Form\\Front\\Customer\\DeliveryAddressFormType::VALIDATION_GROUP_DIFFERENT_DELIVERY_ADDRESS'));
+                     groups.push(constant('\\App\\Form\\Front\\Customer\\DeliveryAddressFormType::VALIDATION_GROUP_DIFFERENT_DELIVERY_ADDRESS'));
                  }
                  if ($orderPersonalInfoForm.find('#order_personal_info_form_companyCustomer').is(':checked')) {
-                     groups.push(Shopsys.constant('\\App\\Form\\Front\\Customer\\BillingAddressFormType::VALIDATION_GROUP_COMPANY_CUSTOMER'));
+                     groups.push(constant('\\App\\Form\\Front\\Customer\\BillingAddressFormType::VALIDATION_GROUP_COMPANY_CUSTOMER'));
                  }
                  return groups;
             }
          });
      });
  })(jQuery);
```

Or you can see full changes between your old file and new file
```sh
git diff master:src/Resources/scripts/frontend/validation/form/order.js assets/js/frontend/validation/form/order.js
```

- Update your `src/Controller/Front/OrderController.php`
```diff

    public function indexAction()
    {
        ...
        return $this->render('Front/Content/Order/index.html.twig', [
+           'paymentTransportRelations' => $this->getPaymentTransportRelations($payments),
        ]);
    }

+     /**
+     * @param \App\Model\Payment\Payment[] $payments
+     * @return string
+     */
+    private function getPaymentTransportRelations(array $payments): string
+    {
+        $relations = [];
+        foreach ($payments as $payment) {
+            foreach ($payment->getTransports() as $transport) {
+                $relations[] = [
+                    'paymentId' => $payment->getId(),
+                    'transportId' => $transport->getId(),
+                ];
+            }
+        }
+
+        return json_encode($relations);
+    }

```

- Update your `templates/Front/Content/Order/index.html.twig`
```diff
- {% block javascripts_bottom %}
-     {{ parent() }}
-     <script type="text/javascript">
-         {% for payment in payments %}
-             {% for transport in payment.transports %}
-                 Shopsys.order.addPaymentTransportRelation({{ payment.id }}, {{ transport.id }});
-             {% endfor %}
-         {% endfor %}
-     </script>
- {% endblock %}

  {% block main_content %}
      ...
+     <span class="js-payment-transport-relations" data-relations="{{ paymentTransportRelations }}"></span>
  {% endblock %}

```
