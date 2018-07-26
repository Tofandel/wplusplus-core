CodeMirror Code Editor Extension for Redux Framework
---
**CodeMirror** is a versatile text editor implemented in JavaScript for the browser. It is specialized for editing code, and comes with a number of language modes and addons that implement more advanced editing functionality.

A rich programming API and a CSS theming system are available for customizing CodeMirror to fit your application, and extending it with new functionality.

###Features
- Support for over 60 languages out of the box
- A powerful, composable language mode system
- Autocompletion (XML)
- Code folding
- Configurable keybindings
- Vim and Emacs bindings
- Search and replace interface
- Bracket and tag matching
- Support for split views
- Linter integration
- Mixing font sizes and styles
- Various themes 
- Able to resize to fit content
- Inline and block widgets
- Programmable gutters
- Making ranges of text styled, read-only, or atomic
- Bi-directional text support

Many other methods and addons...

###Browser support
The desktop versions of the following browsers, in standards mode (HTML5 <!doctype html> recommended) are supported:

* Firefox    version 3 and up
* Chrome    any version
* Safari	version 5.2 and up
* Internet Explorer	version 8 and up
* Opera	version 9 and up

Modern mobile browsers tend to partly work. Bug reports and patches for mobile support are welcome, but the - maintainer does not have the time or budget to actually work on it himself.

### Basic Usage
The basic Codemirror Editor for Redux Framework is added like the other fields with this parameters:

```php
    'fields' => array(
    array(
        'id' => "codemirror",
        'type' => "codemirror",
        'title' => __("Code Editor CSS", 'redux-framework-demo'),
        'compiler' => 'true',
        'subtitle' => __('Dummy Subtitle', 'redux-framework-demo'),
        'editor_options' => array(
            "mode" => "css",
            "addon" => array("foldcode-css","activeline"),
            "theme" => "monokai",
            "lint" => true,
            "hint" => true,
            "autohint" => true
	    )
    ),
    .
    .
    .
```

id 
: (string) The standard "id" field as in other fields, which makes the field unique.

type
: (string) This tells the framework that this field is a "codemirror" editor field.

title 
: (string) The title shown before the field.

compiler 
: (boolean) Whether to activate the compiler hook or not.

subtitle 
: (string) Description text under the field title.

editor_options 
: (array) contains the options that defines the properties of the editor.

    mode 
    : (string/array) defines the editor mode like CSS,Javascript, CoffeeScript, Markdown etc. For available modes see __Language Modes__ section of this document.
    
    addon 
    : (array) defines the editor's addons like search, markselection, rulers etc. For available addons, refer to __Add-ons__ section of this document.
    
    theme 
    : (string) defines the editor's color scheme. Available themes can be found in the **Themes** section.
    
    lint 
    : (boolean) enables the lint for the mode specified in `mode` option. If any linter is available for this mode, it'll add **_gutters_** nearby the line numbers and tells you _what's wrong about your code_. 
    
    hint 
    : (boolean) enables the **_autocomplete_** feature for the mode specified. 
If any autocomplete list is available for the selected `mode`, the editor will show a combobox filled with appropiate values for you. If the `autohint` mode is set to `false`, the trigger of this autocomplete combobox is `Ctrl+Space`.
    
    autolint 
    : (boolean) sets the autocomplete to show on every key stroke in the editor box. You don't have to use `Ctrl+Space` every time you want to autocomplete the code, just writing a few letters would make you code faster.
    
    
###Language Modes

This is a list of every mode in the distribution. Each mode lives in a subdirectory of the mode/ directory, and typically defines a single JavaScript file that implements the mode. Loading such file will make the language available to CodeMirror, through the mode option. This extension has the autoloading feature of those files, which searches for defined mode directory and autoloads neccessary files.

