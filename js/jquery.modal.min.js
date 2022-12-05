/*
    A simple jQuery mage_modal
    Version 1.0
*/
! function(o) { "object" == typeof module && "object" == typeof module.exports ? o(require("jquery"), window, document) : o(jQuery, window, document) }(function(o, t, i, e) {
    var s = [],
        l = function() { return s.length ? s[s.length - 1] : null },
        n = function() { var o, t = !1; for (o = s.length - 1; o >= 0; o--) s[o].$mage_blocker && (s[o].$mage_blocker.toggleClass("current", !t).toggleClass("behind", t), t = !0) };
    o.mage_modal = function(t, i) {
        var e, n;
        if (this.$body = o("body"), this.options = o.extend({}, o.mage_modal.defaults, i), this.options.doFade = !isNaN(parseInt(this.options.fadeDuration, 10)), this.$mage_blocker = null, this.options.closeExisting)
            for (; o.mage_modal.isActive();) o.mage_modal.close();
        if (s.push(this), t.is("a"))
            if (n = t.attr("href"), this.anchor = t, /^#/.test(n)) {
                if (this.$elm = o(n), 1 !== this.$elm.length) return null;
                this.$body.append(this.$elm), this.open()
            } else this.$elm = o("<div>"), this.$body.append(this.$elm), e = function(o, t) { t.elm.remove() }, this.showSpinner(), t.trigger(o.mage_modal.AJAX_SEND), o.get(n).done(function(i) {
                if (o.mage_modal.isActive()) {
                    t.trigger(o.mage_modal.AJAX_SUCCESS);
                    var s = l();
                    s.$elm.empty().append(i).on(o.mage_modal.CLOSE, e), s.hideSpinner(), s.open(), t.trigger(o.mage_modal.AJAX_COMPLETE)
                }
            }).fail(function() {
                t.trigger(o.mage_modal.AJAX_FAIL);
                var i = l();
                i.hideSpinner(), s.pop(), t.trigger(o.mage_modal.AJAX_COMPLETE)
            });
        else this.$elm = t, this.anchor = t, this.$body.append(this.$elm), this.open()
    }, o.mage_modal.prototype = {
        constructor: o.mage_modal,
        open: function() {
            var t = this;
            this.block(), this.anchor.blur(), this.options.doFade ? setTimeout(function() { t.show() }, this.options.fadeDuration * this.options.fadeDelay) : this.show(), o(i).off("keydown.mage_modal").on("keydown.mage_modal", function(o) {
                var t = l();
                27 === o.which && t.options.escapeClose && t.close()
            }), this.options.clickClose && this.$mage_blocker.click(function(t) { t.target === this && o.mage_modal.close() })
        },
        close: function() { s.pop(), this.unblock(), this.hide(), o.mage_modal.isActive() || o(i).off("keydown.mage_modal") },
        block: function() { this.$elm.trigger(o.mage_modal.BEFORE_BLOCK, [this._ctx()]), this.$body.css("overflow", "hidden"), this.$mage_blocker = o('<div class="' + this.options.mage_blockerClass + ' mage_blocker current"></div>').appendTo(this.$body), n(), this.options.doFade && this.$mage_blocker.css("opacity", 0).animate({ opacity: 1 }, this.options.fadeDuration), this.$elm.trigger(o.mage_modal.BLOCK, [this._ctx()]) },
        unblock: function(t) {!t && this.options.doFade ? this.$mage_blocker.fadeOut(this.options.fadeDuration, this.unblock.bind(this, !0)) : (this.$mage_blocker.children().appendTo(this.$body), this.$mage_blocker.remove(), this.$mage_blocker = null, n(), o.mage_modal.isActive() || this.$body.css("overflow", "")) },
        show: function() { this.$elm.trigger(o.mage_modal.BEFORE_OPEN, [this._ctx()]), this.options.showClose && (this.closeButton = o('<a href="#close-mage_modal" rel="mage_modal:close" class="close-mage_modal ' + this.options.closeClass + '">' + this.options.closeText + "</a>"), this.$elm.append(this.closeButton)), this.$elm.addClass(this.options.mage_modalClass).appendTo(this.$mage_blocker), this.options.doFade ? this.$elm.css({ opacity: 0, display: "inline-block" }).animate({ opacity: 1 }, this.options.fadeDuration) : this.$elm.css("display", "inline-block"), this.$elm.trigger(o.mage_modal.OPEN, [this._ctx()]) },
        hide: function() {
            this.$elm.trigger(o.mage_modal.BEFORE_CLOSE, [this._ctx()]), this.closeButton && this.closeButton.remove();
            var t = this;
            this.options.doFade ? this.$elm.fadeOut(this.options.fadeDuration, function() { t.$elm.trigger(o.mage_modal.AFTER_CLOSE, [t._ctx()]) }) : this.$elm.hide(0, function() { t.$elm.trigger(o.mage_modal.AFTER_CLOSE, [t._ctx()]) }), this.$elm.trigger(o.mage_modal.CLOSE, [this._ctx()])
        },
        showSpinner: function() { this.options.showSpinner && (this.spinner = this.spinner || o('<div class="' + this.options.mage_modalClass + '-spinner"></div>').append(this.options.spinnerHtml), this.$body.append(this.spinner), this.spinner.show()) },
        hideSpinner: function() { this.spinner && this.spinner.remove() },
        _ctx: function() { return { elm: this.$elm, $elm: this.$elm, $mage_blocker: this.$mage_blocker, options: this.options } }
    }, o.mage_modal.close = function(t) { if (o.mage_modal.isActive()) { t && t.preventDefault(); var i = l(); return i.close(), i.$elm } }, o.mage_modal.isActive = function() { return s.length > 0 }, o.mage_modal.getCurrent = l, o.mage_modal.defaults = { closeExisting: !0, escapeClose: !0, clickClose: !0, closeText: "Close", closeClass: "", mage_modalClass: "mage_modal", mage_blockerClass: "jquery-mage_modal", spinnerHtml: '<div class="rect1"></div><div class="rect2"></div><div class="rect3"></div><div class="rect4"></div>', showSpinner: !0, showClose: !0, fadeDuration: null, fadeDelay: 1 }, o.mage_modal.BEFORE_BLOCK = "mage_modal:before-block", o.mage_modal.BLOCK = "mage_modal:block", o.mage_modal.BEFORE_OPEN = "mage_modal:before-open", o.mage_modal.OPEN = "mage_modal:open", o.mage_modal.BEFORE_CLOSE = "mage_modal:before-close", o.mage_modal.CLOSE = "mage_modal:close", o.mage_modal.AFTER_CLOSE = "mage_modal:after-close", o.mage_modal.AJAX_SEND = "mage_modal:ajax:send", o.mage_modal.AJAX_SUCCESS = "mage_modal:ajax:success", o.mage_modal.AJAX_FAIL = "mage_modal:ajax:fail", o.mage_modal.AJAX_COMPLETE = "mage_modal:ajax:complete", o.fn.mage_modal = function(t) { return 1 === this.length && new o.mage_modal(this, t), this }, o(i).on("click.mage_modal", 'a[rel~="mage_modal:close"]', o.mage_modal.close), o(i).on("click.mage_modal", 'a[rel~="mage_modal:open"]', function(t) { t.preventDefault(), o(this).mage_modal() })
});