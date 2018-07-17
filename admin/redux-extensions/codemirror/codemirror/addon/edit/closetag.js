'use strict';
(function (d) {
	"object" == typeof exports && "object" == typeof module ? d(require("../../lib/codemirror"), require("../fold/xml-fold")) : "function" == typeof define && define.amd ? define(["../../lib/codemirror", "../fold/xml-fold"], d) : d(CodeMirror)
})(function (d) {
	function t(b) {
		if (b.getOption("disableInput")) return d.Pass;
		for (var k = b.listSelections(), a = [], e = b.getOption("autoCloseTags"), g = 0; g < k.length; g++) {
			if (!k[g].empty()) return d.Pass;
			var f = k[g].head, c = b.getTokenAt(f), l = d.innerMode(b.getMode(), c.state),
				m = l.state;
			if ("xml" != l.mode.name || !m.tagName) return d.Pass;
			var h = "html" == l.mode.configuration;
			l = "object" == typeof e && e.dontCloseTags || h && u;
			var n = "object" == typeof e && e.indentTags || h && v;
			h = m.tagName;
			c.end > f.ch && (h = h.slice(0, h.length - c.end + f.ch));
			var p = h.toLowerCase();
			if (!h || "string" == c.type && (c.end != f.ch || !/["']/.test(c.string.charAt(c.string.length - 1)) || 1 == c.string.length) || "tag" == c.type && "closeTag" == m.type || c.string.indexOf("/") == c.string.length - 1 || l && -1 < q(l, p) || r(b, h, f, m, !0)) return d.Pass;
			c = n && -1 < q(n,
				p);
			a[g] = {
				indent: c,
				text: ">" + (c ? "\n\n" : "") + "</" + h + ">",
				newPos: c ? d.Pos(f.line + 1, 0) : d.Pos(f.line, f.ch + 1)
			}
		}
		e = "object" == typeof e && e.dontIndentOnAutoClose;
		for (g = k.length - 1; 0 <= g; g--) f = a[g], b.replaceRange(f.text, k[g].head, k[g].anchor, "+insert"), c = b.listSelections().slice(0), c[g] = {
			head: f.newPos,
			anchor: f.newPos
		}, b.setSelections(c), !e && f.indent && (b.indentLine(f.newPos.line, null, !0), b.indentLine(f.newPos.line + 1, null, !0))
	}

	function p(b, k) {
		var a = b.listSelections(), e = [], g = k ? "/" : "</", f = b.getOption("autoCloseTags");
		f = "object" == typeof f && f.dontIndentOnSlash;
		for (var c = 0; c < a.length; c++) {
			if (!a[c].empty()) return d.Pass;
			var l = a[c].head, m = b.getTokenAt(l), h = d.innerMode(b.getMode(), m.state), n = h.state;
			if (k && ("string" == m.type || "<" != m.string.charAt(0) || m.start != l.ch - 1)) return d.Pass;
			if ("xml" != h.mode.name) if ("htmlmixed" == b.getMode().name && "javascript" == h.mode.name) h = g + "script"; else if ("htmlmixed" == b.getMode().name && "css" == h.mode.name) h = g + "style"; else return d.Pass; else {
				if (!n.context || !n.context.tagName || r(b, n.context.tagName,
						l, n)) return d.Pass;
				h = g + n.context.tagName
			}
			">" != b.getLine(l.line).charAt(m.end) && (h += ">");
			e[c] = h
		}
		b.replaceSelections(e);
		a = b.listSelections();
		if (!f) for (c = 0; c < a.length; c++) (c == a.length - 1 || a[c].head.line < a[c + 1].head.line) && b.indentLine(a[c].head.line)
	}

	function q(b, d) {
		if (b.indexOf) return b.indexOf(d);
		for (var a = 0, e = b.length; a < e; ++a) if (b[a] == d) return a;
		return -1
	}

	function r(b, k, a, e, g) {
		if (!d.scanForClosingTag) return !1;
		var f = Math.min(b.lastLine() + 1, a.line + 500);
		a = d.scanForClosingTag(b, a, null, f);
		if (!a || a.tag !=
			k) return !1;
		e = e.context;
		for (g = g ? 1 : 0; e && e.tagName == k; e = e.prev) ++g;
		a = a.to;
		for (e = 1; e < g; e++) {
			a = d.scanForClosingTag(b, a, null, f);
			if (!a || a.tag != k) return !1;
			a = a.to
		}
		return !0
	}

	d.defineOption("autoCloseTags", !1, function (b, k, a) {
		a != d.Init && a && b.removeKeyMap("autoCloseTags");
		if (k) {
			a = {name: "autoCloseTags"};
			if ("object" != typeof k || k.whenClosing) a["'/'"] = function (a) {
				a = a.getOption("disableInput") ? d.Pass : p(a, !0);
				return a
			};
			if ("object" != typeof k || k.whenOpening) a["'>'"] = function (a) {
				return t(a)
			};
			b.addKeyMap(a)
		}
	});
	var u = "area base br col command embed hr img input keygen link meta param source track wbr".split(" "),
		v = "applet blockquote body button div dl fieldset form frameset h1 h2 h3 h4 h5 h6 head html iframe layer legend object ol p select table ul".split(" ");
	d.commands.closeTag = function (b) {
		return p(b)
	}
});
