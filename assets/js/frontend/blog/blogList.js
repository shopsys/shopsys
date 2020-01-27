import $ from 'jquery';
import Register from 'framework/common/utils/register';
import AjaxMoreLoader from '../components/ajaxMoreLoader';
import Translator from 'bazinga-translator';

export default function blogList ($container) {
    $container.filterAllNodes('.js-blog-list-with-paginator').each(function () {
        // eslint-disable-next-line no-new
        new AjaxMoreLoader($(this), {
            buttonTextCallback: function (loadNextCount) {
                return Translator.transChoice(
                    '{1}Načíst další %loadNextCount% článek|[2,4]Načíst další %loadNextCount% články|[5,Inf]Načíst dalších %loadNextCount% článků',
                    loadNextCount,
                    { '%loadNextCount%': loadNextCount }
                );
            }
        });
    });
}

(new Register()).registerCallback(blogList);
