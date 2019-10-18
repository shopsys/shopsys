# How to override admin styles from project-base

Add new less files according to style folders -
[Understanding the Style Directory](./understanding-the-style-directory.md)
to folder `src/Shopsys/ShopBundle/Resources/styles/admin/` and import them in `src/Shopsys/ShopBundle/Resources/styles/admin/main.less` file.

Don't forget to re-run `php phing grunt` after changes in `main.less`.
