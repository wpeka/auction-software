!function(e){var t={};function n(o){if(t[o])return t[o].exports;var r=t[o]={i:o,l:!1,exports:{}};return e[o].call(r.exports,r,r.exports,n),r.l=!0,r.exports}n.m=e,n.c=t,n.d=function(e,t,o){n.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:o})},n.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},n.t=function(e,t){if(1&t&&(e=n(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var o=Object.create(null);if(n.r(o),Object.defineProperty(o,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var r in e)n.d(o,r,function(t){return e[t]}.bind(null,r));return o},n.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return n.d(t,"a",t),t},n.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},n.p="",n(n.s=6)}([function(e,t){e.exports=window.wp.element},function(e,t){e.exports=function(e,t,n){return t in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e},e.exports.default=e.exports,e.exports.__esModule=!0},,,,,function(e,t,n){"use strict";n.r(t);var o=n(1),r=n.n(o),u=n(0),i=wp.blocks.registerBlockType,__=(wp.components.Placeholder,wp.i18n.__);i("auction-software/featured-auctions",{title:__("Auction Software Featured Auctions","auction-software"),description:__("Shows the list of featured auctions","auction-software"),icon:"flag",category:"auction-software",attributes:{title:{type:"text",default:"Featured Auctions"},num_of_auctions:{type:"text",default:5},hide_time_left:{type:"boolean",default:!1}},edit:function(e){var t=function(t,n){e.setAttributes(r()({},t,"hide_time_left"===t?n.target.checked:n.target.value))};return e.isSelected?Object(u.createElement)("div",null,Object(u.createElement)("p",null,__("Title","auction-software")),Object(u.createElement)("input",{type:"text",value:e.attributes.title,onChange:function(e){t("title",e)}}),Object(u.createElement)("p",null,__("Number of auctions to show:","auction-software")),Object(u.createElement)("input",{type:"number",value:e.attributes.num_of_auctions,onChange:function(e){t("num_of_auctions",e)}}),Object(u.createElement)("p",null,__("Hide Time Left","auction-software")),Object(u.createElement)("input",{type:"checkbox",checked:e.attributes.hide_time_left,onChange:function(e){t("hide_time_left",e)}})):Object(u.createElement)("div",null,Object(u.createElement)("p",null,__("Auction Software Featured Auctions Widget","auction-software")))},save:function(){return null}})}]);