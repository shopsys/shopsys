module.exports = function(grunt) {

    require('es6-promise').polyfill();
    require('jit-grunt')(grunt, {
        sprite: 'pngsmith'
    });
    require('time-grunt')(grunt);

    grunt.initConfig({

        pkg: grunt.file.readJSON('package.json'),

        less: {
            helpers1: {
                files: {
                    'src/Shopsys/ShopBundle/Resources/styles/front/common/helpers/helpers-generated.less': '../src/Shopsys/ShopBundle/Resources/styles/front/common/helpers.less'
                }
            },
            frontend1: {
                files: {
                    'web/assets/frontend/styles/index11.css': '../src/Shopsys/ShopBundle/Resources/styles/front/common/main.less'
                },
                options: {
                    compress: true,
                    sourceMap: true,
                    sourceMapFilename: 'web/assets/frontend/styles/index11.css.map',
                    sourceMapBasepath: 'web',
                    sourceMapURL: 'index11.css.map',
                    sourceMapRootpath: '../../../'
                }
            },
            print1: {
                files: {
                    'web/assets/frontend/styles/print1.css': '../src/Shopsys/ShopBundle/Resources/styles/front/common/print/main.less'
                },
                options: {
                    compress: true
                }
            },
            wysiwyg1: {
                files: {
                    'web/assets/admin/styles/wysiwyg1.css': '../src/Shopsys/ShopBundle/Resources/styles/front/common/wysiwyg.less'
                },
                options: {
                    compress: true
                }
            },
            wysiwygLocalized1: {
                files: {
                    'web/assets/admin/styles/wysiwyg-localized1.css': '../src/Shopsys/ShopBundle/Resources/styles/admin//wysiwyg-localized.less'
                },
                options: {
                    compress: true
                }
            }
        },

        postcss: {
            options: {
                processors: [
                    require('autoprefixer')({browsers: ['last 3 versions', 'ios 6', 'Safari 7', 'Safari 8', 'ie 7', 'ie 8', 'ie 9']})
                ]
            },
            dist: {
                src: ['web/assets/frontend/styles/*.css', 'web/assets/admin/styles/*.css']
            }
        },

        legacssy: {
            frontend1: {
                options: {
                    legacyWidth: 1200,
                    matchingOnly: false,
                    overridesOnly: false
                },
                files: {
                    'web/assets/frontend/styles/index11-ie8.css': 'web/assets/frontend/styles/index11.css'
                }
            }
        },

        sprite: {
            frontend: {
                src: '../web/assets/frontend/images/icons/*.png',
                dest: '../web/assets/frontend/images/sprites/sprite.png',
                destCss: 'src/Shopsys/ShopBundle/Resources/styles/front/common/libs/sprites.less',
                imgPath: '../images/sprites/sprite.png?v=' + (new Date().getTime()),
                algorithm: 'binary-tree',
                padding: 50,
                cssFormat: 'css',
                cssVarMap: function (sprite) {
                    sprite.name = 'sprite.sprite-' + sprite.name;
                },
                engineOpts: {
                    imagemagick: true
                },
                imgOpts: {
                    format: 'png',
                    quality: 90,
                    timeout: 10000
                },
                cssOpts: {
                    functions: false,
                    cssClass: function (item) {
                        return '.' + item.name;
                    },
                    cssSelector: function (sprite) {
                        return '.' + sprite.name;
                    }
                }
            }
        },

        webfont: {
            frontend: {
                src: '../src/Shopsys/ShopBundle/Resources/svg/front/*.svg',
                dest: 'web/assets/frontend/fonts',
                destCss: 'src/Shopsys/ShopBundle/Resources/styles/front/common/libs/',
                options: {
                    autoHint: false,
                    font: 'svg',
                    hashes: true,
                    types: 'eot,woff,ttf,svg',
                    engine: 'node',
                    stylesheet: 'less',
                    relativeFontPath: '../fonts',
                    fontHeight: '512',
                    descent: '0',
                    destHtml: 'docs/generated',
                    htmlDemo: true,
                    htmlDemoTemplate: '../src/Shopsys/ShopBundle/Resources/views/Grunt/htmlDocumentTemplate.html',
                    templateOptions: {
                        baseClass: 'svg',
                        classPrefix: 'svg-',
                        mixinPrefix: 'svg-'
                    }
                }
            }
        },

        watch: {
            frontendSprite: {
                files: ['../web/assets/frontend/images/icons/**/*.png'],
                tasks: ['sprite:frontend'],
                options: {
                    livereload: true
                }
            },
            frontendWebfont: {
                files: ['../src/Shopsys/ShopBundle/Resources/svg/front/*.svg'],
                tasks: ['webfont:frontend']
            },
            frontend1: {
                files: [
                    '../src/Shopsys/ShopBundle/Resources/styles/front/common/**/*.less',
                    '!../src/Shopsys/ShopBundle/Resources/styles/front/common/helpers.less',
                    '!../src/Shopsys/ShopBundle/Resources/styles/front/common/helpers/*.less',
                    '../src/Shopsys/ShopBundle/Resources/styles/front/common/helpers/helpers-generated.less',
                    '!../src/Shopsys/ShopBundle/Resources/styles/front/common/core/mixin/*.less',
                    '../src/Shopsys/ShopBundle/Resources/styles/front/common/core/mixin/base.less'
                ],
                tasks:['frontendLess1']
            },
            helpers1: {
                files: [
                    '../src/Shopsys/ShopBundle/Resources/styles/front/common/helpers.less',
                    '../src/Shopsys/ShopBundle/Resources/styles/front/common/helpers/*.less',
                    '!../src/Shopsys/ShopBundle/Resources/styles/front/common/helpers/helpers-generated.less',
                    '../src/Shopsys/ShopBundle/Resources/styles/front/common/core/mixin/*.less',
                    '!../src/Shopsys/ShopBundle/Resources/styles/front/common/core/mixin/base.less'
                ],
                tasks:['less:helpers1']
            },
            frontend2: {
                    files: [
                        '../src/Shopsys/ShopBundle/Resources/styles/front/domain2/**/*.less',
                        '!../src/Shopsys/ShopBundle/Resources/styles/front/domain2/helpers.less',
                        '!../src/Shopsys/ShopBundle/Resources/styles/front/domain2/helpers/*.less',
                        '../src/Shopsys/ShopBundle/Resources/styles/front/domain2/helpers/helpers-generated.less',
                        '!../src/Shopsys/ShopBundle/Resources/styles/front/domain2/core/mixin/*.less',
                        '../src/Shopsys/ShopBundle/Resources/styles/front/domain2/core/mixin/base.less'
                    ],
                    tasks:['frontendLess2']
            },
            helpers2: {
                files: [
                    '../src/Shopsys/ShopBundle/Resources/styles/front/domain2/helpers.less',
                    '../src/Shopsys/ShopBundle/Resources/styles/front/domain2/helpers/*.less',
                    '!../src/Shopsys/ShopBundle/Resources/styles/front/domain2/helpers/helpers-generated.less',
                    '../src/Shopsys/ShopBundle/Resources/styles/front/domain2/core/mixin/*.less',
                    '!../src/Shopsys/ShopBundle/Resources/styles/front/domain2/core/mixin/base.less'
                ],
                tasks:['frontendLess2']
            },

            livereload: {
                options: {
                    livereload: true
                },
                files: ['../web/assets/frontend/styles/*.css']
            },

            twig: {
                files: ['../src/Shopsys/ShopBundle/Resources/views/**/*.twig'],
                tasks: [],
                options: {
                    livereload: true,
                }
            },

            html: {
                files: ['*.html'],
                tasks: [],
                options: {
                    livereload: true,
                }
            }
        }
    });
    grunt.loadNpmTasks('grunt-spritesmith');

    grunt.registerTask('default', ["sprite:frontend", "webfont", "less", "postcss", "legacssy"]);

    grunt.registerTask('frontend1', ['webfont:frontend', 'sprite:frontend', 'less:frontend1', 'less:print1', 'legacssy:frontend1', 'less:wysiwyg1'], 'postcss');

    grunt.registerTask('frontendLess1', ['less:frontend1', 'legacssy:frontend1', 'less:print1', 'less:wysiwyg1']);
};
