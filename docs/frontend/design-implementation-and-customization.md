# Design Implementation and Customization

Here are the basic technologies we use in Shopsys Platform for design implementation:

- [LESS pre-processor](http://lesscss.org/) for definition of cascading style sheets (i.e. [CSS](https://www.w3.org/Style/CSS/Overview.en.html))
    - the LESS files are located in `assets/styles/frontend`
    - you can read more about LESS in separate article [Introduction to Less](./introduction-to-less.md)
- [Rsbuild](https://rsbuild.rs/) for svg optimize, font gemerator and less compiler
- [Twig templating engine](https://twig.symfony.com/) for definition of HTML (and other) templates
    - the Twig templates are located in `templates` directory

When you want to customize the styles or templates, you can modify any of the files directly, as all of them are located in your project.
