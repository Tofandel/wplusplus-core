/*
 * Copyright (c) Adrien Foulon - 2018.
 * Licensed under the Apache License, Version 2.0
 * http://www.apache.org/licenses/LICENSE-2.0
 */

(function(){if(jQuery&&jQuery.fn&&jQuery.fn.select2&&jQuery.fn.select2.amd)var e=jQuery.fn.select2.amd;return e.define("select2/i18n/hy",[],function(){return{errorLoading:function(){return"Արդյունքները հնարավոր չէ բեռնել։"},inputTooLong:function(e){var t=e.input.length-e.maximum,n="Խնդրում ենք հեռացնել "+t+" նշան";return n},inputTooShort:function(e){var t=e.minimum-e.input.length,n="Խնդրում ենք մուտքագրել "+t+" կամ ավել նշաններ";return n},loadingMore:function(){return"Բեռնվում են նոր արդյունքներ․․․"},maximumSelected:function(e){var t="Դուք կարող եք ընտրել առավելագույնը "+e.maximum+" կետ";return t},noResults:function(){return"Արդյունքներ չեն գտնվել"},searching:function(){return"Որոնում․․․"}}}),{define:e.define,require:e.require}})();