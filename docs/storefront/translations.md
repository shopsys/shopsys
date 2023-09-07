# Translations

For managing translations we use the `i18n` library with Next.js integration `next-translate`.

All available translations you can find under `/project-base/storefront/public/locales`.

## Add new translation

Wherever you want to use a translated string you first need to import the `useTranslation` hook from `next-translate`.

```ts
import useTranslation from "next-translate/useTranslation";
```

then you are able to use it in component/hook

```tsx
const TranslatedStringComponent: FC = () => {
    const { t } = useTranslation();

    return <div>{t("translated")}</div>;
};
```

After you put `t('translated')` to the code, the translation parser is able to find the string and generate translations for other languages. Now run

```bash
pnpm translate
```

This will generate a new translation key (in our case key `translated`) in every language file `/project-base/storefront/public/locales/{language_code}/common.json`.

Now you can open each language file and fill in the proper translation string to the new key pair.
