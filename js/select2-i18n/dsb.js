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

(function(){if(jQuery&&jQuery.fn&&jQuery.fn.select2&&jQuery.fn.select2.amd)var e=jQuery.fn.select2.amd;return e.define("select2/i18n/dsb",[],function(){var e=["znamuško","znamušce","znamuška","znamuškow"],t=["zapisk","zapiska","zapiski","zapiskow"],n=function(t,n){if(t===1)return n[0];if(t===2)return n[1];if(t>2&&t<=4)return n[2];if(t>=5)return n[3]};return{errorLoading:function(){return"Wuslědki njejsu se dali zacytaś."},inputTooLong:function(t){var r=t.input.length-t.maximum;return"Pšosym lašuj "+r+" "+n(r,e)},inputTooShort:function(t){var r=t.minimum-t.input.length;return"Pšosym zapódaj nanejmjenjej "+r+" "+n(r,e)},loadingMore:function(){return"Dalšne wuslědki se zacytaju…"},maximumSelected:function(e){return"Móžoš jano "+e.maximum+" "+n(e.maximum,t)+"wubraś."},noResults:function(){return"Žedne wuslědki namakane"},searching:function(){return"Pyta se…"}}}),{define:e.define,require:e.require}})();