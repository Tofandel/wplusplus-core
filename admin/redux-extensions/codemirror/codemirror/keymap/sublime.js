'use strict';
var $jscomp = $jscomp || {};
$jscomp.scope = {};
$jscomp.findInternal = function (k, n, m) {
	k instanceof String && (k = String(k));
	for (var p = k.length, q = 0; q < p; q++) {
		var u = k[q];
		if (n.call(m, u, q, k)) return {i: q, v: u}
	}
	return {i: -1, v: void 0}
};
$jscomp.ASSUME_ES5 = !1;
$jscomp.ASSUME_NO_NATIVE_MAP = !1;
$jscomp.ASSUME_NO_NATIVE_SET = !1;
$jscomp.defineProperty = $jscomp.ASSUME_ES5 || "function" == typeof Object.defineProperties ? Object.defineProperty : function (k, n, m) {
	k != Array.prototype && k != Object.prototype && (k[n] = m.value)
};
$jscomp.getGlobal = function (k) {
	return "undefined" != typeof window && window === k ? k : "undefined" != typeof global && null != global ? global : k
};
$jscomp.global = $jscomp.getGlobal(this);
$jscomp.polyfill = function (k, n, m, p) {
	if (n) {
		m = $jscomp.global;
		k = k.split(".");
		for (p = 0; p < k.length - 1; p++) {
			var q = k[p];
			q in m || (m[q] = {});
			m = m[q]
		}
		k = k[k.length - 1];
		p = m[k];
		n = n(p);
		n != p && null != n && $jscomp.defineProperty(m, k, {configurable: !0, writable: !0, value: n})
	}
};
$jscomp.polyfill("Array.prototype.find", function (k) {
	return k ? k : function (k, m) {
		return $jscomp.findInternal(this, k, m).v
	}
}, "es6", "es3");
(function (k) {
	"object" == typeof exports && "object" == typeof module ? k(require("../lib/codemirror"), require("../addon/search/searchcursor"), require("../addon/edit/matchbrackets")) : "function" == typeof define && define.amd ? define(["../lib/codemirror", "../addon/search/searchcursor", "../addon/edit/matchbrackets"], k) : k(CodeMirror)
})(function (k) {
	function n(a, b) {
		a.extendSelectionsBy(function (c) {
			if (a.display.shift || a.doc.extend || c.empty()) {
				var d = a.doc;
				c = c.head;
				if (0 > b && 0 == c.ch) var e = d.clipPos(l(c.line - 1)); else {
					var f =
						d.getLine(c.line);
					if (0 < b && c.ch >= f.length) e = d.clipPos(l(c.line + 1, 0)); else {
						d = "start";
						for (var h = c.ch, g = 0 > b ? 0 : f.length, t = 0; h != g; h += b, t++) {
							var v = f.charAt(0 > b ? h - 1 : h), r = "_" != v && k.isWordChar(v) ? "w" : "o";
							"w" == r && v.toUpperCase() == v && (r = "W");
							if ("start" == d) "o" != r && (d = "in", e = r); else if ("in" == d && e != r) if ("w" == e && "W" == r && 0 > b && h--, "W" == e && "w" == r && 0 < b) e = "w"; else break
						}
						e = l(c.line, h)
					}
				}
				return e
			}
			return 0 > b ? c.from() : c.to()
		})
	}

	function m(a, b) {
		if (a.isReadOnly()) return k.Pass;
		a.operation(function () {
			for (var c = a.listSelections().length,
					 d = [], e = -1, f = 0; f < c; f++) {
				var h = a.listSelections()[f].head;
				h.line <= e || (e = l(h.line + (b ? 0 : 1), 0), a.replaceRange("\n", e, null, "+insertLine"), a.indentLine(e.line, null, !0), d.push({
					head: e,
					anchor: e
				}), e = h.line + 1)
			}
			a.setSelections(d)
		});
		a.execCommand("indentAuto")
	}

	function p(a, b) {
		var c = b.ch, d = c;
		for (a = a.getLine(b.line); c && k.isWordChar(a.charAt(c - 1));) --c;
		for (; d < a.length && k.isWordChar(a.charAt(d));) ++d;
		return {from: l(b.line, c), to: l(b.line, d), word: a.slice(c, d)}
	}

	function q(a, b) {
		for (var c = a.listSelections(), d = [], e = 0; e <
		c.length; e++) {
			var f = c[e], h = a.findPosV(f.anchor, b, "line", f.anchor.goalColumn),
				k = a.findPosV(f.head, b, "line", f.head.goalColumn);
			h.goalColumn = null != f.anchor.goalColumn ? f.anchor.goalColumn : a.cursorCoords(f.anchor, "div").left;
			k.goalColumn = null != f.head.goalColumn ? f.head.goalColumn : a.cursorCoords(f.head, "div").left;
			h = {anchor: h, head: k};
			d.push(f);
			d.push(h)
		}
		a.setSelections(d)
	}

	function u(a) {
		for (var b = a.listSelections(), c = [], d = 0; d < b.length; d++) {
			var e = b[d], f = e.head, h = a.scanForBracket(f, -1);
			if (!h) return !1;
			for (; ;) {
				f =
					a.scanForBracket(f, 1);
				if (!f) return !1;
				if (f.ch == "(){}[]".charAt("(){}[]".indexOf(h.ch) + 1)) {
					var g = l(h.pos.line, h.pos.ch + 1);
					if (0 == k.cmpPos(g, e.from()) && 0 == k.cmpPos(f.pos, e.to())) {
						if (h = a.scanForBracket(h.pos, -1), !h) return !1
					} else {
						c.push({anchor: g, head: f.pos});
						break
					}
				}
				f = l(f.pos.line, f.pos.ch + 1)
			}
		}
		a.setSelections(c);
		return !0
	}

	function x(a, b) {
		if (a.isReadOnly()) return k.Pass;
		for (var c = a.listSelections(), d = [], e, f = 0; f < c.length; f++) {
			var h = c[f];
			if (!h.empty()) {
				var g = h.from().line;
				for (h = h.to().line; f < c.length - 1 && c[f +
				1].from().line == h;) h = c[++f].to().line;
				c[f].to().ch || h--;
				d.push(g, h)
			}
		}
		d.length ? e = !0 : d.push(a.firstLine(), a.lastLine());
		a.operation(function () {
			for (var c = [], f = 0; f < d.length; f += 2) {
				var h = d[f + 1], k = l(d[f], 0), g = l(h), w = a.getRange(k, g, !1);
				b ? w.sort() : w.sort(function (a, b) {
					var c = a.toUpperCase(), d = b.toUpperCase();
					c != d && (a = c, b = d);
					return a < b ? -1 : a == b ? 0 : 1
				});
				a.replaceRange(w, k, g);
				e && c.push({anchor: k, head: l(h + 1, 0)})
			}
			e && a.setSelections(c, 0)
		})
	}

	function y(a, b) {
		a.operation(function () {
			for (var c = a.listSelections(), d = [], e = [],
					 f = 0; f < c.length; f++) {
				var h = c[f];
				h.empty() ? (d.push(f), e.push("")) : e.push(b(a.getRange(h.from(), h.to())))
			}
			a.replaceSelections(e, "around", "case");
			f = d.length - 1;
			for (var g; 0 <= f; f--) h = c[d[f]], g && 0 < k.cmpPos(h.head, g) || (e = p(a, h.head), g = e.from, a.replaceRange(b(e.word), e.from, e.to))
		})
	}

	function z(a) {
		var b = a.getCursor("from"), c = a.getCursor("to");
		if (0 == k.cmpPos(b, c)) {
			var d = p(a, b);
			if (!d.word) return;
			b = d.from;
			c = d.to
		}
		return {from: b, to: c, query: a.getRange(b, c), word: d}
	}

	function A(a, b) {
		var c = z(a);
		if (c) {
			var d = c.query, e = a.getSearchCursor(d,
				b ? c.to : c.from);
			(b ? e.findNext() : e.findPrevious()) ? a.setSelection(e.from(), e.to()) : (e = a.getSearchCursor(d, b ? l(a.firstLine(), 0) : a.clipPos(l(a.lastLine()))), (b ? e.findNext() : e.findPrevious()) ? a.setSelection(e.from(), e.to()) : c.word && a.setSelection(c.from, c.to))
		}
	}

	var g = k.commands, l = k.Pos;
	g.goSubwordLeft = function (a) {
		n(a, -1)
	};
	g.goSubwordRight = function (a) {
		n(a, 1)
	};
	g.scrollLineUp = function (a) {
		var b = a.getScrollInfo();
		if (!a.somethingSelected()) {
			var c = a.lineAtHeight(b.top + b.clientHeight, "local");
			a.getCursor().line >=
			c && a.execCommand("goLineUp")
		}
		a.scrollTo(null, b.top - a.defaultTextHeight())
	};
	g.scrollLineDown = function (a) {
		var b = a.getScrollInfo();
		if (!a.somethingSelected()) {
			var c = a.lineAtHeight(b.top, "local") + 1;
			a.getCursor().line <= c && a.execCommand("goLineDown")
		}
		a.scrollTo(null, b.top + a.defaultTextHeight())
	};
	g.splitSelectionByLine = function (a) {
		for (var b = a.listSelections(), c = [], d = 0; d < b.length; d++) for (var e = b[d].from(), f = b[d].to(), h = e.line; h <= f.line; ++h) f.line > e.line && h == f.line && 0 == f.ch || c.push({
			anchor: h == e.line ? e : l(h,
				0), head: h == f.line ? f : l(h)
		});
		a.setSelections(c, 0)
	};
	g.singleSelectionTop = function (a) {
		var b = a.listSelections()[0];
		a.setSelection(b.anchor, b.head, {scroll: !1})
	};
	g.selectLine = function (a) {
		for (var b = a.listSelections(), c = [], d = 0; d < b.length; d++) {
			var e = b[d];
			c.push({anchor: l(e.from().line, 0), head: l(e.to().line + 1, 0)})
		}
		a.setSelections(c)
	};
	g.insertLineAfter = function (a) {
		return m(a, !1)
	};
	g.insertLineBefore = function (a) {
		return m(a, !0)
	};
	g.selectNextOccurrence = function (a) {
		var b = a.getCursor("from"), c = a.getCursor("to"), d =
			a.state.sublimeFindFullWord == a.doc.sel;
		if (0 == k.cmpPos(b, c)) {
			d = p(a, b);
			if (!d.word) return;
			a.setSelection(d.from, d.to);
			d = !0
		} else {
			b = a.getRange(b, c);
			b = d ? new RegExp("\\b" + b + "\\b") : b;
			c = a.getSearchCursor(b, c);
			var e = c.findNext();
			e || (c = a.getSearchCursor(b, l(a.firstLine(), 0)), e = c.findNext());
			if (!(b = !e)) a:{
				b = a.listSelections();
				e = c.from();
				for (var f = c.to(), h = 0; h < b.length; h++) if (b[h].from() == e && b[h].to() == f) {
					b = !0;
					break a
				}
				b = !1
			}
			if (b) return k.Pass;
			a.addSelection(c.from(), c.to())
		}
		d && (a.state.sublimeFindFullWord = a.doc.sel)
	};
	g.addCursorToPrevLine = function (a) {
		q(a, -1)
	};
	g.addCursorToNextLine = function (a) {
		q(a, 1)
	};
	g.selectScope = function (a) {
		u(a) || a.execCommand("selectAll")
	};
	g.selectBetweenBrackets = function (a) {
		if (!u(a)) return k.Pass
	};
	g.goToBracket = function (a) {
		a.extendSelectionsBy(function (b) {
			var c = a.scanForBracket(b.head, 1);
			return c && 0 != k.cmpPos(c.pos, b.head) ? c.pos : (c = a.scanForBracket(b.head, -1)) && l(c.pos.line, c.pos.ch + 1) || b.head
		})
	};
	g.swapLineUp = function (a) {
		if (a.isReadOnly()) return k.Pass;
		for (var b = a.listSelections(), c = [], d =
			a.firstLine() - 1, e = [], f = 0; f < b.length; f++) {
			var h = b[f], g = h.from().line - 1, t = h.to().line;
			e.push({anchor: l(h.anchor.line - 1, h.anchor.ch), head: l(h.head.line - 1, h.head.ch)});
			0 != h.to().ch || h.empty() || --t;
			g > d ? c.push(g, t) : c.length && (c[c.length - 1] = t);
			d = t
		}
		a.operation(function () {
			for (var b = 0; b < c.length; b += 2) {
				var d = c[b], f = c[b + 1], h = a.getLine(d);
				a.replaceRange("", l(d, 0), l(d + 1, 0), "+swapLine");
				f > a.lastLine() ? a.replaceRange("\n" + h, l(a.lastLine()), null, "+swapLine") : a.replaceRange(h + "\n", l(f, 0), null, "+swapLine")
			}
			a.setSelections(e);
			a.scrollIntoView()
		})
	};
	g.swapLineDown = function (a) {
		if (a.isReadOnly()) return k.Pass;
		for (var b = a.listSelections(), c = [], d = a.lastLine() + 1, e = b.length - 1; 0 <= e; e--) {
			var f = b[e], h = f.to().line + 1, g = f.from().line;
			0 != f.to().ch || f.empty() || h--;
			h < d ? c.push(h, g) : c.length && (c[c.length - 1] = g);
			d = g
		}
		a.operation(function () {
			for (var b = c.length - 2; 0 <= b; b -= 2) {
				var d = c[b], e = c[b + 1], f = a.getLine(d);
				d == a.lastLine() ? a.replaceRange("", l(d - 1), l(d), "+swapLine") : a.replaceRange("", l(d, 0), l(d + 1, 0), "+swapLine");
				a.replaceRange(f + "\n", l(e, 0), null,
					"+swapLine")
			}
			a.scrollIntoView()
		})
	};
	g.toggleCommentIndented = function (a) {
		a.toggleComment({indent: !0})
	};
	g.joinLines = function (a) {
		for (var b = a.listSelections(), c = [], d = 0; d < b.length; d++) {
			for (var e = b[d], f = e.from(), h = f.line, g = e.to().line; d < b.length - 1 && b[d + 1].from().line == g;) g = b[++d].to().line;
			c.push({start: h, end: g, anchor: !e.empty() && f})
		}
		a.operation(function () {
			for (var b = 0, d = [], e = 0; e < c.length; e++) {
				for (var f = c[e], h = f.anchor && l(f.anchor.line - b, f.anchor.ch), g, k = f.start; k <= f.end; k++) {
					var m = k - b;
					k == f.end && (g = l(m, a.getLine(m).length +
						1));
					m < a.lastLine() && (a.replaceRange(" ", l(m), l(m + 1, /^\s*/.exec(a.getLine(m + 1))[0].length)), ++b)
				}
				d.push({anchor: h || g, head: g})
			}
			a.setSelections(d, 0)
		})
	};
	g.duplicateLine = function (a) {
		a.operation(function () {
			for (var b = a.listSelections().length, c = 0; c < b; c++) {
				var d = a.listSelections()[c];
				d.empty() ? a.replaceRange(a.getLine(d.head.line) + "\n", l(d.head.line, 0)) : a.replaceRange(a.getRange(d.from(), d.to()), d.from())
			}
			a.scrollIntoView()
		})
	};
	g.sortLines = function (a) {
		x(a, !0)
	};
	g.sortLinesInsensitive = function (a) {
		x(a, !1)
	};
	g.nextBookmark =
		function (a) {
			var b = a.state.sublimeBookmarks;
			if (b) for (; b.length;) {
				var c = b.shift(), d = c.find();
				if (d) return b.push(c), a.setSelection(d.from, d.to)
			}
		};
	g.prevBookmark = function (a) {
		var b = a.state.sublimeBookmarks;
		if (b) for (; b.length;) {
			b.unshift(b.pop());
			var c = b[b.length - 1].find();
			if (c) return a.setSelection(c.from, c.to);
			b.pop()
		}
	};
	g.toggleBookmark = function (a) {
		for (var b = a.listSelections(), c = a.state.sublimeBookmarks || (a.state.sublimeBookmarks = []), d = 0; d < b.length; d++) {
			for (var e = b[d].from(), f = b[d].to(), h = b[d].empty() ?
				a.findMarksAt(e) : a.findMarks(e, f), g = 0; g < h.length; g++) if (h[g].sublimeBookmark) {
				h[g].clear();
				for (var k = 0; k < c.length; k++) c[k] == h[g] && c.splice(k--, 1);
				break
			}
			g == h.length && c.push(a.markText(e, f, {sublimeBookmark: !0, clearWhenEmpty: !1}))
		}
	};
	g.clearBookmarks = function (a) {
		if (a = a.state.sublimeBookmarks) for (var b = 0; b < a.length; b++) a[b].clear();
		a.length = 0
	};
	g.selectBookmarks = function (a) {
		var b = a.state.sublimeBookmarks, c = [];
		if (b) for (var d = 0; d < b.length; d++) {
			var e = b[d].find();
			e ? c.push({anchor: e.from, head: e.to}) : b.splice(d--,
				0)
		}
		c.length && a.setSelections(c, 0)
	};
	g.smartBackspace = function (a) {
		if (a.somethingSelected()) return k.Pass;
		a.operation(function () {
			for (var b = a.listSelections(), c = a.getOption("indentUnit"), d = b.length - 1; 0 <= d; d--) {
				var e = b[d].head, f = a.getRange({line: e.line, ch: 0}, e),
					g = k.countColumn(f, null, a.getOption("tabSize")), m = a.findPosH(e, -1, "char", !1);
				f && !/\S/.test(f) && 0 == g % c && (f = new l(e.line, k.findColumn(f, g - c, c)), f.ch != e.ch && (m = f));
				a.replaceRange("", m, e, "+delete")
			}
		})
	};
	g.delLineRight = function (a) {
		a.operation(function () {
			for (var b =
				a.listSelections(), c = b.length - 1; 0 <= c; c--) a.replaceRange("", b[c].anchor, l(b[c].to().line), "+delete");
			a.scrollIntoView()
		})
	};
	g.upcaseAtCursor = function (a) {
		y(a, function (a) {
			return a.toUpperCase()
		})
	};
	g.downcaseAtCursor = function (a) {
		y(a, function (a) {
			return a.toLowerCase()
		})
	};
	g.setSublimeMark = function (a) {
		a.state.sublimeMark && a.state.sublimeMark.clear();
		a.state.sublimeMark = a.setBookmark(a.getCursor())
	};
	g.selectToSublimeMark = function (a) {
		var b = a.state.sublimeMark && a.state.sublimeMark.find();
		b && a.setSelection(a.getCursor(),
			b)
	};
	g.deleteToSublimeMark = function (a) {
		var b = a.state.sublimeMark && a.state.sublimeMark.find();
		if (b) {
			var c = a.getCursor();
			if (0 < k.cmpPos(c, b)) {
				var d = b;
				b = c;
				c = d
			}
			a.state.sublimeKilled = a.getRange(c, b);
			a.replaceRange("", c, b)
		}
	};
	g.swapWithSublimeMark = function (a) {
		var b = a.state.sublimeMark && a.state.sublimeMark.find();
		b && (a.state.sublimeMark.clear(), a.state.sublimeMark = a.setBookmark(a.getCursor()), a.setCursor(b))
	};
	g.sublimeYank = function (a) {
		null != a.state.sublimeKilled && a.replaceSelection(a.state.sublimeKilled, null,
			"paste")
	};
	g.showInCenter = function (a) {
		var b = a.cursorCoords(null, "local");
		a.scrollTo(null, (b.top + b.bottom) / 2 - a.getScrollInfo().clientHeight / 2)
	};
	g.findUnder = function (a) {
		A(a, !0)
	};
	g.findUnderPrevious = function (a) {
		A(a, !1)
	};
	g.findAllUnder = function (a) {
		var b = z(a);
		if (b) {
			for (var c = a.getSearchCursor(b.query), d = [], e = -1; c.findNext();) d.push({
				anchor: c.from(),
				head: c.to()
			}), c.from().line <= b.from.line && c.from().ch <= b.from.ch && e++;
			a.setSelections(d, e)
		}
	};
	g = k.keyMap;
	g.macSublime = {
		"Cmd-Left": "goLineStartSmart",
		"Shift-Tab": "indentLess",
		"Shift-Ctrl-K": "deleteLine",
		"Alt-Q": "wrapLines",
		"Ctrl-Left": "goSubwordLeft",
		"Ctrl-Right": "goSubwordRight",
		"Ctrl-Alt-Up": "scrollLineUp",
		"Ctrl-Alt-Down": "scrollLineDown",
		"Cmd-L": "selectLine",
		"Shift-Cmd-L": "splitSelectionByLine",
		Esc: "singleSelectionTop",
		"Cmd-Enter": "insertLineAfter",
		"Shift-Cmd-Enter": "insertLineBefore",
		"Cmd-D": "selectNextOccurrence",
		"Shift-Cmd-Space": "selectScope",
		"Shift-Cmd-M": "selectBetweenBrackets",
		"Cmd-M": "goToBracket",
		"Cmd-Ctrl-Up": "swapLineUp",
		"Cmd-Ctrl-Down": "swapLineDown",
		"Cmd-/": "toggleCommentIndented",
		"Cmd-J": "joinLines",
		"Shift-Cmd-D": "duplicateLine",
		F9: "sortLines",
		"Cmd-F9": "sortLinesInsensitive",
		F2: "nextBookmark",
		"Shift-F2": "prevBookmark",
		"Cmd-F2": "toggleBookmark",
		"Shift-Cmd-F2": "clearBookmarks",
		"Alt-F2": "selectBookmarks",
		Backspace: "smartBackspace",
		"Cmd-K Cmd-K": "delLineRight",
		"Cmd-K Cmd-U": "upcaseAtCursor",
		"Cmd-K Cmd-L": "downcaseAtCursor",
		"Cmd-K Cmd-Space": "setSublimeMark",
		"Cmd-K Cmd-A": "selectToSublimeMark",
		"Cmd-K Cmd-W": "deleteToSublimeMark",
		"Cmd-K Cmd-X": "swapWithSublimeMark",
		"Cmd-K Cmd-Y": "sublimeYank",
		"Cmd-K Cmd-C": "showInCenter",
		"Cmd-K Cmd-G": "clearBookmarks",
		"Cmd-K Cmd-Backspace": "delLineLeft",
		"Cmd-K Cmd-0": "unfoldAll",
		"Cmd-K Cmd-J": "unfoldAll",
		"Ctrl-Shift-Up": "addCursorToPrevLine",
		"Ctrl-Shift-Down": "addCursorToNextLine",
		"Cmd-F3": "findUnder",
		"Shift-Cmd-F3": "findUnderPrevious",
		"Alt-F3": "findAllUnder",
		"Shift-Cmd-[": "fold",
		"Shift-Cmd-]": "unfold",
		"Cmd-I": "findIncremental",
		"Shift-Cmd-I": "findIncrementalReverse",
		"Cmd-H": "replace",
		F3: "findNext",
		"Shift-F3": "findPrev",
		fallthrough: "macDefault"
	};
	k.normalizeKeyMap(g.macSublime);
	g.pcSublime = {
		"Shift-Tab": "indentLess",
		"Shift-Ctrl-K": "deleteLine",
		"Alt-Q": "wrapLines",
		"Ctrl-T": "transposeChars",
		"Alt-Left": "goSubwordLeft",
		"Alt-Right": "goSubwordRight",
		"Ctrl-Up": "scrollLineUp",
		"Ctrl-Down": "scrollLineDown",
		"Ctrl-L": "selectLine",
		"Shift-Ctrl-L": "splitSelectionByLine",
		Esc: "singleSelectionTop",
		"Ctrl-Enter": "insertLineAfter",
		"Shift-Ctrl-Enter": "insertLineBefore",
		"Ctrl-D": "selectNextOccurrence",
		"Shift-Ctrl-Space": "selectScope",
		"Shift-Ctrl-M": "selectBetweenBrackets",
		"Ctrl-M": "goToBracket",
		"Shift-Ctrl-Up": "swapLineUp",
		"Shift-Ctrl-Down": "swapLineDown",
		"Ctrl-/": "toggleCommentIndented",
		"Ctrl-J": "joinLines",
		"Shift-Ctrl-D": "duplicateLine",
		F9: "sortLines",
		"Ctrl-F9": "sortLinesInsensitive",
		F2: "nextBookmark",
		"Shift-F2": "prevBookmark",
		"Ctrl-F2": "toggleBookmark",
		"Shift-Ctrl-F2": "clearBookmarks",
		"Alt-F2": "selectBookmarks",
		Backspace: "smartBackspace",
		"Ctrl-K Ctrl-K": "delLineRight",
		"Ctrl-K Ctrl-U": "upcaseAtCursor",
		"Ctrl-K Ctrl-L": "downcaseAtCursor",
		"Ctrl-K Ctrl-Space": "setSublimeMark",
		"Ctrl-K Ctrl-A": "selectToSublimeMark",
		"Ctrl-K Ctrl-W": "deleteToSublimeMark",
		"Ctrl-K Ctrl-X": "swapWithSublimeMark",
		"Ctrl-K Ctrl-Y": "sublimeYank",
		"Ctrl-K Ctrl-C": "showInCenter",
		"Ctrl-K Ctrl-G": "clearBookmarks",
		"Ctrl-K Ctrl-Backspace": "delLineLeft",
		"Ctrl-K Ctrl-0": "unfoldAll",
		"Ctrl-K Ctrl-J": "unfoldAll",
		"Ctrl-Alt-Up": "addCursorToPrevLine",
		"Ctrl-Alt-Down": "addCursorToNextLine",
		"Ctrl-F3": "findUnder",
		"Shift-Ctrl-F3": "findUnderPrevious",
		"Alt-F3": "findAllUnder",
		"Shift-Ctrl-[": "fold",
		"Shift-Ctrl-]": "unfold",
		"Ctrl-I": "findIncremental",
		"Shift-Ctrl-I": "findIncrementalReverse",
		"Ctrl-H": "replace",
		F3: "findNext",
		"Shift-F3": "findPrev",
		fallthrough: "pcDefault"
	};
	k.normalizeKeyMap(g.pcSublime);
	g.sublime = g.default == g.macDefault ? g.macSublime : g.pcSublime
});
