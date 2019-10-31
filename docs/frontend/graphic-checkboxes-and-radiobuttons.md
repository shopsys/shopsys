# Graphic checkboxes and radiobuttons
For replacing native checkboxes and radiobuttons we use image background, which has six positions:

    - normal (top, left)
    - normal + hover (top, center)
    - normal + disabled (top, right)
    - checked (bottom, left)
    - checked + hover (bottom, center)
    - checked + disabled (bottom, right)

## Html structure
Html structure is easy and has only few rules. Native input is hidden by class `css-checkbox` and directly after this input we need to add label with class `css-checkbox__image`, which represents image. Between this tags can't be any other tags. It is because input gives to span statuses like `checked` and according this it shows background on current position.

Graphic checkboxes
```Html
    <input type="checkbox" id="[input_id]" name="[input_name]" class="css-checkbox" value="1">
    <label class="css-checkbox__image" for="[input_id]">
        I agree with privacy policy.
    </label>
```

Graphic radiobuttons
```Html
    <input type="radio" id="[input_id]" name="[input_name]" class="css-radio" value="1">
    <label class="css-radio__image" for="[input_id]">
        I agree with privacy policy.
    </label>
```

## Background image template
We prepared PSD file for create custom background image. You can download it (./frontend/custom_checkbox_ssfw.psd). For editing all colors, borders and radiuses you can upload it to (https://www.photopea.com/).

As you can see at layer structure, there are radiobuttons and checkboxes separately. Just make visible part that you want, make your changes and export as png file and save as original file:

```
 `web/assets/frontend/images/custom_checkbox.png`
 `web/assets/frontend/images/custom_radio.png`
```

Background image contains six squares with 74x74px size. This allows you to have nice graphic inputs on higher pixel ratio devices.