| | | | | | | | | |
|-|-|-|-|-|-|-|-|-|
|APL<br><small>(mode:apl)</small> | Asterisk dialplan<br><small>(mode:asterisk)</small> | C, C++, C#<br><small>(mode:clike)</small> | Clojure<br><small>(mode:clojure)</small> | COBOL<br><small>(mode:cobol)</small> | CoffeeScript<br><small>(mode:coffeescript)</small> | Common Lisp<br><small>(mode:commonlisp)</small> | CSS<br><small>(mode:css)</small> | 
|Cython<br><small>(mode:phyton)</small> | D<br><small>(mode:d)</small> | diff<br><small>(mode:diff)</small> | DTD<br><small>(mode:dtd)</small> |ECL<br><small>(mode:ecl)</small> | Eiffel<br><small>(mode:eiffel)</small> | Erlang<br><small>(mode:erlang)</small> | Fortran<br><small>(mode:fortran)</small> | 
|F#<br><small>(mode:mllike)</small> | Gas (AT&T-style assembly)<br><small>(mode:gas)</small> | Gherkin<br><small>(mode:gherkin)</small> | Go<br><small>(mode:go)</small> | Groovy<br><small>(mode:groovy)</small> | HAML<br><small>(mode:haml)</small> | Haskell<br><small>(mode:haskell)</small> | Haxe<br><small>(mode:haxe)</small> |HTML embedded scripts<br><small>(mode:htmlembedded)</small> | HTML mixed-mode<br><small>(mode:htmlmixed)</small> | 
|HTTP<br><small>(mode:http)</small> | Java<br><small>(mode:clike)</small> | Jade<br><small>(mode:jade)</small> | JavaScript<br><small>(mode:javascript)</small> | Jinja2<br><small>(mode:jinja2)</small> |Julia<br><small>(mode:julia)</small> | LESS<br><small>(mode:less)</small> | LiveScript<br><small>(mode:livescript)</small> | Lua<br><small>(mode:lua)</small> | 
|Markdown (GitHub-flavour)<br><small>(mode:markdown)</small> | mIRC<br><small>(mode:mirc)</small> | Nginx<br><small>(mode:nginx)</small> | NTriples<br><small>(mode:ntriples)</small> | OCaml<br><small>(mode:mllike)</small> | Octave (MATLAB)<br><small>(mode:octave)</small> | Pascal<br><small>(mode:pascal)</small> | PEG.js<br><small>(mode:pegjs)</small> | 
|Perl<br><small>(mode:perl)</small> | PHP<br><small>(mode:php)</small> | Pig Latin<br><small>(mode:pig)</small> | Properties files<br><small>(mode:properties)</small> | Puppet<br><small>(mode:puppet)</small> | Python<br><small>(mode:phyton)</small> | Q<br><small>(mode:q)</small> | R<br><small>(mode:r)</small> | 
|RPM spec and changelog<br><small>(mode:rpm)</small> | reStructuredText<br><small>(mode:rst)</small> | Ruby<br><small>(mode:ruby)</small> | Rust<br><small>(mode:rust)</small> | Sass<br><small>(mode:sass)</small> | Scala<br><small>(mode:clike)</small> | Scheme<br><small>(mode:scheme)</small> | SCSS<br><small>(mode:css)</small> | 
|Shell<br><small>(mode:shell)</small> | Sieve<br><small>(mode:sieve)</small> | Smalltalk<br><small>(mode:smalltalk)</small> | Smarty<br><small>(mode:smarty)</small> | Smarty/HTML mixed<br><small>(mode:smartymixed)</small> | Solr<br><small>(mode:solr)</small> | SQL (several dialects)<br><small>(mode:sql)</small> | SPARQL<br><small>(mode:sparql)</small> | 
|sTeX, LaTeX<br><small>(mode:stex)</small> | Tcl<br><small>(mode:tcl)</small> | Tiddlywiki<br><small>(mode:tiddlywiki)</small> | Tiki wiki<br><small>(mode:tiki)</small> | TOML<br><small>(mode:toml)</small> | Turtle<br><small>(mode:turtle)</small> | VB.NET<br><small>(mode:vb)</small> | VBScript<br><small>(mode:vbscript)</small> | 
|Velocity<br><small>(mode:velocity)</small> | Verilog<br><small>(mode:verilog)</small> | XML/HTML<br><small>(mode:xml)</small> | XQuery<br><small>(mode:xquery)</small> | YAML<br><small>(mode:yaml)</small> | Z80<br><small>(mode:z80)</small>

