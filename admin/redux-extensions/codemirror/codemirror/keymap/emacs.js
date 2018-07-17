'use strict';
(function (h) {
	"object" == typeof exports && "object" == typeof module ? h(require("../lib/codemirror")) : "function" == typeof define && define.amd ? define(["../lib/codemirror"], h) : h(CodeMirror)
})(function (h) {
	function z(a, b) {
		return a.line == b.line && a.ch == b.ch
	}

	function A(a) {
		k.push(a);
		50 < k.length && k.shift()
	}

	function G(a) {
		return k[k.length - (a ? Math.min(a, 1) : 1)] || ""
	}

	function O() {
		1 < k.length && k.pop();
		return G()
	}

	function q(a, b, c, d, e) {
		null == e && (e = a.getRange(b, c));
		"grow" == d && u && u.cm == a && z(b, u.pos) && a.isClean(u.gen) ?
			k.length ? k[k.length - 1] += e : A(e) : !1 !== d && A(e);
		a.replaceRange("", b, c, "+delete");
		u = "grow" == d ? {cm: a, pos: b, gen: a.changeGeneration()} : null
	}

	function p(a, b, c) {
		return a.findPosH(b, c, "char", !0)
	}

	function r(a, b, c) {
		return a.findPosH(b, c, "word", !0)
	}

	function v(a, b, c) {
		return a.findPosV(b, c, "line", a.doc.sel.goalColumn)
	}

	function w(a, b, c) {
		return a.findPosV(b, c, "page", a.doc.sel.goalColumn)
	}

	function H(a, b, c) {
		var d = b.line, e = a.getLine(d);
		b = /\S/.test(0 > c ? e.slice(0, b.ch) : e.slice(b.ch));
		for (var f = a.firstLine(), g = a.lastLine(); ;) {
			d +=
				c;
			if (d < f || d > g) return a.clipPos(l(d - c, 0 > c ? 0 : null));
			e = a.getLine(d);
			if (/\S/.test(e)) b = !0; else if (b) return l(d, 0)
		}
	}

	function x(a, b, c) {
		var d = b.line, e = b.ch;
		b = a.getLine(b.line);
		for (var f = !1; ;) {
			var g = b.charAt(e + (0 > c ? -1 : 0));
			if (g) {
				if (f && /[!?.]/.test(g)) return l(d, e + (0 < c ? 1 : 0));
				f || (f = /\w/.test(g));
				e += c
			} else {
				if (d == (0 > c ? a.firstLine() : a.lastLine())) return l(d, e);
				b = a.getLine(d + c);
				if (!/\S/.test(b)) return l(d, e);
				d += c;
				e = 0 > c ? b.length : 0
			}
		}
	}

	function m(a, b, c) {
		var d;
		if (a.findMatchingBracket && (d = a.findMatchingBracket(b, {strict: !0})) &&
			d.match && (d.forward ? 1 : -1) == c) return 0 < c ? l(d.to.line, d.to.ch + 1) : d.to;
		for (var e = !0; ; e = !1) {
			var f = a.getTokenAt(b);
			d = l(b.line, 0 > c ? f.start : f.end);
			if (e && 0 < c && f.end == b.ch || !/\w/.test(f.string)) {
				e = a.findPosH(d, c, "char");
				if (z(d, e)) return b;
				b = e
			} else return d
		}
	}

	function t(a, b) {
		var c = a.state.emacsPrefix;
		if (!c) return b ? null : 1;
		B(a);
		return "-" == c ? -1 : Number(c)
	}

	function g(a) {
		var b = "string" == typeof a ? function (b) {
			b.execCommand(a)
		} : a;
		return function (a) {
			var c = t(a);
			b(a);
			for (var e = 1; e < c; ++e) b(a)
		}
	}

	function C(a, b, c, d) {
		var e =
			t(a);
		0 > e && (d = -d, e = -e);
		for (var f = 0; f < e; ++f) {
			var g = c(a, b, d);
			if (z(g, b)) break;
			b = g
		}
		return b
	}

	function f(a, b) {
		var c = function (c) {
			c.extendSelection(C(c, c.getCursor(), a, b))
		};
		c.motion = !0;
		return c
	}

	function n(a, b, c, d) {
		for (var e = a.listSelections(), f, g = e.length; g--;) f = e[g].head, q(a, f, C(a, f, b, c), d)
	}

	function D(a, b) {
		if (a.somethingSelected()) {
			for (var c = a.listSelections(), d, e = c.length; e--;) d = c[e], q(a, d.anchor, d.head, b);
			return !0
		}
	}

	function I(a, b) {
		a.state.emacsPrefix ? "-" != b && (a.state.emacsPrefix += b) : (a.state.emacsPrefix =
			b, a.on("keyHandled", J), a.on("inputRead", K))
	}

	function J(a, b) {
		a.state.emacsPrefixMap || L.hasOwnProperty(b) || B(a)
	}

	function B(a) {
		a.state.emacsPrefix = null;
		a.off("keyHandled", J);
		a.off("inputRead", K)
	}

	function K(a, b) {
		var c = t(a);
		if (1 < c && "+input" == b.origin) {
			b = b.text.join("\n");
			for (var d = "", e = 1; e < c; ++e) d += b;
			a.replaceSelection(d)
		}
	}

	function y(a, b) {
		if ("string" != typeof b || !/^\d$/.test(b) && "Ctrl-U" != b) a.removeKeyMap(E), a.state.emacsPrefixMap = !1, a.off("keyHandled", y), a.off("inputRead", y)
	}

	function M(a) {
		a.setCursor(a.getCursor());
		a.setExtending(!a.getExtending());
		a.on("change", function () {
			a.setExtending(!1)
		})
	}

	function P(a, b, c) {
		a.openDialog ? a.openDialog(b + ': <input type="text" style="width: 10em"/>', c, {bottom: !0}) : c(prompt(b, ""))
	}

	function F(a, b) {
		var c = a.getCursor(), d = a.findPosH(c, 1, "word");
		a.replaceRange(b(a.getRange(c, d)), c, d);
		a.setCursor(d)
	}

	function N(a) {
		E[a] = function (b) {
			I(b, a)
		};
		Q["Ctrl-" + a] = function (b) {
			I(b, a)
		};
		L["Ctrl-" + a] = !0
	}

	var l = h.Pos, k = [], u = null, L = {"Alt-G": !0, "Ctrl-X": !0, "Ctrl-Q": !0, "Ctrl-U": !0};
	h.emacs = {
		kill: q, killRegion: D,
		repeated: g
	};
	var Q = h.keyMap.emacs = h.normalizeKeyMap({
		"Ctrl-W": function (a) {
			q(a, a.getCursor("start"), a.getCursor("end"), !0)
		},
		"Ctrl-K": g(function (a) {
			var b = a.getCursor(), c = a.clipPos(l(b.line)), d = a.getRange(b, c);
			/\S/.test(d) || (d += "\n", c = l(b.line + 1, 0));
			q(a, b, c, "grow", d)
		}),
		"Alt-W": function (a) {
			A(a.getSelection());
			a.setExtending(!1);
			a.setCursor(a.getCursor())
		},
		"Ctrl-Y": function (a) {
			var b = a.getCursor();
			a.replaceRange(G(t(a)), b, b, "paste");
			a.setSelection(b, a.getCursor())
		},
		"Alt-Y": function (a) {
			a.replaceSelection(O(),
				"around", "paste")
		},
		"Ctrl-Space": M,
		"Ctrl-Shift-2": M,
		"Ctrl-F": f(p, 1),
		"Ctrl-B": f(p, -1),
		Right: f(p, 1),
		Left: f(p, -1),
		"Ctrl-D": function (a) {
			n(a, p, 1, !1)
		},
		Delete: function (a) {
			D(a, !1) || n(a, p, 1, !1)
		},
		"Ctrl-H": function (a) {
			n(a, p, -1, !1)
		},
		Backspace: function (a) {
			D(a, !1) || n(a, p, -1, !1)
		},
		"Alt-F": f(r, 1),
		"Alt-B": f(r, -1),
		"Alt-Right": f(r, 1),
		"Alt-Left": f(r, -1),
		"Alt-D": function (a) {
			n(a, r, 1, "grow")
		},
		"Alt-Backspace": function (a) {
			n(a, r, -1, "grow")
		},
		"Ctrl-N": f(v, 1),
		"Ctrl-P": f(v, -1),
		Down: f(v, 1),
		Up: f(v, -1),
		"Ctrl-A": "goLineStart",
		"Ctrl-E": "goLineEnd",
		End: "goLineEnd",
		Home: "goLineStart",
		"Alt-V": f(w, -1),
		"Ctrl-V": f(w, 1),
		PageUp: f(w, -1),
		PageDown: f(w, 1),
		"Ctrl-Up": f(H, -1),
		"Ctrl-Down": f(H, 1),
		"Alt-A": f(x, -1),
		"Alt-E": f(x, 1),
		"Alt-K": function (a) {
			n(a, x, 1, "grow")
		},
		"Ctrl-Alt-K": function (a) {
			n(a, m, 1, "grow")
		},
		"Ctrl-Alt-Backspace": function (a) {
			n(a, m, -1, "grow")
		},
		"Ctrl-Alt-F": f(m, 1),
		"Ctrl-Alt-B": f(m, -1, "grow"),
		"Shift-Ctrl-Alt-2": function (a) {
			var b = a.getCursor();
			a.setSelection(C(a, b, m, 1), b)
		},
		"Ctrl-Alt-T": function (a) {
			var b = m(a, a.getCursor(), -1), c = m(a, b, 1), d = m(a, c, 1), e =
				m(a, d, -1);
			a.replaceRange(a.getRange(e, d) + a.getRange(c, e) + a.getRange(b, c), b, d)
		},
		"Ctrl-Alt-U": g(function (a) {
			var b = a.getCursor(), c = b.line;
			b = b.ch;
			for (var d = []; c >= a.firstLine();) {
				for (var e = a.getLine(c), f = null == b ? e.length : b; 0 < f;) if (b = e.charAt(--f), ")" == b) d.push("("); else if ("]" == b) d.push("["); else if ("}" == b) d.push("{"); else if (/[\(\{\[]/.test(b) && (!d.length || d.pop() != b)) return a.extendSelection(l(c, f));
				--c;
				b = null
			}
		}),
		"Alt-Space": function (a) {
			for (var b = a.getCursor(), c = b.ch, d = b.ch, e = a.getLine(b.line); c && /\s/.test(e.charAt(c -
				1));) --c;
			for (; d < e.length && /\s/.test(e.charAt(d));) ++d;
			a.replaceRange(" ", l(b.line, c), l(b.line, d))
		},
		"Ctrl-O": g(function (a) {
			a.replaceSelection("\n", "start")
		}),
		"Ctrl-T": g(function (a) {
			a.execCommand("transposeChars")
		}),
		"Alt-C": g(function (a) {
			F(a, function (a) {
				var b = a.search(/\w/);
				return -1 == b ? a : a.slice(0, b) + a.charAt(b).toUpperCase() + a.slice(b + 1).toLowerCase()
			})
		}),
		"Alt-U": g(function (a) {
			F(a, function (a) {
				return a.toUpperCase()
			})
		}),
		"Alt-L": g(function (a) {
			F(a, function (a) {
				return a.toLowerCase()
			})
		}),
		"Alt-;": "toggleComment",
		"Ctrl-/": g("undo"),
		"Shift-Ctrl--": g("undo"),
		"Ctrl-Z": g("undo"),
		"Cmd-Z": g("undo"),
		"Shift-Alt-,": "goDocStart",
		"Shift-Alt-.": "goDocEnd",
		"Ctrl-S": "findPersistentNext",
		"Ctrl-R": "findPersistentPrev",
		"Ctrl-G": function (a) {
			a.execCommand("clearSearch");
			a.setExtending(!1);
			a.setCursor(a.getCursor())
		},
		"Shift-Alt-5": "replace",
		"Alt-/": "autocomplete",
		Enter: "newlineAndIndent",
		"Ctrl-J": g(function (a) {
			a.replaceSelection("\n", "end")
		}),
		Tab: "indentAuto",
		"Alt-G G": function (a) {
			var b = t(a, !0);
			if (null != b && 0 < b) return a.setCursor(b -
				1);
			P(a, "Goto line", function (b) {
				var c;
				b && !isNaN(c = Number(b)) && c == (c | 0) && 0 < c && a.setCursor(c - 1)
			})
		},
		"Ctrl-X Tab": function (a) {
			a.indentSelection(t(a, !0) || a.getOption("indentUnit"))
		},
		"Ctrl-X Ctrl-X": function (a) {
			a.setSelection(a.getCursor("head"), a.getCursor("anchor"))
		},
		"Ctrl-X Ctrl-S": "save",
		"Ctrl-X Ctrl-W": "save",
		"Ctrl-X S": "saveAll",
		"Ctrl-X F": "open",
		"Ctrl-X U": g("undo"),
		"Ctrl-X K": "close",
		"Ctrl-X Delete": function (a) {
			q(a, a.getCursor(), x(a, a.getCursor(), 1), "grow")
		},
		"Ctrl-X H": "selectAll",
		"Ctrl-Q Tab": g("insertTab"),
		"Ctrl-U": function (a) {
			a.state.emacsPrefixMap = !0;
			a.addKeyMap(E);
			a.on("keyHandled", y);
			a.on("inputRead", y)
		}
	}), E = {"Ctrl-G": B};
	for (h = 0; 10 > h; ++h) N(String(h));
	N("-")
});
