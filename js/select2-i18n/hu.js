/*
 * Copyright (c) Adrien Foulon - 2018.
 * Licensed under the Apache License, Version 2.0
 * http://www.apache.org/licenses/LICENSE-2.0
 */

(function(){if(jQuery&&jQuery.fn&&jQuery.fn.select2&&jQuery.fn.select2.amd)var e=jQuery.fn.select2.amd;return e.define("select2/i18n/hu",[],function(){return{errorLoading:function(){return"Az eredmények betöltése nem sikerült."},inputTooLong:function(e){var t=e.input.length-e.maximum;return"Túl hosszú. "+t+" karakterrel több, mint kellene."},inputTooShort:function(e){var t=e.minimum-e.input.length;return"Túl rövid. Még "+t+" karakter hiányzik."},loadingMore:function(){return"Töltés…"},maximumSelected:function(e){return"Csak "+e.maximum+" elemet lehet kiválasztani."},noResults:function(){return"Nincs találat."},searching:function(){return"Keresés…"}}}),{define:e.define,require:e.require}})();