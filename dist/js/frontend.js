!function(){var n={771:function(){var n;(n=jQuery)(document).on("submit","#magicloginform",(function(i){i.preventDefault();const a=n(this);a.trigger("magic-login:login:before-submit"),n.post(a.data("ajax-url"),{beforeSend(){const i='<div class="magic-login-spinner-container"><img src="'+a.data("ajax-spinner")+'" alt="'+a.data("ajax-sending-msg")+'" class="magic-login-spinner-image" /><span class="magic-login-spinner-message">'+a.data("ajax-sending-msg")+"</span></div>";n(".magic-login-form-header").html(i),a.find(".magic-login-submit ").attr("disabled","disabled"),a.trigger("magic-login:login:before-send")},action:"magic_login_ajax_request",data:n("#magicloginform").serialize()},(function(i){n(".magic-login-form-header").html(i.data.message),i.data.show_form||a.hide(),a.trigger("magic-login:login:ajax-complete",[i])})).done((function(){a.find(".magic-login-submit ").attr("disabled",!1),a.trigger("magic-login:login:done")}))}))}},i={};function a(e){var t=i[e];if(void 0!==t)return t.exports;var o=i[e]={exports:{}};return n[e](o,o.exports,a),o.exports}a.n=function(n){var i=n&&n.__esModule?function(){return n.default}:function(){return n};return a.d(i,{a:i}),i},a.d=function(n,i){for(var e in i)a.o(i,e)&&!a.o(n,e)&&Object.defineProperty(n,e,{enumerable:!0,get:i[e]})},a.o=function(n,i){return Object.prototype.hasOwnProperty.call(n,i)},function(){"use strict";a(771)}()}();