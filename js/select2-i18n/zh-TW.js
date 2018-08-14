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

(function(){if(jQuery&&jQuery.fn&&jQuery.fn.select2&&jQuery.fn.select2.amd)var e=jQuery.fn.select2.amd;return e.define("select2/i18n/zh-TW",[],function(){return{inputTooLong:function(e){var t=e.input.length-e.maximum,n="請刪掉"+t+"個字元";return n},inputTooShort:function(e){var t=e.minimum-e.input.length,n="請再輸入"+t+"個字元";return n},loadingMore:function(){return"載入中…"},maximumSelected:function(e){var t="你只能選擇最多"+e.maximum+"項";return t},noResults:function(){return"沒有找到相符的項目"},searching:function(){return"搜尋中…"}}}),{define:e.define,require:e.require}})();