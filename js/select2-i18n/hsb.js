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

(function(){if(jQuery&&jQuery.fn&&jQuery.fn.select2&&jQuery.fn.select2.amd)var e=jQuery.fn.select2.amd;return e.define("select2/i18n/hsb",[],function(){var e=["znamješko","znamješce","znamješka","znamješkow"],t=["zapisk","zapiskaj","zapiski","zapiskow"],n=function(t,n){if(t===1)return n[0];if(t===2)return n[1];if(t>2&&t<=4)return n[2];if(t>=5)return n[3]};return{errorLoading:function(){return"Wuslědki njedachu so začitać."},inputTooLong:function(t){var r=t.input.length-t.maximum;return"Prošu zhašej "+r+" "+n(r,e)},inputTooShort:function(t){var r=t.minimum-t.input.length;return"Prošu zapodaj znajmjeńša "+r+" "+n(r,e)},loadingMore:function(){return"Dalše wuslědki so začitaja…"},maximumSelected:function(e){return"Móžeš jenož "+e.maximum+" "+n(e.maximum,t)+"wubrać"},noResults:function(){return"Žane wuslědki namakane"},searching:function(){return"Pyta so…"}}}),{define:e.define,require:e.require}})();