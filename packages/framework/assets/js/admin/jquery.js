/*
 * Why?
 *
 * We temporarily use jsFormValidateBundle Bundle and we load it before webpack build file and this bundle needs jquery to register itself.
 *
 * We create jquery entry from this file in webpack config file and load it before jsFormValidateBundle.
 *
 * This will be deleted after updating jsFormValidateBundle
 *
 */

import $ from 'jquery';

global.$ = global.jQuery = $;
