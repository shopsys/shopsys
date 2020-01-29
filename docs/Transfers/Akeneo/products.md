# Přenosy : Akeneo

## Produkty

### Info
| API         | Info                                |
|:------------|:------------------------------------|
| Směr        | Akeneo => Eshop                     |
| CronModule  | ```AkeneoImportProductCronModule``` |
| API metoda  | ```getPublishedProductApi```        |
| Frekvence   | co 5 minut                          |
| Typ přenosu | dávkově                             |

### Vlastnosti přenosu
| Pole eshop                | Pole Akeneo                 | Poznámka                      |
|:--------------------------|:----------------------------|:------------------------------|
| ProductData->catnum       | ```[identifier]```          | používá se jako unikátní klíč |
| ProductData->hidden       | ```[enabled]```             | Skrývá / odkrývá produkt      |
| ProductData->ean          | ```[values][ean]```         |                               |
| ProductData->namePrefix   | ```[values][name_prefix]``` |                               |
| ProductData->name         | ```[values][name]```        |                               |
| ProductData->nameSufix    | ```[values][name_sufix]```  |                               |
| ProductData->descriptions | ```[values][description]``` |                               |
| ProductData->usp1         | ```[values][usp1]```        |                               |
| ProductData->usp2         | ```[values][usp2]```        |                               |
| ProductData->usp3         | ```[values][usp3]```        |                               |
| ProductData->usp4         | ```[values][usp4]```        |                               |
| ProductData->usp5         | ```[values][usp5]```        |                               |

### Dobré vědět
Když není v Akeneu nastavena žádná hodnota u atributu
není v datech v rámci API přenosu přenášen atribut.

Z přenesených dat se nastaví interně poslední datum modifikace z pole ```[updated]```
a následné volání dalšího požadavku na přenos je filtrováno ```updated``` > námi evidovan čas.

Datum se vrací z Akenea s časovou zonou, ale směrem k API se volá v UTC.
