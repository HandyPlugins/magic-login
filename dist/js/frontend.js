!function(){var i={771:function(){var i;(i=jQuery)(document).on("submit","#magicloginform",(function(n){n.preventDefault();const a=i(this);a.trigger("magic-login:login:before-submit"),i.post(a.data("ajax-url"),{beforeSend(){const n='<div class="magic-login-spinner-container"><img src="'+a.data("ajax-spinner")+'" alt="'+a.data("ajax-sending-msg")+'" class="magic-login-spinner-image" /><span class="magic-login-spinner-message">'+a.data("ajax-sending-msg")+"</span></div>";i(".magic-login-form-header").html(n),a.find(".magic-login-submit ").attr("disabled","disabled"),a.trigger("magic-login:login:before-send")},action:"magic_login_ajax_request",data:i("#magicloginform").serialize()},(function(n){i(".magic-login-form-header").html(n.data.message),n.data.show_form||a.hide(),a.trigger("magic-login:login:ajax-success",[n])})).fail((function(i){a.trigger("magic-login:login:ajax-fail",[i])})).always((function(){a.find(".magic-login-submit ").attr("disabled",!1),a.trigger("magic-login:login:always")}))}))}},n={};function a(e){var t=n[e];if(void 0!==t)return t.exports;var o=n[e]={exports:{}};return i[e](o,o.exports,a),o.exports}a.n=function(i){var n=i&&i.__esModule?function(){return i.default}:function(){return i};return a.d(n,{a:n}),n},a.d=function(i,n){for(var e in n)a.o(n,e)&&!a.o(i,e)&&Object.defineProperty(i,e,{enumerable:!0,get:n[e]})},a.o=function(i,n){return Object.prototype.hasOwnProperty.call(i,n)},function(){"use strict";a(771)}()}();