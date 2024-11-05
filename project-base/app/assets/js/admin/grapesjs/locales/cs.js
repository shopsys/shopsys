export const cs = {
    assetManager: {
        addButton: 'Přidat obrázek',
        inputPlh: 'https://path/to/the/image.jpg',
        modalTitle: 'Vyberte obrázek',
        uploadTitle: 'Zde přetáhněte obrázky nebo kliknětě na upload'
    },
    // Here just as a reference, GrapesJS core doesn't contain any block,
    // so this should be omitted from other local files
    blockManager: {
        labels: {
            // 'block-id': 'Block Label',
        },
        categories: {
            // 'category-id': 'Category Label',
        }
    },
    domComponents: {
        names: {
            '': 'Box',
            wrapper: 'Obal',
            text: 'Text',
            comment: 'Komentář',
            image: 'Obrázek',
            video: 'Video',
            label: 'Popisek',
            link: 'Odkaz',
            map: 'Mapa',
            tfoot: 'Patička tabulky',
            tbody: 'Tělo tabulky',
            thead: 'Hlavička tabulky',
            table: 'Tabulka',
            row: 'Řádek tabulky',
            cell: 'Buňka tabulky'
        }
    },
    deviceManager: {
        device: 'Zařízení',
        devices: {
            desktop: 'Desktop',
            tablet: 'Tablet',
            mobileLandscape: 'Mobil naležato',
            mobilePortrait: 'Mobil nastojato'
        }
    },
    panels: {
        buttons: {
            titles: {
                preview: 'Náhled',
                fullscreen: 'Celá obrazovka',
                'sw-visibility': 'Komponenty',
                'export-template': 'Zobrazit kód',
                'open-sm': 'Otevřít editor stylů',
                'open-tm': 'Nastavení',
                'open-layers': 'Otevřit editor vrstev',
                'open-blocks': 'Otevřít bloky',
                undo: 'Zpět',
                redo: 'Znovu',
                save: 'Uložit',
                close: 'Zavřít',
                cmdClear: 'Zahodit změny',
                cmdTglImages: 'Vypnout/Zaponout obrázky',
                cmdOpenImport: 'Importovat obsah',
                activateOutline: 'Komponenty'
            }
        },
        options: {
            devices: {
                cmdDeviceDesktop: 'Desktop',
                cmdDeviceTablet: 'Tablet',
                cmdDeviceMobile: 'Mobil'
            }
        }
    },
    selectorManager: {
        label: 'Třídy',
        selected: 'Vybráno',
        emptyState: '- Stav -',
        states: {
            hover: 'Přejetí myší',
            active: 'Kliknutí',
            'nth-of-type(2n)': 'Sudý/Lichý'
        }
    },
    styleManager: {
        empty: 'Před použitím Editoru stylů vyberte element',
        layer: 'Vrstva',
        fileButton: 'Obrázky',
        sectors: {
            general: 'Obecné',
            layout: 'Vrstva',
            typography: 'Typografie',
            decorations: 'Dekorace',
            extra: 'Extra',
            flex: 'Flex',
            dimension: 'Rozměry'
        },
        // Default names for sub properties in Composite and Stack types.
        // Other labels are generated directly from their property names (eg. 'font-size' will be 'Font size').
        properties: {
            'text-shadow-h': 'X',
            'text-shadow-v': 'Y',
            'text-shadow-blur': 'Rozmazání',
            'text-shadow-color': 'Barva',
            'box-shadow-h': 'X',
            'box-shadow-v': 'Y',
            'box-shadow-blur': 'Rozmazání',
            'box-shadow-spread': 'Roztažení',
            'box-shadow-color': 'Barva',
            'box-shadow-type': 'Typ',
            'margin-top-sub': 'Nahoře',
            'margin-right-sub': 'Vpravo',
            'margin-bottom-sub': 'Dole',
            'margin-left-sub': 'Vlevo',
            'padding-top-sub': 'Nahoře',
            'padding-right-sub': 'Vpravo',
            'padding-bottom-sub': 'Dole',
            'padding-left-sub': 'Vlevo',
            'border-width-sub': 'Šířka',
            'border-style-sub': 'Styl',
            'border-color-sub': 'Barva',
            'border-top-left-radius-sub': 'Levý horní',
            'border-top-right-radius-sub': 'Pravý horní',
            'border-bottom-right-radius-sub': 'Pravý dolní',
            'border-bottom-left-radius-sub': 'Levý dolní',
            'transform-rotate-x': 'Rotace X',
            'transform-rotate-y': 'Rotace Y',
            'transform-rotate-z': 'Rotace Z',
            'transform-scale-x': 'Měřítko X',
            'transform-scale-y': 'Měřítko Y',
            'transform-scale-z': 'Měřítko Z',
            'transition-property-sub': 'Vlastnost',
            'transition-duration-sub': 'Trvání',
            'transition-timing-function-sub': 'Časování',
            'background-image-sub': 'Obrázek',
            'background-repeat-sub': 'Opakování',
            'background-position-sub': 'Pozice',
            'background-attachment-sub': 'Přichycení',
            'background-size-sub': 'Velikost'
        }
        // Translate options in style properties
        // options: {
        //   float: { // Id of the property
        //     ...
        //     left: 'Left', // {option id}: {Option label}
        //   }
        // }
    },
    traitManager: {
        empty: 'Před použitím editoru vlastností vyberte element',
        label: 'Nastavení komponenty',
        traits: {
            // The core library generates the name by their `name` property
            labels: {
                id: 'Id',
                alt: 'Alt',
                title: 'Titulek',
                href: 'Odkaz',
                target: 'Cíl',
                provider: 'Poskytovatel',
                src: 'Url',
                poster: 'Náhled',
                autoplay: 'Autoplay',
                loop: 'Opakovat',
                controls: 'Ovládání',
                startfrom: 'Začátek',
                endText: 'Text po ukončení'
            },
            // In a simple trait, like text input, these are used on input attributes
            /*
            attributes: {
                id: traitInputAttr,
                alt: traitInputAttr,
                title: traitInputAttr,
                href: { placeholder: 'eg. https://google.com' }
            },
            */
            // In a trait like select, these are used to translate option names
            options: {
                target: {
                    false: 'Toto okno',
                    _blank: 'Nové okno'
                }
            }
        }
    },
    storageManager: {
        recover: 'Chcete obnovit neuložené změny?'
    }
};
