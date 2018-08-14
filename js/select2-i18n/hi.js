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

(function(){if(jQuery&&jQuery.fn&&jQuery.fn.select2&&jQuery.fn.select2.amd)var e=jQuery.fn.select2.amd;return e.define("select2/i18n/hi",[],function(){return{errorLoading:function(){return"परिणामों को लोड नहीं किया जा सका।"},inputTooLong:function(e){var t=e.input.length-e.maximum,n=t+" अक्षर को हटा दें";return t>1&&(n=t+" अक्षरों को हटा दें "),n},inputTooShort:function(e){var t=e.minimum-e.input.length,n="कृपया "+t+" या अधिक अक्षर दर्ज करें";return n},loadingMore:function(){return"अधिक परिणाम लोड हो रहे है..."},maximumSelected:function(e){var t="आप केवल "+e.maximum+" आइटम का चयन कर सकते हैं";return t},noResults:function(){return"कोई परिणाम नहीं मिला"},searching:function(){return"खोज रहा है..."}}}),{define:e.define,require:e.require}})();