###Themes

|       |       |       |       ||
|-------|-------|-------|-------||
|![][1]<br>3024-day|![][2]<br>3024-night |![][3]<br>ambiance|![][4]<br>base16-dark|
|![][5]<br>base16-light|![][6]<br>blackboard |![][7]<br>cobalt|![][8]<br>eclipse|
|![][9]<br>elegant|![][10]<br>erlang-dark |![][11]<br>lesser-dark|![][12]<br>mbo|
|![][13]<br>mdn-like|![][14]<br>midnight |![][15]<br>monokai|![][16]<br>neat|
|![][17]<br>night|![][18]<br>paraiso-dark |![][19]<br>paraiso-light|       |

###Add-ons

####activeline
: Displays active line in different style.
[Demo page for this addon][20] in Codemirror Official Site.

####closebrackets
: Auto closes `[]`,`{}`,`''`,`""` brackets inside the editor when typed.
[Demo page for this addon][21] in Codemirror Official Site.

####closetag
: Auto closes xml tags if the editor has an xml compatible mode like HTML.
[Demo page for this addon][22] in Codemirror Official Site.

####continuelist
: If this add-on is active in the `markdown` mode, when user presses the `Enter` key while typing a markdown list, the editor automatically indents the new line to the markdown definition list's left margin. 
[Demo page for this addon][23] in Codemirror Official Site.

####foldcode-comment
: Adds folding feature to gutter for multiline comments.

####foldcode-css
: Adds folding feature to gutter for CSS brackets.

####foldcode-html
: Adds folding feature to gutter for matched XML like tags.
[Demo page for this addon][24] in Codemirror Official Site.

####foldcode-indent
: Adds folding feature to gutter for the lines that have the same indent level.

####foldcode-js
: Adds folding feature to gutter curly brace matches in Javascript (and likes).
[Demo page for this addon][25] in Codemirror Official Site.

####foldcode-markdown
: Adds folding feature to gutter for markdown mode.
[Demo page for this addon][26] in Codemirror Official Site.

####fullscreen
: Adds a fullscreen feature which will switch to fullscreen when `F11` key is pressed inside the editor.
[Demo page for this addon][27] in Codemirror Official Site.

####markselection
: Adds a different styling to the selected text in the editor.
[Demo page for this addon][28] in Codemirror Official Site.

####matchbrackets
: Highlights the matching bracket couples mentioned in `closebrackets` addon when clicked on one of them inside the editor.

####matchhighlights
: Highlights the same words as which the user selects in the editor.
[Demo page for this addon][29] in Codemirror Official Site.

####matchtags
: Highlights the same tags as the tag that user selected.
[Demo page for this addon][30] in Codemirror Official Site.

####placeholder
: if a `placeholder` property is defined in the field's property list (outside `editor_options` array), this text will be shown in the editor as a placeholder text for the user.
[Demo page for this addon][31] in Codemirror Official Site.

####rulers
: Displays an user-defined ruler on the editor. If you wonder how to define a ruler set, have a look at `addons/display/default-ruler.js` file, and feel free to modify that script to suit your needs. Also the styling resides in `addons/display/default-rulers.css` file.
[Demo page for this addon][32] in Codemirror Official Site.

####search
: Adds a function to the key `Ctrl+F` to show a dialog for searching the editor content. 
[Demo page for this addon][33] in Codemirror Official Site.

####trailingspace
: Styles the trailing spaces with the style defined in `addons/edit/trailingspace.css`.
[Demo page for this addon][34] in Codemirror Official Site.

###CodeMirror Community for developers

[CodeMirror][35] is an open-source project shared under an [MIT license][36]. It is the editor used in the dev tools for both Firefox and Chrome, Light Table, Adobe Brackets, Bitbucket, and many other projects.

Development and bug tracking happens on [github][37] ([alternate git repository][38]). Please [read these pointers][39] before submitting a bug. Use pull requests to submit patches. All contributions must be released under the same MIT license that CodeMirror uses.

