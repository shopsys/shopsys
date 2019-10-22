# Shopsys Framework styleguide

## Documentation
styleguide - tool to make creating and maintaining styleguides easy for Shopsys Framework designs.
Contains all html elements necessary to create new design.

## Installation to runing SSFW project
```
1) clone or copy content of repository to your SSFW project base
- so your final folder structure will be xxx/ssfw-shop/styleguide/*.*
2) project have to be ready and successfully build
3) in cmd run: npm install
4) in cmd run: grunt
5) open /styleguide/styleguide.html file in browser
```

## Installation standalone without runing SSFW project
```
1) clone or copy content of repository to your folder e.g. "ssfw-styleguide"
2) unzip file styleguide_demo_data.zip to your folder "ssfw-styleguide"
3) your folder should contain 3 new folders (ssfw-styleguide/docs, ssfw-styleguide/src, ssfw-styleguide/web)
4) in folder ssfw-styleguide run "npm install" in command line
5) in folder ssfw-styleguide run "grunt" in command line
6) open /ssfw-styleguide/styleguide.html file in browser
```

### How to add new information

File styleguide.html is simply html file.
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
### Add JS from project
You can find minimum JS files needed for styleguide use in folder: src/Shopsys/ShopBundle/Resources/scripts/. In frontend folder you can find JS files used directly at frontend.
If you want to add new JS file from frontend, just copy file to this folder and add it on bottom of styleguide.html file manually. There is no autoload function.

### Note
Colors in styleguide content are NOT loaded from LESS files of SSFW project )-; And need to be added manually.

### Inspired by
<a href="https://hugeinc.github.io/styleguide/">https://hugeinc.github.io/styleguide/</a>
