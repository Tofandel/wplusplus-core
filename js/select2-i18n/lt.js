/*
 * Copyright (c) Adrien Foulon - 2018. All rights reserved.
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/*! Select2 4.0.6-rc.1 | https://github.com/select2/select2/blob/master/LICENSE.md */

(function(){if(jQuery&&jQuery.fn&&jQuery.fn.select2&&jQuery.fn.select2.amd)var e=jQuery.fn.select2.amd;return e.define("select2/i18n/lt",[],function(){function e(e,t,n,r){return e%10===1&&(e%100<11||e%100>19)?t:e%10>=2&&e%10<=9&&(e%100<11||e%100>19)?n:r}return{inputTooLong:function(t){var n=t.input.length-t.maximum,r="Pašalinkite "+n+" simbol";return r+=e(n,"į","ius","ių"),r},inputTooShort:function(t){var n=t.minimum-t.input.length,r="Įrašykite dar "+n+" simbol";return r+=e(n,"į","ius","ių"),r},loadingMore:function(){return"Kraunama daugiau rezultatų…"},maximumSelected:function(t){var n="Jūs galite pasirinkti tik "+t.maximum+" element";return n+=e(t.maximum,"ą","us","ų"),n},noResults:function(){return"Atitikmenų nerasta"},searching:function(){return"Ieškoma…"}}}),{define:e.define,require:e.require}})();