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

(function(){if(jQuery&&jQuery.fn&&jQuery.fn.select2&&jQuery.fn.select2.amd)var e=jQuery.fn.select2.amd;return e.define("select2/i18n/fa",[],function(){return{errorLoading:function(){return"امکان بارگذاری نتایج وجود ندارد."},inputTooLong:function(e){var t=e.input.length-e.maximum,n="لطفاً "+t+" کاراکتر را حذف نمایید";return n},inputTooShort:function(e){var t=e.minimum-e.input.length,n="لطفاً تعداد "+t+" کاراکتر یا بیشتر وارد نمایید";return n},loadingMore:function(){return"در حال بارگذاری نتایج بیشتر..."},maximumSelected:function(e){var t="شما تنها می‌توانید "+e.maximum+" آیتم را انتخاب نمایید";return t},noResults:function(){return"هیچ نتیجه‌ای یافت نشد"},searching:function(){return"در حال جستجو..."}}}),{define:e.define,require:e.require}})();