<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use Fp\JsFormValidatorBundle\Twig\Extension\JsFormValidatorTwigExtension as BaseJsFormValidatorTwigExtension;

class JsFormValidatorTwigExtension extends BaseJsFormValidatorTwigExtension
{
    /**
     * @inheritDoc
     */
    public function getJsValidator($form = null, $onLoad = true, $wrapped = true)
    {
        // onLoad can not be registered in the jsModel, because following listener below is called after onLoad event
        $jsModels = parent::getJsValidator($form, false, false);
        if ($jsModels === '') {
            return '';
        }

        // Following addListener is copy-paste from FpJsFormValidator.js
        $jsModels = '
            (function () {
                var runJsModel = function () {' . $jsModels . '};
                if (typeof FpJsFormValidator !== "undefined" ) {
                    runJsModel();
                } else {
                    var addListener = document.addEventListener || document.attachEvent;
                    var removeListener = document.removeEventListener || document.detachEvent;
                    var eventName = document.addEventListener ? "DOMContentLoaded" : "onreadystatechange";
                    addListener.call(document, eventName, function (callee) {
                        removeListener.call(this, eventName, callee, false);
                        runJsModel();
                    }, false);
                }
            })();';

        if ($wrapped) {
            $jsModels = '<script type="text/javascript">' . $jsModels . '</script>';
        }

        return $jsModels;
    }
}