Discussion around the project is done on a [mailing list][40]. There is also the [codemirror-announce list][41], which is only used for major announcements (such as new versions). If needed, you can contact the maintainer directly.

A list of CodeMirror-related software that is not part of the main distribution is maintained on our [wiki][42]. Feel free to add your project.


  [1]: http://i129.photobucket.com/albums/p209/tpaksu/codemirror-images/3024-day_zps82e4cfe9.png
  [2]: http://i129.photobucket.com/albums/p209/tpaksu/codemirror-images/3024-night_zps7c937c99.png
  [3]: http://i129.photobucket.com/albums/p209/tpaksu/codemirror-images/ambiance_zps94c9415f.png
  [4]: http://i129.photobucket.com/albums/p209/tpaksu/codemirror-images/base16-dark_zps24ccbd10.png
  [5]: http://i129.photobucket.com/albums/p209/tpaksu/codemirror-images/base16-light_zpsb0c03993.png
  [6]: http://i129.photobucket.com/albums/p209/tpaksu/codemirror-images/blackboard_zps6c079187.png
  [7]: http://i129.photobucket.com/albums/p209/tpaksu/codemirror-images/cobalt_zps417b347a.png
  [8]: http://i129.photobucket.com/albums/p209/tpaksu/codemirror-images/eclipse_zpsd7753101.png
  [9]: http://i129.photobucket.com/albums/p209/tpaksu/codemirror-images/elegant_zpsadb3abab.png
  [10]: http://i129.photobucket.com/albums/p209/tpaksu/codemirror-images/erlang-dark_zpsc119b74f.png
  [11]: http://i129.photobucket.com/albums/p209/tpaksu/codemirror-images/lesser-dark_zps1d44483b.png
  [12]: http://i129.photobucket.com/albums/p209/tpaksu/codemirror-images/mbo_zps88b8e5b8.png
  [13]: http://i129.photobucket.com/albums/p209/tpaksu/codemirror-images/mdn-like_zps36501872.png
  [14]: http://i129.photobucket.com/albums/p209/tpaksu/codemirror-images/midnight_zps6e4f0873.png
  [15]: http://i129.photobucket.com/albums/p209/tpaksu/codemirror-images/monokai_zpse4d85c35.png
  [16]: http://i129.photobucket.com/albums/p209/tpaksu/codemirror-images/neat_zps50af0a57.png
  [17]: http://i129.photobucket.com/albums/p209/tpaksu/codemirror-images/night_zpsb0dabe0b.png
  [18]: http://i129.photobucket.com/albums/p209/tpaksu/codemirror-images/paraiso-dark_zpsbed1c247.png
  [19]: http://i129.photobucket.com/albums/p209/tpaksu/codemirror-images/paraiso-light_zps9a61eb4a.png
  [20]: http://codemirror.net/demo/activeline.html
  [21]: http://codemirror.net/demo/closebrackets.html
  [22]: http://codemirror.net/demo/closetag.html
  [23]: http://codemirror.net/mode/markdown/index.html
  [24]: http://codemirror.net/demo/folding.html
  [25]: http://codemirror.net/demo/folding.html
  [26]: http://codemirror.net/demo/folding.html
  [27]: http://codemirror.net/demo/fullscreen.html
  [28]: http://codemirror.net/demo/markselection.html
  [29]: http://codemirror.net/demo/matchhighlighter.html
  [30]: http://codemirror.net/demo/matchtags.html
  [31]: http://codemirror.net/demo/placeholder.html
  [32]: http://codemirror.net/demo/rulers.html
  [33]: http://codemirror.net/demo/search.html
  [34]: http://codemirror.net/demo/trailingspace.html
  [35]: http://codemirror.net/
  [36]: http://codemirror.net/LICENSE
  [37]: https://github.com/marijnh/CodeMirror/
  [38]: http://marijnhaverbeke.nl/git/codemirror
  [39]: http://codemirror.net/doc/reporting.html
  [40]: http://groups.google.com/group/codemirror
  [41]: http://groups.google.com/group/codemirror-announce
  [42]: https://github.com/marijnh/CodeMirror/wiki/CodeMirror-addons