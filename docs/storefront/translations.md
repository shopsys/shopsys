# Translations

For managing the translations, we use `i18n` library with the Next.js integration package `next-translate`.

All available translations can be found under `/project-base/storefront/public/locales`.

## Add new translation

Wherever you want to use translated string, you first need to import the `useTranslation` hook from `next-translate`(if you are inside a component) or `getT` if you are in a server-side function, such as `getServerSideProps`.

```ts
import useTranslation from 'next-translate/useTranslation';
```

then you are able to use it in a component/hook

```tsx
const TranslatedStringComponent: FC = () => {
  const { t } = useTranslation();

  return <div>{t('translated')}</div>;
};
```

After you put `t('translated')` in the code, the translation parser is able to find the string and generate translations for other languages. Now run

```bash
pnpm translate
```

This will generate a new translation key (in our case key `translated`) in every language file `/project-base/storefront/public/locales/{language_code}/common.json`.

Now, you can open each language file and fill in the proper translation strings for the new key pair. The parsing is done using `i18next-parser`.
