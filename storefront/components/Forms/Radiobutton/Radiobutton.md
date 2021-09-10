```jsx
import { FormProvider, useForm } from 'react-hook-form';

const formProviderMethods = useForm({
    defaultValues: {
        radiobuttonGroup: 'selected',
        radiobuttonGroupDisabled: 'selected',
        radiobuttonGroupWithImages: 'selected',
    },
});

<FormProvider {...formProviderMethods}>
    <form>
        <div style={{ width: '350px' }}>
            <ShopsysRadiobutton
                id="my-form_radiobutton-selected"
                name="radiobuttonGroup"
                label="Selected"
                value="selected"
            />
            <ShopsysRadiobutton
                id="my-form_radiobutton"
                name="radiobuttonGroup"
                label="Unselected"
                value="not-selected"
            />
            <ShopsysRadiobutton
                id="my-form_radiobutton-disabled-selected"
                name="radiobuttonGroupDisabled"
                label="Selected disabled"
                value="selected"
                disabled={true}
            />
            <ShopsysRadiobutton
                id="my-form_radiobutton-disabled"
                name="radiobuttonGroupDisabled"
                label="Unselected disabled"
                value="not-selected"
                disabled={true}
            />
            <ShopsysRadiobutton
                id="my-form_radiobutton-with-images-selected"
                name="radiobuttonGroupWithImages"
                label="Selected with image"
                image="/images/czech_post.png"
                value="selected"
            />
            <ShopsysRadiobutton
                id="my-form_radiobutton-with-images"
                name="radiobuttonGroupWithImages"
                label="Unselected with image"
                image="/images/czech_post.png"
                value="not-selected"
            />
        </div>
    </form>
</FormProvider>;
```
