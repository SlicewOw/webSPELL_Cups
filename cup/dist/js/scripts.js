/*
PNotify 3.0.0 sciactive.com/pnotify/
(C) 2015 Hunter Perrin; Google, Inc.
license Apache-2.0
*/
(function(b,k){"function"===typeof define&&define.amd?define("pnotify",["jquery"],function(q){return k(q,b)}):"object"===typeof exports&&"undefined"!==typeof module?module.exports=k(require("jquery"),global||b):b.PNotify=k(b.jQuery,b)})(this,function(b,k){var q=function(l){var k={dir1:"down",dir2:"left",push:"bottom",spacing1:36,spacing2:36,context:b("body"),modal:!1},g,h,n=b(l),r=function(){h=b("body");d.prototype.options.stack.context=h;n=b(l);n.bind("resize",function(){g&&clearTimeout(g);g=setTimeout(function(){d.positionAll(!0)},
10)})},s=function(c){var a=b("<div />",{"class":"ui-pnotify-modal-overlay"});a.prependTo(c.context);c.overlay_close&&a.click(function(){d.removeStack(c)});return a},d=function(c){this.parseOptions(c);this.init()};b.extend(d.prototype,{version:"3.0.0",options:{title:!1,title_escape:!1,text:!1,text_escape:!1,styling:"brighttheme",addclass:"",cornerclass:"",auto_display:!0,width:"300px",min_height:"16px",type:"notice",icon:!0,animation:"fade",animate_speed:"normal",shadow:!0,hide:!0,delay:8E3,mouse_reset:!0,
remove:!0,insert_brs:!0,destroy:!0,stack:k},modules:{},runModules:function(c,a){var p,b;for(b in this.modules)p="object"===typeof a&&b in a?a[b]:a,"function"===typeof this.modules[b][c]&&(this.modules[b].notice=this,this.modules[b].options="object"===typeof this.options[b]?this.options[b]:{},this.modules[b][c](this,"object"===typeof this.options[b]?this.options[b]:{},p))},state:"initializing",timer:null,animTimer:null,styles:null,elem:null,container:null,title_container:null,text_container:null,animating:!1,
timerHide:!1,init:function(){var c=this;this.modules={};b.extend(!0,this.modules,d.prototype.modules);this.styles="object"===typeof this.options.styling?this.options.styling:d.styling[this.options.styling];this.elem=b("<div />",{"class":"ui-pnotify "+this.options.addclass,css:{display:"none"},"aria-live":"assertive","aria-role":"alertdialog",mouseenter:function(a){if(c.options.mouse_reset&&"out"===c.animating){if(!c.timerHide)return;c.cancelRemove()}c.options.hide&&c.options.mouse_reset&&c.cancelRemove()},
mouseleave:function(a){c.options.hide&&c.options.mouse_reset&&"out"!==c.animating&&c.queueRemove();d.positionAll()}});"fade"===this.options.animation&&this.elem.addClass("ui-pnotify-fade-"+this.options.animate_speed);this.container=b("<div />",{"class":this.styles.container+" ui-pnotify-container "+("error"===this.options.type?this.styles.error:"info"===this.options.type?this.styles.info:"success"===this.options.type?this.styles.success:this.styles.notice),role:"alert"}).appendTo(this.elem);""!==
this.options.cornerclass&&this.container.removeClass("ui-corner-all").addClass(this.options.cornerclass);this.options.shadow&&this.container.addClass("ui-pnotify-shadow");!1!==this.options.icon&&b("<div />",{"class":"ui-pnotify-icon"}).append(b("<span />",{"class":!0===this.options.icon?"error"===this.options.type?this.styles.error_icon:"info"===this.options.type?this.styles.info_icon:"success"===this.options.type?this.styles.success_icon:this.styles.notice_icon:this.options.icon})).prependTo(this.container);
this.title_container=b("<h4 />",{"class":"ui-pnotify-title"}).appendTo(this.container);!1===this.options.title?this.title_container.hide():this.options.title_escape?this.title_container.text(this.options.title):this.title_container.html(this.options.title);this.text_container=b("<div />",{"class":"ui-pnotify-text","aria-role":"alert"}).appendTo(this.container);!1===this.options.text?this.text_container.hide():this.options.text_escape?this.text_container.text(this.options.text):this.text_container.html(this.options.insert_brs?
String(this.options.text).replace(/\n/g,"<br />"):this.options.text);"string"===typeof this.options.width&&this.elem.css("width",this.options.width);"string"===typeof this.options.min_height&&this.container.css("min-height",this.options.min_height);d.notices="top"===this.options.stack.push?b.merge([this],d.notices):b.merge(d.notices,[this]);"top"===this.options.stack.push&&this.queuePosition(!1,1);this.options.stack.animation=!1;this.runModules("init");this.options.auto_display&&this.open();return this},
update:function(c){var a=this.options;this.parseOptions(a,c);this.elem.removeClass("ui-pnotify-fade-slow ui-pnotify-fade-normal ui-pnotify-fade-fast");"fade"===this.options.animation&&this.elem.addClass("ui-pnotify-fade-"+this.options.animate_speed);this.options.cornerclass!==a.cornerclass&&this.container.removeClass("ui-corner-all "+a.cornerclass).addClass(this.options.cornerclass);this.options.shadow!==a.shadow&&(this.options.shadow?this.container.addClass("ui-pnotify-shadow"):this.container.removeClass("ui-pnotify-shadow"));
!1===this.options.addclass?this.elem.removeClass(a.addclass):this.options.addclass!==a.addclass&&this.elem.removeClass(a.addclass).addClass(this.options.addclass);!1===this.options.title?this.title_container.slideUp("fast"):this.options.title!==a.title&&(this.options.title_escape?this.title_container.text(this.options.title):this.title_container.html(this.options.title),!1===a.title&&this.title_container.slideDown(200));!1===this.options.text?this.text_container.slideUp("fast"):this.options.text!==
a.text&&(this.options.text_escape?this.text_container.text(this.options.text):this.text_container.html(this.options.insert_brs?String(this.options.text).replace(/\n/g,"<br />"):this.options.text),!1===a.text&&this.text_container.slideDown(200));this.options.type!==a.type&&this.container.removeClass(this.styles.error+" "+this.styles.notice+" "+this.styles.success+" "+this.styles.info).addClass("error"===this.options.type?this.styles.error:"info"===this.options.type?this.styles.info:"success"===this.options.type?
this.styles.success:this.styles.notice);if(this.options.icon!==a.icon||!0===this.options.icon&&this.options.type!==a.type)this.container.find("div.ui-pnotify-icon").remove(),!1!==this.options.icon&&b("<div />",{"class":"ui-pnotify-icon"}).append(b("<span />",{"class":!0===this.options.icon?"error"===this.options.type?this.styles.error_icon:"info"===this.options.type?this.styles.info_icon:"success"===this.options.type?this.styles.success_icon:this.styles.notice_icon:this.options.icon})).prependTo(this.container);
this.options.width!==a.width&&this.elem.animate({width:this.options.width});this.options.min_height!==a.min_height&&this.container.animate({minHeight:this.options.min_height});this.options.hide?a.hide||this.queueRemove():this.cancelRemove();this.queuePosition(!0);this.runModules("update",a);return this},open:function(){this.state="opening";this.runModules("beforeOpen");var c=this;this.elem.parent().length||this.elem.appendTo(this.options.stack.context?this.options.stack.context:h);"top"!==this.options.stack.push&&
this.position(!0);this.animateIn(function(){c.queuePosition(!0);c.options.hide&&c.queueRemove();c.state="open";c.runModules("afterOpen")});return this},remove:function(c){this.state="closing";this.timerHide=!!c;this.runModules("beforeClose");var a=this;this.timer&&(l.clearTimeout(this.timer),this.timer=null);this.animateOut(function(){a.state="closed";a.runModules("afterClose");a.queuePosition(!0);a.options.remove&&a.elem.detach();a.runModules("beforeDestroy");if(a.options.destroy&&null!==d.notices){var c=
b.inArray(a,d.notices);-1!==c&&d.notices.splice(c,1)}a.runModules("afterDestroy")});return this},get:function(){return this.elem},parseOptions:function(c,a){this.options=b.extend(!0,{},d.prototype.options);this.options.stack=d.prototype.options.stack;for(var p=[c,a],m,f=0;f<p.length;f++){m=p[f];if("undefined"===typeof m)break;if("object"!==typeof m)this.options.text=m;else for(var e in m)this.modules[e]?b.extend(!0,this.options[e],m[e]):this.options[e]=m[e]}},animateIn:function(c){this.animating=
"in";var a=this;c=function(){a.animTimer&&clearTimeout(a.animTimer);"in"===a.animating&&(a.elem.is(":visible")?(this&&this.call(),a.animating=!1):a.animTimer=setTimeout(c,40))}.bind(c);"fade"===this.options.animation?(this.elem.one("webkitTransitionEnd mozTransitionEnd MSTransitionEnd oTransitionEnd transitionend",c).addClass("ui-pnotify-in"),this.elem.css("opacity"),this.elem.addClass("ui-pnotify-fade-in"),this.animTimer=setTimeout(c,650)):(this.elem.addClass("ui-pnotify-in"),c())},animateOut:function(c){this.animating=
"out";var a=this;c=function(){a.animTimer&&clearTimeout(a.animTimer);"out"===a.animating&&("0"!=a.elem.css("opacity")&&a.elem.is(":visible")?a.animTimer=setTimeout(c,40):(a.elem.removeClass("ui-pnotify-in"),this&&this.call(),a.animating=!1))}.bind(c);"fade"===this.options.animation?(this.elem.one("webkitTransitionEnd mozTransitionEnd MSTransitionEnd oTransitionEnd transitionend",c).removeClass("ui-pnotify-fade-in"),this.animTimer=setTimeout(c,650)):(this.elem.removeClass("ui-pnotify-in"),c())},position:function(c){var a=
this.options.stack,b=this.elem;"undefined"===typeof a.context&&(a.context=h);if(a){"number"!==typeof a.nextpos1&&(a.nextpos1=a.firstpos1);"number"!==typeof a.nextpos2&&(a.nextpos2=a.firstpos2);"number"!==typeof a.addpos2&&(a.addpos2=0);var d=!b.hasClass("ui-pnotify-in");if(!d||c){a.modal&&(a.overlay?a.overlay.show():a.overlay=s(a));b.addClass("ui-pnotify-move");var f;switch(a.dir1){case "down":f="top";break;case "up":f="bottom";break;case "left":f="right";break;case "right":f="left"}c=parseInt(b.css(f).replace(/(?:\..*|[^0-9.])/g,
""));isNaN(c)&&(c=0);"undefined"!==typeof a.firstpos1||d||(a.firstpos1=c,a.nextpos1=a.firstpos1);var e;switch(a.dir2){case "down":e="top";break;case "up":e="bottom";break;case "left":e="right";break;case "right":e="left"}c=parseInt(b.css(e).replace(/(?:\..*|[^0-9.])/g,""));isNaN(c)&&(c=0);"undefined"!==typeof a.firstpos2||d||(a.firstpos2=c,a.nextpos2=a.firstpos2);if("down"===a.dir1&&a.nextpos1+b.height()>(a.context.is(h)?n.height():a.context.prop("scrollHeight"))||"up"===a.dir1&&a.nextpos1+b.height()>
(a.context.is(h)?n.height():a.context.prop("scrollHeight"))||"left"===a.dir1&&a.nextpos1+b.width()>(a.context.is(h)?n.width():a.context.prop("scrollWidth"))||"right"===a.dir1&&a.nextpos1+b.width()>(a.context.is(h)?n.width():a.context.prop("scrollWidth")))a.nextpos1=a.firstpos1,a.nextpos2+=a.addpos2+("undefined"===typeof a.spacing2?25:a.spacing2),a.addpos2=0;"number"===typeof a.nextpos2&&(a.animation?b.css(e,a.nextpos2+"px"):(b.removeClass("ui-pnotify-move"),b.css(e,a.nextpos2+"px"),b.css(e),b.addClass("ui-pnotify-move")));
switch(a.dir2){case "down":case "up":b.outerHeight(!0)>a.addpos2&&(a.addpos2=b.height());break;case "left":case "right":b.outerWidth(!0)>a.addpos2&&(a.addpos2=b.width())}"number"===typeof a.nextpos1&&(a.animation?b.css(f,a.nextpos1+"px"):(b.removeClass("ui-pnotify-move"),b.css(f,a.nextpos1+"px"),b.css(f),b.addClass("ui-pnotify-move")));switch(a.dir1){case "down":case "up":a.nextpos1+=b.height()+("undefined"===typeof a.spacing1?25:a.spacing1);break;case "left":case "right":a.nextpos1+=b.width()+("undefined"===
typeof a.spacing1?25:a.spacing1)}}return this}},queuePosition:function(b,a){g&&clearTimeout(g);a||(a=10);g=setTimeout(function(){d.positionAll(b)},a);return this},cancelRemove:function(){this.timer&&l.clearTimeout(this.timer);this.animTimer&&l.clearTimeout(this.animTimer);"closing"===this.state&&(this.state="open",this.animating=!1,this.elem.addClass("ui-pnotify-in"),"fade"===this.options.animation&&this.elem.addClass("ui-pnotify-fade-in"));return this},queueRemove:function(){var b=this;this.cancelRemove();
this.timer=l.setTimeout(function(){b.remove(!0)},isNaN(this.options.delay)?0:this.options.delay);return this}});b.extend(d,{notices:[],reload:q,removeAll:function(){b.each(d.notices,function(){this.remove&&this.remove(!1)})},removeStack:function(c){b.each(d.notices,function(){this.remove&&this.options.stack===c&&this.remove(!1)})},positionAll:function(c){g&&clearTimeout(g);g=null;if(d.notices&&d.notices.length)b.each(d.notices,function(){var a=this.options.stack;a&&(a.overlay&&a.overlay.hide(),a.nextpos1=
a.firstpos1,a.nextpos2=a.firstpos2,a.addpos2=0,a.animation=c)}),b.each(d.notices,function(){this.position()});else{var a=d.prototype.options.stack;a&&(delete a.nextpos1,delete a.nextpos2)}},styling:{brighttheme:{container:"brighttheme",notice:"brighttheme-notice",notice_icon:"brighttheme-icon-notice",info:"brighttheme-info",info_icon:"brighttheme-icon-info",success:"brighttheme-success",success_icon:"brighttheme-icon-success",error:"brighttheme-error",error_icon:"brighttheme-icon-error"},jqueryui:{container:"ui-widget ui-widget-content ui-corner-all",
notice:"ui-state-highlight",notice_icon:"ui-icon ui-icon-info",info:"",info_icon:"ui-icon ui-icon-info",success:"ui-state-default",success_icon:"ui-icon ui-icon-circle-check",error:"ui-state-error",error_icon:"ui-icon ui-icon-alert"},bootstrap3:{container:"alert",notice:"alert-warning",notice_icon:"glyphicon glyphicon-exclamation-sign",info:"alert-info",info_icon:"glyphicon glyphicon-info-sign",success:"alert-success",success_icon:"glyphicon glyphicon-ok-sign",error:"alert-danger",error_icon:"glyphicon glyphicon-warning-sign"}}});
d.styling.fontawesome=b.extend({},d.styling.bootstrap3);b.extend(d.styling.fontawesome,{notice_icon:"fa fa-exclamation-circle",info_icon:"fa fa-info",success_icon:"fa fa-check",error_icon:"fa fa-warning"});l.document.body?r():b(r);return d};return q(k)});
(function(d,e){"function"===typeof define&&define.amd?define("pnotify.buttons",["jquery","pnotify"],e):"object"===typeof exports&&"undefined"!==typeof module?module.exports=e(require("jquery"),require("./pnotify")):e(d.jQuery,d.PNotify)})(this,function(d,e){e.prototype.options.buttons={closer:!0,closer_hover:!0,sticker:!0,sticker_hover:!0,show_on_nonblock:!1,labels:{close:"Close",stick:"Stick",unstick:"Unstick"},classes:{closer:null,pin_up:null,pin_down:null}};e.prototype.modules.buttons={closer:null,
sticker:null,init:function(a,b){var c=this;a.elem.on({mouseenter:function(b){!c.options.sticker||a.options.nonblock&&a.options.nonblock.nonblock&&!c.options.show_on_nonblock||c.sticker.trigger("pnotify:buttons:toggleStick").css("visibility","visible");!c.options.closer||a.options.nonblock&&a.options.nonblock.nonblock&&!c.options.show_on_nonblock||c.closer.css("visibility","visible")},mouseleave:function(a){c.options.sticker_hover&&c.sticker.css("visibility","hidden");c.options.closer_hover&&c.closer.css("visibility",
"hidden")}});this.sticker=d("<div />",{"class":"ui-pnotify-sticker","aria-role":"button","aria-pressed":a.options.hide?"false":"true",tabindex:"0",title:a.options.hide?b.labels.stick:b.labels.unstick,css:{cursor:"pointer",visibility:b.sticker_hover?"hidden":"visible"},click:function(){a.options.hide=!a.options.hide;a.options.hide?a.queueRemove():a.cancelRemove();d(this).trigger("pnotify:buttons:toggleStick")}}).bind("pnotify:buttons:toggleStick",function(){var b=null===c.options.classes.pin_up?a.styles.pin_up:
c.options.classes.pin_up,e=null===c.options.classes.pin_down?a.styles.pin_down:c.options.classes.pin_down;d(this).attr("title",a.options.hide?c.options.labels.stick:c.options.labels.unstick).children().attr("class","").addClass(a.options.hide?b:e).attr("aria-pressed",a.options.hide?"false":"true")}).append("<span />").trigger("pnotify:buttons:toggleStick").prependTo(a.container);(!b.sticker||a.options.nonblock&&a.options.nonblock.nonblock&&!b.show_on_nonblock)&&this.sticker.css("display","none");
this.closer=d("<div />",{"class":"ui-pnotify-closer","aria-role":"button",tabindex:"0",title:b.labels.close,css:{cursor:"pointer",visibility:b.closer_hover?"hidden":"visible"},click:function(){a.remove(!1);c.sticker.css("visibility","hidden");c.closer.css("visibility","hidden")}}).append(d("<span />",{"class":null===b.classes.closer?a.styles.closer:b.classes.closer})).prependTo(a.container);(!b.closer||a.options.nonblock&&a.options.nonblock.nonblock&&!b.show_on_nonblock)&&this.closer.css("display",
"none")},update:function(a,b){!b.closer||a.options.nonblock&&a.options.nonblock.nonblock&&!b.show_on_nonblock?this.closer.css("display","none"):b.closer&&this.closer.css("display","block");!b.sticker||a.options.nonblock&&a.options.nonblock.nonblock&&!b.show_on_nonblock?this.sticker.css("display","none"):b.sticker&&this.sticker.css("display","block");this.sticker.trigger("pnotify:buttons:toggleStick");this.closer.find("span").attr("class","").addClass(null===b.classes.closer?a.styles.closer:b.classes.closer);
b.sticker_hover?this.sticker.css("visibility","hidden"):a.options.nonblock&&a.options.nonblock.nonblock&&!b.show_on_nonblock||this.sticker.css("visibility","visible");b.closer_hover?this.closer.css("visibility","hidden"):a.options.nonblock&&a.options.nonblock.nonblock&&!b.show_on_nonblock||this.closer.css("visibility","visible")}};d.extend(e.styling.brighttheme,{closer:"brighttheme-icon-closer",pin_up:"brighttheme-icon-sticker",pin_down:"brighttheme-icon-sticker brighttheme-icon-stuck"});d.extend(e.styling.jqueryui,
{closer:"ui-icon ui-icon-close",pin_up:"ui-icon ui-icon-pin-w",pin_down:"ui-icon ui-icon-pin-s"});d.extend(e.styling.bootstrap2,{closer:"icon-remove",pin_up:"icon-pause",pin_down:"icon-play"});d.extend(e.styling.bootstrap3,{closer:"glyphicon glyphicon-remove",pin_up:"glyphicon glyphicon-pause",pin_down:"glyphicon glyphicon-play"});d.extend(e.styling.fontawesome,{closer:"fa fa-times",pin_up:"fa fa-pause",pin_down:"fa fa-play"})});
(function(b,a){"function"===typeof define&&define.amd?define("pnotify.callbacks",["jquery","pnotify"],a):"object"===typeof exports&&"undefined"!==typeof module?module.exports=a(require("jquery"),require("./pnotify")):a(b.jQuery,b.PNotify)})(this,function(b,a){var c=a.prototype.init,d=a.prototype.open,e=a.prototype.remove;a.prototype.init=function(){this.options.before_init&&this.options.before_init(this.options);c.apply(this,arguments);this.options.after_init&&this.options.after_init(this)};a.prototype.open=
function(){var a;this.options.before_open&&(a=this.options.before_open(this));!1!==a&&(d.apply(this,arguments),this.options.after_open&&this.options.after_open(this))};a.prototype.remove=function(a){var b;this.options.before_close&&(b=this.options.before_close(this,a));!1!==b&&(e.apply(this,arguments),this.options.after_close&&this.options.after_close(this,a))}});
(function(e,c){"function"===typeof define&&define.amd?define("pnotify.confirm",["jquery","pnotify"],c):"object"===typeof exports&&"undefined"!==typeof module?module.exports=c(require("jquery"),require("./pnotify")):c(e.jQuery,e.PNotify)})(this,function(e,c){c.prototype.options.confirm={confirm:!1,prompt:!1,prompt_class:"",prompt_default:"",prompt_multi_line:!1,align:"right",buttons:[{text:"Ok",addClass:"",promptTrigger:!0,click:function(b,a){b.remove();b.get().trigger("pnotify.confirm",[b,a])}},{text:"Cancel",
addClass:"",click:function(b){b.remove();b.get().trigger("pnotify.cancel",b)}}]};c.prototype.modules.confirm={container:null,prompt:null,init:function(b,a){this.container=e('<div class="ui-pnotify-action-bar" style="margin-top:5px;clear:both;" />').css("text-align",a.align).appendTo(b.container);a.confirm||a.prompt?this.makeDialog(b,a):this.container.hide()},update:function(b,a){a.confirm?(this.makeDialog(b,a),this.container.show()):this.container.hide().empty()},afterOpen:function(b,a){a.prompt&&
this.prompt.focus()},makeDialog:function(b,a){var h=!1,l=this,g,d;this.container.empty();a.prompt&&(this.prompt=e("<"+(a.prompt_multi_line?'textarea rows="5"':'input type="text"')+' style="margin-bottom:5px;clear:both;" />').addClass(("undefined"===typeof b.styles.input?"":b.styles.input)+" "+("undefined"===typeof a.prompt_class?"":a.prompt_class)).val(a.prompt_default).appendTo(this.container));for(var m=a.buttons[0]&&a.buttons[0]!==c.prototype.options.confirm.buttons[0],f=0;f<a.buttons.length;f++)if(!(null===
a.buttons[f]||m&&c.prototype.options.confirm.buttons[f]&&c.prototype.options.confirm.buttons[f]===a.buttons[f])){g=a.buttons[f];h?this.container.append(" "):h=!0;d=e('<button type="button" class="ui-pnotify-action-button" />').addClass(("undefined"===typeof b.styles.btn?"":b.styles.btn)+" "+("undefined"===typeof g.addClass?"":g.addClass)).text(g.text).appendTo(this.container).on("click",function(k){return function(){"function"==typeof k.click&&k.click(b,a.prompt?l.prompt.val():null)}}(g));a.prompt&&
!a.prompt_multi_line&&g.promptTrigger&&this.prompt.keypress(function(b){return function(a){13==a.keyCode&&b.click()}}(d));b.styles.text&&d.wrapInner('<span class="'+b.styles.text+'"></span>');b.styles.btnhover&&d.hover(function(a){return function(){a.addClass(b.styles.btnhover)}}(d),function(a){return function(){a.removeClass(b.styles.btnhover)}}(d));if(b.styles.btnactive)d.on("mousedown",function(a){return function(){a.addClass(b.styles.btnactive)}}(d)).on("mouseup",function(a){return function(){a.removeClass(b.styles.btnactive)}}(d));
if(b.styles.btnfocus)d.on("focus",function(a){return function(){a.addClass(b.styles.btnfocus)}}(d)).on("blur",function(a){return function(){a.removeClass(b.styles.btnfocus)}}(d))}}};e.extend(c.styling.jqueryui,{btn:"ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only",btnhover:"ui-state-hover",btnactive:"ui-state-active",btnfocus:"ui-state-focus",input:"",text:"ui-button-text"});e.extend(c.styling.bootstrap2,{btn:"btn",input:""});e.extend(c.styling.bootstrap3,{btn:"btn btn-default",
input:"form-control"});e.extend(c.styling.fontawesome,{btn:"btn btn-default",input:"form-control"})});
(function(e,c){"function"===typeof define&&define.amd?define("pnotify.desktop",["jquery","pnotify"],c):"object"===typeof exports&&"undefined"!==typeof module?module.exports=c(require("jquery"),require("./pnotify")):c(e.jQuery,e.PNotify)})(this,function(e,c){var d,f=function(a,b){f="Notification"in window?function(a,b){return new Notification(a,b)}:"mozNotification"in navigator?function(a,b){return navigator.mozNotification.createNotification(a,b.body,b.icon).show()}:"webkitNotifications"in window?
function(a,b){return window.webkitNotifications.createNotification(b.icon,a,b.body)}:function(a,b){return null};return f(a,b)};c.prototype.options.desktop={desktop:!1,fallback:!0,icon:null,tag:null};c.prototype.modules.desktop={tag:null,icon:null,genNotice:function(a,b){this.icon=null===b.icon?"http://sciactive.com/pnotify/includes/desktop/"+a.options.type+".png":!1===b.icon?null:b.icon;if(null===this.tag||null!==b.tag)this.tag=null===b.tag?"PNotify-"+Math.round(1E6*Math.random()):b.tag;a.desktop=
f(a.options.title,{icon:this.icon,body:b.text||a.options.text,tag:this.tag});!("close"in a.desktop)&&"cancel"in a.desktop&&(a.desktop.close=function(){a.desktop.cancel()});a.desktop.onclick=function(){a.elem.trigger("click")};a.desktop.onclose=function(){"closing"!==a.state&&"closed"!==a.state&&a.remove()}},init:function(a,b){b.desktop&&(d=c.desktop.checkPermission(),0!==d?b.fallback||(a.options.auto_display=!1):this.genNotice(a,b))},update:function(a,b,c){0!==d&&b.fallback||!b.desktop||this.genNotice(a,
b)},beforeOpen:function(a,b){0!==d&&b.fallback||!b.desktop||a.elem.css({left:"-10000px"}).removeClass("ui-pnotify-in")},afterOpen:function(a,b){0!==d&&b.fallback||!b.desktop||(a.elem.css({left:"-10000px"}).removeClass("ui-pnotify-in"),"show"in a.desktop&&a.desktop.show())},beforeClose:function(a,b){0!==d&&b.fallback||!b.desktop||a.elem.css({left:"-10000px"}).removeClass("ui-pnotify-in")},afterClose:function(a,b){0!==d&&b.fallback||!b.desktop||(a.elem.css({left:"-10000px"}).removeClass("ui-pnotify-in"),
"close"in a.desktop&&a.desktop.close())}};c.desktop={permission:function(){"undefined"!==typeof Notification&&"requestPermission"in Notification?Notification.requestPermission():"webkitNotifications"in window&&window.webkitNotifications.requestPermission()},checkPermission:function(){return"undefined"!==typeof Notification&&"permission"in Notification?"granted"===Notification.permission?0:1:"webkitNotifications"in window?0==window.webkitNotifications.checkPermission()?0:1:1}};d=c.desktop.checkPermission()});
(function(g,c){"function"===typeof define&&define.amd?define("pnotify.mobile",["jquery","pnotify"],c):"object"===typeof exports&&"undefined"!==typeof module?module.exports=c(require("jquery"),require("./pnotify")):c(g.jQuery,g.PNotify)})(this,function(g,c){c.prototype.options.mobile={swipe_dismiss:!0,styling:!0};c.prototype.modules.mobile={swipe_dismiss:!0,init:function(a,b){var c=this,d=null,e=null,f=null;this.swipe_dismiss=b.swipe_dismiss;this.doMobileStyling(a,b);a.elem.on({touchstart:function(b){c.swipe_dismiss&&
(d=b.originalEvent.touches[0].screenX,f=a.elem.width(),a.container.css("left","0"))},touchmove:function(b){d&&c.swipe_dismiss&&(e=b.originalEvent.touches[0].screenX-d,b=(1-Math.abs(e)/f)*a.options.opacity,a.elem.css("opacity",b),a.container.css("left",e))},touchend:function(){if(d&&c.swipe_dismiss){if(40<Math.abs(e)){var b=0>e?-2*f:2*f;a.elem.animate({opacity:0},100);a.container.animate({left:b},100);a.remove()}else a.elem.animate({opacity:a.options.opacity},100),a.container.animate({left:0},100);
f=e=d=null}},touchcancel:function(){d&&c.swipe_dismiss&&(a.elem.animate({opacity:a.options.opacity},100),a.container.animate({left:0},100),f=e=d=null)}})},update:function(a,b){this.swipe_dismiss=b.swipe_dismiss;this.doMobileStyling(a,b)},doMobileStyling:function(a,b){if(b.styling)if(a.elem.addClass("ui-pnotify-mobile-able"),480>=g(window).width())a.options.stack.mobileOrigSpacing1||(a.options.stack.mobileOrigSpacing1=a.options.stack.spacing1,a.options.stack.mobileOrigSpacing2=a.options.stack.spacing2),
a.options.stack.spacing1=0,a.options.stack.spacing2=0;else{if(a.options.stack.mobileOrigSpacing1||a.options.stack.mobileOrigSpacing2)a.options.stack.spacing1=a.options.stack.mobileOrigSpacing1,delete a.options.stack.mobileOrigSpacing1,a.options.stack.spacing2=a.options.stack.mobileOrigSpacing2,delete a.options.stack.mobileOrigSpacing2}else a.elem.removeClass("ui-pnotify-mobile-able"),a.options.stack.mobileOrigSpacing1&&(a.options.stack.spacing1=a.options.stack.mobileOrigSpacing1,delete a.options.stack.mobileOrigSpacing1),
a.options.stack.mobileOrigSpacing2&&(a.options.stack.spacing2=a.options.stack.mobileOrigSpacing2,delete a.options.stack.mobileOrigSpacing2)}}});

/**
 * ViewBox
 * @version 0.2.4
 * @author Pavel Khoroshkov aka pgood
 * @link https://github.com/pgooood/viewbox
 */
(function($){$.fn.viewbox = function(options){
	
	if(typeof(options) === 'undefined')
		options = {};
	
	options = $.extend({
		template: '<div class="viewbox-container"><div class="viewbox-body"><div class="viewbox-header"></div><div class="viewbox-content"></div><div class="viewbox-footer"></div></div></div>'
		,loader: '<div class="loader"><div class="spinner"><div class="double-bounce1"></div><div class="double-bounce2"></div></div></div>'
		,setTitle: true
		,margin: 20
		,resizeDuration: 400
		,openDuration: 200
		,closeDuration: 200
		,closeButton: true
		,fullscreenButton: false
		,navButtons: true
		,closeOnSideClick: true
		,nextOnContentClick: false
		,useGestures: true
	},options);
	
	var $links = $(this)
		,$container = $(options.template)
		,$loader = $(options.loader)
		,state = false
		,locked = false
		,$current
		,arPopupContent = [];
	
	if(!$('#viewbox-sprite').length)	
		$('body').get(0).insertAdjacentHTML('afterbegin','<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" id="viewbox-sprite" style="display:none"><symbol id="viewbox-close-icon" viewBox="0 0 50 50"><path d="M37.304 11.282l1.414 1.414-26.022 26.02-1.414-1.413z"/><path d="M12.696 11.282l26.022 26.02-1.414 1.415-26.022-26.02z"/></symbol><symbol id="viewbox-prev-icon" viewBox="0 0 50 50"><path d="M27.3 34.7L17.6 25l9.7-9.7 1.4 1.4-8.3 8.3 8.3 8.3z"/></symbol><symbol id="viewbox-next-icon" viewBox="0 0 50 50"><path d="M22.7 34.7l-1.4-1.4 8.3-8.3-8.3-8.3 1.4-1.4 9.7 9.7z"/></symbol><symbol id="viewbox-full-screen-icon" viewBox="0 0 50 50"><path d="M8.242 11.387v9.037h2.053v-6.986h9.197v-2.051H8.242zm22.606 0v2.05h9.367v6.987h2.05v-9.037H30.849zM8.242 29.747v8.866h11.25v-2.05h-9.197v-6.817H8.242zm31.973 0v6.816h-9.367v2.05h11.418v-8.867h-2.051z"/></symbol></svg>');
	
	
	$container.bind('viewbox.open',function(event,target){
		if(Number.isInteger(target) && $links.length)
			show($links.eq(target >= 0 && target < $links.length ? target : 0));
		else if(target && target.tagName)
			show($(target));
		else if($links.length)
			show($links.eq(0));
	});
	
	$container.bind('viewbox.next',function(event){
		if($links.length <= 1)
			return;
		var nextIndex = index() + 1;
		if(nextIndex >= $links.length)
			nextIndex = 0;
		show($links.eq(nextIndex));
	});
	
	$container.bind('viewbox.prev',function(event){
		if($links.length <= 1)
			return;
		var nextIndex = index() - 1;
		if(nextIndex < 0)
			nextIndex = $links.length - 1;
		show($links.eq(nextIndex));
	});
	
	$container.bind('viewbox.close',function(event){
		if(state){
			$container.fadeOut(options.closeDuration,function(){
				state = false;
			});
		};
	});

	$container.bind('viewbox.fullscreen',function(event){
		var e = get('content').find('.viewbox-image').get(0);
		if(e){
			if(e.requestFullscreen) 
				e.requestFullscreen();
			else if(e.mozRequestFullScreen)
				e.mozRequestFullScreen();
			else if(e.webkitRequestFullscreen)
				e.webkitRequestFullscreen();
			else if(e.msRequestFullscreen)
				e.msRequestFullscreen();
		}
	});
	
	function show($e){
		if(locked) return;
		putBackPopupContent();
		var href = $e.attr('href')
			,caption = options.setTitle ? $e.data('viewbox-title') || $e.attr('title') : '';
		if(!href){
			$current = $e;
			showPopup($e,caption);
		}else if(isImage(href)){
			$current = $e;
			showImage(href,caption);
		}else if(isAnchor(href)){
			$current = $e;
			showPopup(href,caption);
		};
	};
		
	function openWindow(width,height){
		var $body = get('body')
			,$content = get('content')
			,$header = get('header')
			,w,h;
		if(width){
			$content.width(width);
			$header.width(width);
		}
		if(height)
			$content.height(height);
		if(!state){
			state = true;
			$('body').append($container);
			$container.show();
			w = $body.width();
			h = $body.height();
			$container.hide();
			$container.fadeIn(options.openDuration);
		}else{
			w = $body.width();
			h = $body.height();
		};
		$body.css({
			'margin-left': -w/2
			,'margin-top': -h/2
		});
	};
	
	function get(name){
		return $container.find('.viewbox-'+name);
	};
	
	function set(name,value){
		get(name).html(value);
	};
	
	function index(){
		var index = -1;
		if($current){
			$links.each(function(i){
				if($current.is(this)){
					index = i;
					return false;
				};
			});
		};
		return index;
	};
	
	function isImage(href){
		if(href)
			return href.match(/(png|jpg|jpeg|gif)(\?.*)?$/i);
	};
	
	function isAnchor(href){
		return href.match(/^#.+$/i) && $(href).length;
	}
	
	function isImageLoaded($img){
		return $img.get(0).complete;
	};
	
	function loader(state){
		if(state)
			$loader.appendTo(get('body'));
		else
			$loader.detach();
	};
	
	function addSvgButton(name){
		var $e = $('<div class="viewbox-button-default viewbox-button-'+name+'"></div>')
			,href = window.location.pathname+window.location.search+'#viewbox-'+name+'-icon';
		$e.appendTo($container)
			.get(0)
			.insertAdjacentHTML('afterbegin','<svg><use xlink:href="'+href+'"/></svg>');
		return $e;
	};
	
	function showPopup(href,caption){
		var $eContent = $(href)
			,$ePlaceholder = $('<div class="viewbox-content-placeholder"></div>');
		$eContent.before($ePlaceholder);
		if(state)
			$container.trigger('viewbox.close');
		set('content','');
		set('header',caption);
		get('content').append($eContent);
		openWindow('auto','auto');
		arPopupContent.push({
			placeholder: $ePlaceholder
			,content: $eContent
		});
	};
	
	function putBackPopupContent(){
		var ob;
		while(arPopupContent.length){
			ob = arPopupContent.shift();
			ob.placeholder.before(ob.content);
			ob.placeholder.detach();
		}
	};
	
	function showImage(href,caption){
		var $img = $('<img class="viewbox-image" alt="">').attr('src',href);
		if(!isImageLoaded($img))
			loader(true);
		set('content','');
		set('header','');
		openWindow();
		var $body = get('body')
			,counter = 0
			,$content = get('content')
			,$header = get('header')
			,timerId = window.setInterval(function(){
				if(!isImageLoaded($img) && counter < 1000){
					counter++;
					return;
				};

				window.clearInterval(timerId);
				loader(false);

				$('body').append($img);

				$header.hide();

				var wOffset = $body.width() - $content.width() + options.margin*2
					,hOffset = $body.height() - $content.height() + options.margin*2
					,windowWidth = $(window).width() - wOffset
					,windowHeight = $(window).height() - hOffset
					,w = $img.width()
					,h = $img.height();
				$img.detach();

				if(w > windowWidth){
					h = h * windowWidth / w;
					w = windowWidth;
				};
				if(h > windowHeight){
					w = w * windowHeight / h;
					h = windowHeight;
				};
				locked = true;
				$body.animate(
					{
						'margin-left': -(w + wOffset)/2 + options.margin
						,'margin-top': -(h + hOffset)/2 + options.margin
					}
					,options.resizeDuration
				);
				$content.animate(
					{width: w,height: h}
					,options.resizeDuration
					,function(){
						$content.append($img);
						$header.show().width(w);
						set('header',caption);
						locked = false;
					}
				);
			},isImageLoaded($img) ? 0 : 200);
	};
	
	function onSwipe($e,callback){
  		if(typeof(callback) != 'function')
			return;
		var startPos,startTime
			,threshold = 150
			,restraint = 100
			,allowedTime = 300;
		$e.on('touchstart',function(event){
			var touch = event.originalEvent.changedTouches[0];
			startPos = {x:touch.pageX,y:touch.pageY};
			startTime = new Date().getTime();
		});
		$e.on('touchend',function(event){
			var touch = event.originalEvent.changedTouches[0]
				,swipedir = 'none'
				,elapsedTime = new Date().getTime() - startTime
				,endPos = {x:touch.pageX,y:touch.pageY}
				,dist = {
					x:endPos.x - startPos.x
					,y:endPos.y - startPos.y
				};	
			if(elapsedTime <= allowedTime){
				if(Math.abs(dist.x) >= threshold && Math.abs(dist.y) <= restraint)
					swipedir = dist.x < 0 ? 'left' : 'right';
				else if (Math.abs(dist.y) >= threshold && Math.abs(dist.x) <= restraint)
					swipedir = dist.y < 0 ? 'up' : 'down';
			};
			callback.call(this,swipedir);
		});
	};
	
	$links.filter('a').click(function(){
		$container.trigger('viewbox.open',[this]);
		return false;
	});
	
	get('body').click(function(event){
		event.stopPropagation();
		if(options.nextOnContentClick)
			$container.trigger('viewbox.next');
	});
	
	if(options.closeButton){
		addSvgButton('close').click(function(event){
			event.stopPropagation();
			$container.trigger('viewbox.close');
		});
	};
	
	if(options.navButtons && $links.length > 1){
		addSvgButton('next').click(function(event){
			event.stopPropagation();
			$container.trigger('viewbox.next');
		});
		addSvgButton('prev').click(function(event){
			event.stopPropagation();
			$container.trigger('viewbox.prev');
		});
	};

	if(options.fullscreenButton){
		var $fullScreenBtn = addSvgButton('full-screen').click(function(event){
			event.stopPropagation();
			$container.trigger('viewbox.fullscreen');
		});
		$container.on('viewbox.open',function(viewbox,target){
			if(isImage($(target).attr('href')))
				$fullScreenBtn.show();
			else
				$fullScreenBtn.hide();
		});
	}
	
	if(options.closeOnSideClick){
		$container.click(function(){
			$container.trigger('viewbox.close');
		});
	};
	
	if(options.useGestures && 'ontouchstart' in document.documentElement){
		onSwipe($container,function(dir){
			switch(dir){
				case 'left':$container.trigger('viewbox.next');break;
				case 'right':$container.trigger('viewbox.prev');break;
			};
		});
	};
	
	return $container;
	
};}(jQuery));
/*
	Base.js, version 1.1a
	Copyright 2006-2010, Dean Edwards
	License: http://www.opensource.org/licenses/mit-license.php
*/

var Base = function() {
	// dummy
};

Base.extend = function(_instance, _static) { // subclass
	
	"use strict";
	
	var extend = Base.prototype.extend;
	
	// build the prototype
	Base._prototyping = true;
	
	var proto = new this();
	
	extend.call(proto, _instance);
	
	proto.base = function() {
	// call this method from any other method to invoke that method's ancestor
	};

	delete Base._prototyping;
	
	// create the wrapper for the constructor function
	//var constructor = proto.constructor.valueOf(); //-dean
	var constructor = proto.constructor;
	var klass = proto.constructor = function() {
		if (!Base._prototyping) {
			if (this._constructing || this.constructor == klass) { // instantiation
				this._constructing = true;
				constructor.apply(this, arguments);
				delete this._constructing;
			} else if (arguments[0] !== null) { // casting
				return (arguments[0].extend || extend).call(arguments[0], proto);
			}
		}
	};
	
	// build the class interface
	klass.ancestor = this;
	klass.extend = this.extend;
	klass.forEach = this.forEach;
	klass.implement = this.implement;
	klass.prototype = proto;
	klass.toString = this.toString;
	klass.valueOf = function(type) {
		//return (type == "object") ? klass : constructor; //-dean
		return (type == "object") ? klass : constructor.valueOf();
	};
	extend.call(klass, _static);
	// class initialisation
	if (typeof klass.init == "function") klass.init();
	return klass;
};

Base.prototype = {	
	extend: function(source, value) {
		if (arguments.length > 1) { // extending with a name/value pair
			var ancestor = this[source];
			if (ancestor && (typeof value == "function") && // overriding a method?
				// the valueOf() comparison is to avoid circular references
				(!ancestor.valueOf || ancestor.valueOf() != value.valueOf()) &&
				/\bbase\b/.test(value)) {
				// get the underlying method
				var method = value.valueOf();
				// override
				value = function() {
					var previous = this.base || Base.prototype.base;
					this.base = ancestor;
					var returnValue = method.apply(this, arguments);
					this.base = previous;
					return returnValue;
				};
				// point to the underlying method
				value.valueOf = function(type) {
					return (type == "object") ? value : method;
				};
				value.toString = Base.toString;
			}
			this[source] = value;
		} else if (source) { // extending with an object literal
			var extend = Base.prototype.extend;
			// if this object has a customised extend method then use it
			if (!Base._prototyping && typeof this != "function") {
				extend = this.extend || extend;
			}
			var proto = {toSource: null};
			// do the "toString" and other methods manually
			var hidden = ["constructor", "toString", "valueOf"];
			// if we are prototyping then include the constructor
			var i = Base._prototyping ? 0 : 1;
			while (key = hidden[i++]) {
				if (source[key] != proto[key]) {
					extend.call(this, key, source[key]);

				}
			}
			// copy each of the source object's properties to this object
			for (var key in source) {
				if (!proto[key]) extend.call(this, key, source[key]);
			}
		}
		return this;
	}
};

// initialise
Base = Base.extend({
	constructor: function() {
		this.extend(arguments[0]);
	}
}, {
	ancestor: Object,
	version: "1.1",
	
	forEach: function(object, block, context) {
		for (var key in object) {
			if (this.prototype[key] === undefined) {
				block.call(context, object[key], key, object);
			}
		}
	},
		
	implement: function() {
		for (var i = 0; i < arguments.length; i++) {
			if (typeof arguments[i] == "function") {
				// if it's a function, call it
				arguments[i](this.prototype);
			} else {
				// add the interface using the extend method
				this.prototype.extend(arguments[i]);
			}
		}
		return this;
	},
	
	toString: function() {
		return String(this.valueOf());
	}
});
/*jshint smarttabs:true */

var FlipClock;
	
/**
 * FlipClock.js
 *
 * @author     Justin Kimbrell
 * @copyright  2013 - Objective HTML, LLC
 * @licesnse   http://www.opensource.org/licenses/mit-license.php
 */
	
(function($) {
	
	"use strict";
	
	/**
	 * FlipFlock Helper
	 *
	 * @param  object  A jQuery object or CSS select
	 * @param  int     An integer used to start the clock (no. seconds)
	 * @param  object  An object of properties to override the default	
	 */
	 
	FlipClock = function(obj, digit, options) {
		if(digit instanceof Object && digit instanceof Date === false) {
			options = digit;
			digit = 0;
		}

		return new FlipClock.Factory(obj, digit, options);
	};

	/**
	 * The global FlipClock.Lang object
	 */

	FlipClock.Lang = {};
	
	/**
	 * The Base FlipClock class is used to extend all other FlipFlock
	 * classes. It handles the callbacks and the basic setters/getters
	 *	
	 * @param 	object  An object of the default properties
	 * @param 	object  An object of properties to override the default	
	 */

	FlipClock.Base = Base.extend({
		
		/**
		 * Build Date
		 */
		 
		buildDate: '2014-12-12',
		
		/**
		 * Version
		 */
		 
		version: '0.7.7',
		
		/**
		 * Sets the default options
		 *
		 * @param	object 	The default options
		 * @param	object 	The override options
		 */
		 
		constructor: function(_default, options) {
			if(typeof _default !== "object") {
				_default = {};
			}
			if(typeof options !== "object") {
				options = {};
			}
			this.setOptions($.extend(true, {}, _default, options));
		},
		
		/**
		 * Delegates the callback to the defined method
		 *
		 * @param	object 	The default options
		 * @param	object 	The override options
		 */
		 
		callback: function(method) {
		 	if(typeof method === "function") {
				var args = [];
								
				for(var x = 1; x <= arguments.length; x++) {
					if(arguments[x]) {
						args.push(arguments[x]);
					}
				}
				
				method.apply(this, args);
			}
		},
		 
		/**
		 * Log a string into the console if it exists
		 *
		 * @param 	string 	The name of the option
		 * @return	mixed
		 */		
		 
		log: function(str) {
			if(window.console && console.log) {
				console.log(str);
			}
		},
		 
		/**
		 * Get an single option value. Returns false if option does not exist
		 *
		 * @param 	string 	The name of the option
		 * @return	mixed
		 */		
		 
		getOption: function(index) {
			if(this[index]) {
				return this[index];
			}
			return false;
		},
		
		/**
		 * Get all options
		 *
		 * @return	bool
		 */		
		 
		getOptions: function() {
			return this;
		},
		
		/**
		 * Set a single option value
		 *
		 * @param 	string 	The name of the option
		 * @param 	mixed 	The value of the option
		 */		
		 
		setOption: function(index, value) {
			this[index] = value;
		},
		
		/**
		 * Set a multiple options by passing a JSON object
		 *
		 * @param 	object 	The object with the options
		 * @param 	mixed 	The value of the option
		 */		
		
		setOptions: function(options) {
			for(var key in options) {
	  			if(typeof options[key] !== "undefined") {
		  			this.setOption(key, options[key]);
		  		}
		  	}
		}
		
	});
	
}(jQuery));

/*jshint smarttabs:true */

/**
 * FlipClock.js
 *
 * @author     Justin Kimbrell
 * @copyright  2013 - Objective HTML, LLC
 * @licesnse   http://www.opensource.org/licenses/mit-license.php
 */
	
(function($) {
	
	"use strict";
	
	/**
	 * The FlipClock Face class is the base class in which to extend
	 * all other FlockClock.Face classes.
	 *
	 * @param 	object  The parent FlipClock.Factory object
	 * @param 	object  An object of properties to override the default	
	 */
	 
	FlipClock.Face = FlipClock.Base.extend({
		
		/**
		 * Sets whether or not the clock should start upon instantiation
		 */
		 
		autoStart: true,

		/**
		 * An array of jQuery objects used for the dividers (the colons)
		 */
		 
		dividers: [],

		/**
		 * An array of FlipClock.List objects
		 */		
		 
		factory: false,
		
		/**
		 * An array of FlipClock.List objects
		 */		
		 
		lists: [],

		/**
		 * Constructor
		 *
		 * @param 	object  The parent FlipClock.Factory object
		 * @param 	object  An object of properties to override the default	
		 */
		 
		constructor: function(factory, options) {
			this.dividers = [];
			this.lists = [];
			this.base(options);
			this.factory = factory;
		},
		
		/**
		 * Build the clock face
		 */
		 
		build: function() {
			if(this.autoStart) {
				this.start();
			}
		},
		
		/**
		 * Creates a jQuery object used for the digit divider
		 *
		 * @param	mixed 	The divider label text
		 * @param	mixed	Set true to exclude the dots in the divider. 
		 *					If not set, is false.
		 */
		 
		createDivider: function(label, css, excludeDots) {
			if(typeof css == "boolean" || !css) {
				excludeDots = css;
				css = label;
			}

			var dots = [
				'<span class="'+this.factory.classes.dot+' top"></span>',
				'<span class="'+this.factory.classes.dot+' bottom"></span>'
			].join('');

			if(excludeDots) {
				dots = '';	
			}

			label = this.factory.localize(label);

			var html = [
				'<span class="'+this.factory.classes.divider+' '+(css ? css : '').toLowerCase()+'">',
					'<span class="'+this.factory.classes.label+'">'+(label ? label : '')+'</span>',
					dots,
				'</span>'
			];	
			
			var $html = $(html.join(''));

			this.dividers.push($html);

			return $html;
		},
		
		/**
		 * Creates a FlipClock.List object and appends it to the DOM
		 *
		 * @param	mixed 	The digit to select in the list
		 * @param	object  An object to override the default properties
		 */
		 
		createList: function(digit, options) {
			if(typeof digit === "object") {
				options = digit;
				digit = 0;
			}

			var obj = new FlipClock.List(this.factory, digit, options);
		
			this.lists.push(obj);

			return obj;
		},
		
		/**
		 * Triggers when the clock is reset
		 */

		reset: function() {
			this.factory.time = new FlipClock.Time(
				this.factory, 
				this.factory.original ? Math.round(this.factory.original) : 0,
				{
					minimumDigits: this.factory.minimumDigits
				}
			);

			this.flip(this.factory.original, false);
		},

		/**
		 * Append a newly created list to the clock
		 */

		appendDigitToClock: function(obj) {
			obj.$el.append(false);
		},

		/**
		 * Add a digit to the clock face
		 */
		 
		addDigit: function(digit) {
			var obj = this.createList(digit, {
				classes: {
					active: this.factory.classes.active,
					before: this.factory.classes.before,
					flip: this.factory.classes.flip
				}
			});

			this.appendDigitToClock(obj);
		},
		
		/**
		 * Triggers when the clock is started
		 */
		 
		start: function() {},
		
		/**
		 * Triggers when the time on the clock stops
		 */
		 
		stop: function() {},
		
		/**
		 * Auto increments/decrements the value of the clock face
		 */
		 
		autoIncrement: function() {
			if(!this.factory.countdown) {
				this.increment();
			}
			else {
				this.decrement();
			}
		},

		/**
		 * Increments the value of the clock face
		 */
		 
		increment: function() {
			this.factory.time.addSecond();
		},

		/**
		 * Decrements the value of the clock face
		 */

		decrement: function() {
			if(this.factory.time.getTimeSeconds() == 0) {
	        	this.factory.stop()
			}
			else {
				this.factory.time.subSecond();
			}
		},
			
		/**
		 * Triggers when the numbers on the clock flip
		 */
		 
		flip: function(time, doNotAddPlayClass) {
			var t = this;

			$.each(time, function(i, digit) {
				var list = t.lists[i];

				if(list) {
					if(!doNotAddPlayClass && digit != list.digit) {
						list.play();	
					}

					list.select(digit);
				}	
				else {
					t.addDigit(digit);
				}
			});
		}
					
	});
	
}(jQuery));

/*jshint smarttabs:true */

/**
 * FlipClock.js
 *
 * @author     Justin Kimbrell
 * @copyright  2013 - Objective HTML, LLC
 * @licesnse   http://www.opensource.org/licenses/mit-license.php
 */
	
(function($) {
	
	"use strict";
	
	/**
	 * The FlipClock Factory class is used to build the clock and manage
	 * all the public methods.
	 *
	 * @param 	object  A jQuery object or CSS selector used to fetch
	 				    the wrapping DOM nodes
	 * @param 	mixed   This is the digit used to set the clock. If an 
	 				    object is passed, 0 will be used.	
	 * @param 	object  An object of properties to override the default	
	 */
	 	
	FlipClock.Factory = FlipClock.Base.extend({
		
		/**
		 * The clock's animation rate.
		 * 
		 * Note, currently this property doesn't do anything.
		 * This property is here to be used in the future to
		 * programmaticaly set the clock's animation speed
		 */		

		animationRate: 1000,

		/**
		 * Auto start the clock on page load (True|False)
		 */	
		 
		autoStart: true,
		
		/**
		 * The callback methods
		 */		
		 
		callbacks: {
			destroy: false,
			create: false,
			init: false,
			interval: false,
			start: false,
			stop: false,
			reset: false
		},
		
		/**
		 * The CSS classes
		 */		
		 
		classes: {
			active: 'flip-clock-active',
			before: 'flip-clock-before',
			divider: 'flip-clock-divider',
			dot: 'flip-clock-dot',
			label: 'flip-clock-label',
			flip: 'flip',
			play: 'play',
			wrapper: 'flip-clock-wrapper'
		},
		
		/**
		 * The name of the clock face class in use
		 */	
		 
		clockFace: 'HourlyCounter',
		 
		/**
		 * The name of the clock face class in use
		 */	
		 
		countdown: false,
		 
		/**
		 * The name of the default clock face class to use if the defined
		 * clockFace variable is not a valid FlipClock.Face object
		 */	
		 
		defaultClockFace: 'HourlyCounter',
		 
		/**
		 * The default language
		 */	
		 
		defaultLanguage: 'english',
		 
		/**
		 * The jQuery object
		 */		
		 
		$el: false,

		/**
		 * The FlipClock.Face object
		 */	
		 
		face: true,
		 
		/**
		 * The language object after it has been loaded
		 */	
		 
		lang: false,
		 
		/**
		 * The language being used to display labels (string)
		 */	
		 
		language: 'english',
		 
		/**
		 * The minimum digits the clock must have
		 */		

		minimumDigits: 0,

		/**
		 * The original starting value of the clock. Used for the reset method.
		 */		
		 
		original: false,
		
		/**
		 * Is the clock running? (True|False)
		 */		
		 
		running: false,
		
		/**
		 * The FlipClock.Time object
		 */		
		 
		time: false,
		
		/**
		 * The FlipClock.Timer object
		 */		
		 
		timer: false,
		
		/**
		 * The jQuery object (depcrecated)
		 */		
		 
		$wrapper: false,
		
		/**
		 * Constructor
		 *
		 * @param   object  The wrapping jQuery object
		 * @param	object  Number of seconds used to start the clock
		 * @param	object 	An object override options
		 */
		 
		constructor: function(obj, digit, options) {

			if(!options) {
				options = {};
			}

			this.lists = [];
			this.running = false;
			this.base(options);	

			this.$el = $(obj).addClass(this.classes.wrapper);

			// Depcrated support of the $wrapper property.
			this.$wrapper = this.$el;

			this.original = (digit instanceof Date) ? digit : (digit ? Math.round(digit) : 0);

			this.time = new FlipClock.Time(this, this.original, {
				minimumDigits: this.minimumDigits,
				animationRate: this.animationRate 
			});

			this.timer = new FlipClock.Timer(this, options);

			this.loadLanguage(this.language);
			
			this.loadClockFace(this.clockFace, options);

			if(this.autoStart) {
				this.start();
			}

		},
		
		/**
		 * Load the FlipClock.Face object
		 *
		 * @param	object  The name of the FlickClock.Face class
		 * @param	object 	An object override options
		 */
		 
		loadClockFace: function(name, options) {	
			var face, suffix = 'Face', hasStopped = false;
			
			name = name.ucfirst()+suffix;

			if(this.face.stop) {
				this.stop();
				hasStopped = true;
			}

			this.$el.html('');

			this.time.minimumDigits = this.minimumDigits;
			
			if(FlipClock[name]) {
				face = new FlipClock[name](this, options);
			}
			else {
				face = new FlipClock[this.defaultClockFace+suffix](this, options);
			}
			
			face.build();

			this.face = face

			if(hasStopped) {
				this.start();
			}
			
			return this.face;
		},
				
		/**
		 * Load the FlipClock.Lang object
		 *
		 * @param	object  The name of the language to load
		 */
		 
		loadLanguage: function(name) {	
			var lang;
			
			if(FlipClock.Lang[name.ucfirst()]) {
				lang = FlipClock.Lang[name.ucfirst()];
			}
			else if(FlipClock.Lang[name]) {
				lang = FlipClock.Lang[name];
			}
			else {
				lang = FlipClock.Lang[this.defaultLanguage];
			}
			
			return this.lang = lang;
		},
					
		/**
		 * Localize strings into various languages
		 *
		 * @param	string  The index of the localized string
		 * @param	object  Optionally pass a lang object
		 */

		localize: function(index, obj) {
			var lang = this.lang;

			if(!index) {
				return null;
			}

			var lindex = index.toLowerCase();

			if(typeof obj == "object") {
				lang = obj;
			}

			if(lang && lang[lindex]) {
				return lang[lindex];
			}

			return index;
		},
		 

		/**
		 * Starts the clock
		 */
		 
		start: function(callback) {
			var t = this;

			if(!t.running && (!t.countdown || t.countdown && t.time.time > 0)) {
				t.face.start(t.time);
				t.timer.start(function() {
					t.flip();
					
					if(typeof callback === "function") {
						callback();
					}	
				});
			}
			else {
				t.log('Trying to start timer when countdown already at 0');
			}
		},
		
		/**
		 * Stops the clock
		 */
		 
		stop: function(callback) {
			this.face.stop();
			this.timer.stop(callback);
			
			for(var x in this.lists) {
				if (this.lists.hasOwnProperty(x)) {
					this.lists[x].stop();
				}
			}	
		},
		
		/**
		 * Reset the clock
		 */
		 
		reset: function(callback) {
			this.timer.reset(callback);
			this.face.reset();
		},
		
		/**
		 * Sets the clock time
		 */
		 
		setTime: function(time) {
			this.time.time = time;
			this.flip(true);		
		},
		
		/**
		 * Get the clock time
		 *
		 * @return  object  Returns a FlipClock.Time object
		 */
		 
		getTime: function(time) {
			return this.time;		
		},
		
		/**
		 * Changes the increment of time to up or down (add/sub)
		 */
		 
		setCountdown: function(value) {
			var running = this.running;
			
			this.countdown = value ? true : false;
				
			if(running) {
				this.stop();
				this.start();
			}
		},
		
		/**
		 * Flip the digits on the clock
		 *
		 * @param  array  An array of digits	 
		 */
		flip: function(doNotAddPlayClass) {	
			this.face.flip(false, doNotAddPlayClass);
		}
		
	});
		
}(jQuery));

/*jshint smarttabs:true */

/**
 * FlipClock.js
 *
 * @author     Justin Kimbrell
 * @copyright  2013 - Objective HTML, LLC
 * @licesnse   http://www.opensource.org/licenses/mit-license.php
 */
	
(function($) {
	
	"use strict";
	
	/**
	 * The FlipClock List class is used to build the list used to create 
	 * the card flip effect. This object fascilates selecting the correct
	 * node by passing a specific digit.
	 *
	 * @param 	object  A FlipClock.Factory object
	 * @param 	mixed   This is the digit used to set the clock. If an 
	 *				    object is passed, 0 will be used.	
	 * @param 	object  An object of properties to override the default	
	 */
	 	
	FlipClock.List = FlipClock.Base.extend({
		
		/**
		 * The digit (0-9)
		 */		
		 
		digit: 0,
		
		/**
		 * The CSS classes
		 */		
		 
		classes: {
			active: 'flip-clock-active',
			before: 'flip-clock-before',
			flip: 'flip'	
		},
				
		/**
		 * The parent FlipClock.Factory object
		 */		
		 
		factory: false,
		
		/**
		 * The jQuery object
		 */		
		 
		$el: false,

		/**
		 * The jQuery object (deprecated)
		 */		
		 
		$obj: false,
		
		/**
		 * The items in the list
		 */		
		 
		items: [],
		
		/**
		 * The last digit
		 */		
		 
		lastDigit: 0,
			
		/**
		 * Constructor
		 *
		 * @param  object  A FlipClock.Factory object
		 * @param  int     An integer use to select the correct digit
		 * @param  object  An object to override the default properties	 
		 */
		 
		constructor: function(factory, digit, options) {
			this.factory = factory;
			this.digit = digit;
			this.lastDigit = digit;
			this.$el = this.createList();
			
			// Depcrated support of the $obj property.
			this.$obj = this.$el;

			if(digit > 0) {
				this.select(digit);
			}

			this.factory.$el.append(this.$el);
		},
		
		/**
		 * Select the digit in the list
		 *
		 * @param  int  A digit 0-9	 
		 */
		 
		select: function(digit) {
			if(typeof digit === "undefined") {
				digit = this.digit;
			}
			else {
				this.digit = digit;
			}

			if(this.digit != this.lastDigit) {
				var $delete = this.$el.find('.'+this.classes.before).removeClass(this.classes.before);

				this.$el.find('.'+this.classes.active).removeClass(this.classes.active)
													  .addClass(this.classes.before);

				this.appendListItem(this.classes.active, this.digit);

				$delete.remove();

				this.lastDigit = this.digit;
			}	
		},
		
		/**
		 * Adds the play class to the DOM object
		 */
		 		
		play: function() {
			this.$el.addClass(this.factory.classes.play);
		},
		
		/**
		 * Removes the play class to the DOM object 
		 */
		 
		stop: function() {
			var t = this;

			setTimeout(function() {
				t.$el.removeClass(t.factory.classes.play);
			}, this.factory.timer.interval);
		},
		
		/**
		 * Creates the list item HTML and returns as a string 
		 */
		 
		createListItem: function(css, value) {
			return [
				'<li class="'+(css ? css : '')+'">',
					'<a href="#">',
						'<div class="up">',
							'<div class="shadow"></div>',
							'<div class="inn">'+(value ? value : '')+'</div>',
						'</div>',
						'<div class="down">',
							'<div class="shadow"></div>',
							'<div class="inn">'+(value ? value : '')+'</div>',
						'</div>',
					'</a>',
				'</li>'
			].join('');
		},

		/**
		 * Append the list item to the parent DOM node 
		 */

		appendListItem: function(css, value) {
			var html = this.createListItem(css, value);

			this.$el.append(html);
		},

		/**
		 * Create the list of digits and appends it to the DOM object 
		 */
		 
		createList: function() {

			var lastDigit = this.getPrevDigit() ? this.getPrevDigit() : this.digit;

			var html = $([
				'<ul class="'+this.classes.flip+' '+(this.factory.running ? this.factory.classes.play : '')+'">',
					this.createListItem(this.classes.before, lastDigit),
					this.createListItem(this.classes.active, this.digit),
				'</ul>'
			].join(''));
					
			return html;
		},

		getNextDigit: function() {
			return this.digit == 9 ? 0 : this.digit + 1;
		},

		getPrevDigit: function() {
			return this.digit == 0 ? 9 : this.digit - 1;
		}

	});
	
	
}(jQuery));

/*jshint smarttabs:true */

/**
 * FlipClock.js
 *
 * @author     Justin Kimbrell
 * @copyright  2013 - Objective HTML, LLC
 * @licesnse   http://www.opensource.org/licenses/mit-license.php
 */
	
(function($) {
	
	"use strict";
	
	/**
	 * Capitalize the first letter in a string
	 *
	 * @return string
	 */
	 
	String.prototype.ucfirst = function() {
		return this.substr(0, 1).toUpperCase() + this.substr(1);
	};
	
	/**
	 * jQuery helper method
	 *
	 * @param  int     An integer used to start the clock (no. seconds)
	 * @param  object  An object of properties to override the default	
	 */
	 
	$.fn.FlipClock = function(digit, options) {	
		return new FlipClock($(this), digit, options);
	};
	
	/**
	 * jQuery helper method
	 *
	 * @param  int     An integer used to start the clock (no. seconds)
	 * @param  object  An object of properties to override the default	
	 */
	 
	$.fn.flipClock = function(digit, options) {
		return $.fn.FlipClock(digit, options);
	};
	
}(jQuery));

/*jshint smarttabs:true */

/**
 * FlipClock.js
 *
 * @author     Justin Kimbrell
 * @copyright  2013 - Objective HTML, LLC
 * @licesnse   http://www.opensource.org/licenses/mit-license.php
 */
	
(function($) {
	
	"use strict";
			
	/**
	 * The FlipClock Time class is used to manage all the time 
	 * calculations.
	 *
	 * @param 	object  A FlipClock.Factory object
	 * @param 	mixed   This is the digit used to set the clock. If an 
	 *				    object is passed, 0 will be used.	
	 * @param 	object  An object of properties to override the default	
	 */
	 	
	FlipClock.Time = FlipClock.Base.extend({
		
		/**
		 * The time (in seconds) or a date object
		 */		
		 
		time: 0,
		
		/**
		 * The parent FlipClock.Factory object
		 */		
		 
		factory: false,
		
		/**
		 * The minimum number of digits the clock face must have
		 */		
		 
		minimumDigits: 0,

		/**
		 * Constructor
		 *
		 * @param  object  A FlipClock.Factory object
		 * @param  int     An integer use to select the correct digit
		 * @param  object  An object to override the default properties	 
		 */
		 
		constructor: function(factory, time, options) {
			if(typeof options != "object") {
				options = {};
			}

			if(!options.minimumDigits) {
				options.minimumDigits = factory.minimumDigits;
			}

			this.base(options);
			this.factory = factory;

			if(time) {
				this.time = time;
			}
		},

		/**
		 * Convert a string or integer to an array of digits
		 *
		 * @param   mixed  String or Integer of digits	 
		 * @return  array  An array of digits 
		 */
		 
		convertDigitsToArray: function(str) {
			var data = [];
			
			str = str.toString();
			
			for(var x = 0;x < str.length; x++) {
				if(str[x].match(/^\d*$/g)) {
					data.push(str[x]);	
				}
			}
			
			return data;
		},
		
		/**
		 * Get a specific digit from the time integer
		 *
		 * @param   int    The specific digit to select from the time	 
		 * @return  mixed  Returns FALSE if no digit is found, otherwise
		 *				   the method returns the defined digit	 
		 */
		 
		digit: function(i) {
			var timeStr = this.toString();
			var length  = timeStr.length;
			
			if(timeStr[length - i])	 {
				return timeStr[length - i];
			}
			
			return false;
		},

		/**
		 * Formats any array of digits into a valid array of digits
		 *
		 * @param   mixed  An array of digits	 
		 * @return  array  An array of digits 
		 */
		 
		digitize: function(obj) {
			var data = [];

			$.each(obj, function(i, value) {
				value = value.toString();
				
				if(value.length == 1) {
					value = '0'+value;
				}
				
				for(var x = 0; x < value.length; x++) {
					data.push(value.charAt(x));
				}				
			});

			if(data.length > this.minimumDigits) {
				this.minimumDigits = data.length;
			}
			
			if(this.minimumDigits > data.length) {
				for(var x = data.length; x < this.minimumDigits; x++) {
					data.unshift('0');
				}
			}

			return data;
		},
		
		/**
		 * Gets a new Date object for the current time
		 *
		 * @return  array  Returns a Date object
		 */

		getDateObject: function() {
			if(this.time instanceof Date) {
				return this.time;
			}

			return new Date((new Date()).getTime() + this.getTimeSeconds() * 1000);
		},
		
		/**
		 * Gets a digitized daily counter
		 *
		 * @return  object  Returns a digitized object
		 */

		getDayCounter: function(includeSeconds) {
			var digits = [
				this.getDays(),
				this.getHours(true),
				this.getMinutes(true)
			];

			if(includeSeconds) {
				digits.push(this.getSeconds(true));
			}

			return this.digitize(digits);
		},

		/**
		 * Gets number of days
		 *
		 * @param   bool  Should perform a modulus? If not sent, then no.
		 * @return  int   Retuns a floored integer
		 */
		 
		getDays: function(mod) {
			var days = this.getTimeSeconds() / 60 / 60 / 24;
			
			if(mod) {
				days = days % 7;
			}
			
			return Math.floor(days);
		},
		
		/**
		 * Gets an hourly breakdown
		 *
		 * @return  object  Returns a digitized object
		 */
		 
		getHourCounter: function() {
			var obj = this.digitize([
				this.getHours(),
				this.getMinutes(true),
				this.getSeconds(true)
			]);
			
			return obj;
		},
		
		/**
		 * Gets an hourly breakdown
		 *
		 * @return  object  Returns a digitized object
		 */
		 
		getHourly: function() {
			return this.getHourCounter();
		},
		
		/**
		 * Gets number of hours
		 *
		 * @param   bool  Should perform a modulus? If not sent, then no.
		 * @return  int   Retuns a floored integer
		 */
		 
		getHours: function(mod) {
			var hours = this.getTimeSeconds() / 60 / 60;
			
			if(mod) {
				hours = hours % 24;	
			}
			
			return Math.floor(hours);
		},
		
		/**
		 * Gets the twenty-four hour time
		 *
		 * @return  object  returns a digitized object
		 */
		 
		getMilitaryTime: function(date, showSeconds) {
			if(typeof showSeconds === "undefined") {
				showSeconds = true;
			}

			if(!date) {
				date = this.getDateObject();
			}

			var data  = [
				date.getHours(),
				date.getMinutes()			
			];

			if(showSeconds === true) {
				data.push(date.getSeconds());
			}

			return this.digitize(data);
		},
				
		/**
		 * Gets number of minutes
		 *
		 * @param   bool  Should perform a modulus? If not sent, then no.
		 * @return  int   Retuns a floored integer
		 */
		 
		getMinutes: function(mod) {
			var minutes = this.getTimeSeconds() / 60;
			
			if(mod) {
				minutes = minutes % 60;
			}
			
			return Math.floor(minutes);
		},
		
		/**
		 * Gets a minute breakdown
		 */
		 
		getMinuteCounter: function() {
			var obj = this.digitize([
				this.getMinutes(),
				this.getSeconds(true)
			]);

			return obj;
		},
		
		/**
		 * Gets time count in seconds regardless of if targetting date or not.
		 *
		 * @return  int   Returns a floored integer
		 */
		 
		getTimeSeconds: function(date) {
			if(!date) {
				date = new Date();
			}

			if (this.time instanceof Date) {
				if (this.factory.countdown) {
					return Math.max(this.time.getTime()/1000 - date.getTime()/1000,0);
				} else {
					return date.getTime()/1000 - this.time.getTime()/1000 ;
				}
			} else {
				return this.time;
			}
		},
		
		/**
		 * Gets the current twelve hour time
		 *
		 * @return  object  Returns a digitized object
		 */
		 
		getTime: function(date, showSeconds) {
			if(typeof showSeconds === "undefined") {
				showSeconds = true;
			}

			if(!date) {
				date = this.getDateObject();
			}

			console.log(date);

			
			var hours = date.getHours();
			var merid = hours > 12 ? 'PM' : 'AM';
			var data   = [
				hours > 12 ? hours - 12 : (hours === 0 ? 12 : hours),
				date.getMinutes()			
			];

			if(showSeconds === true) {
				data.push(date.getSeconds());
			}

			return this.digitize(data);
		},
		
		/**
		 * Gets number of seconds
		 *
		 * @param   bool  Should perform a modulus? If not sent, then no.
		 * @return  int   Retuns a ceiled integer
		 */
		 
		getSeconds: function(mod) {
			var seconds = this.getTimeSeconds();
			
			if(mod) {
				if(seconds == 60) {
					seconds = 0;
				}
				else {
					seconds = seconds % 60;
				}
			}
			
			return Math.ceil(seconds);
		},

		/**
		 * Gets number of weeks
		 *
		 * @param   bool  Should perform a modulus? If not sent, then no.
		 * @return  int   Retuns a floored integer
		 */
		 
		getWeeks: function(mod) {
			var weeks = this.getTimeSeconds() / 60 / 60 / 24 / 7;
			
			if(mod) {
				weeks = weeks % 52;
			}
			
			return Math.floor(weeks);
		},
		
		/**
		 * Removes a specific number of leading zeros from the array.
		 * This method prevents you from removing too many digits, even
		 * if you try.
		 *
		 * @param   int    Total number of digits to remove 
		 * @return  array  An array of digits 
		 */
		 
		removeLeadingZeros: function(totalDigits, digits) {
			var total    = 0;
			var newArray = [];
			
			$.each(digits, function(i, digit) {
				if(i < totalDigits) {
					total += parseInt(digits[i], 10);
				}
				else {
					newArray.push(digits[i]);
				}
			});
			
			if(total === 0) {
				return newArray;
			}
			
			return digits;
		},

		/**
		 * Adds X second to the current time
		 */

		addSeconds: function(x) {
			if(this.time instanceof Date) {
				this.time.setSeconds(this.time.getSeconds() + x);
			}
			else {
				this.time += x;
			}
		},

		/**
		 * Adds 1 second to the current time
		 */

		addSecond: function() {
			this.addSeconds(1);
		},

		/**
		 * Substracts X seconds from the current time
		 */

		subSeconds: function(x) {
			if(this.time instanceof Date) {
				this.time.setSeconds(this.time.getSeconds() - x);
			}
			else {
				this.time -= x;
			}
		},

		/**
		 * Substracts 1 second from the current time
		 */

		subSecond: function() {
			this.subSeconds(1);
		},
		
		/**
		 * Converts the object to a human readable string
		 */
		 
		toString: function() {
			return this.getTimeSeconds().toString();
		}
		
		/*
		getYears: function() {
			return Math.floor(this.time / 60 / 60 / 24 / 7 / 52);
		},
		
		getDecades: function() {
			return Math.floor(this.getWeeks() / 10);
		}*/
	});
	
}(jQuery));

/*jshint smarttabs:true */

/**
 * FlipClock.js
 *
 * @author     Justin Kimbrell
 * @copyright  2013 - Objective HTML, LLC
 * @licesnse   http://www.opensource.org/licenses/mit-license.php
 */
	
(function($) {
	
	"use strict";
	
	/**
	 * The FlipClock.Timer object managers the JS timers
	 *
	 * @param	object  The parent FlipClock.Factory object
	 * @param	object  Override the default options
	 */
	
	FlipClock.Timer = FlipClock.Base.extend({
		
		/**
		 * Callbacks
		 */		
		 
		callbacks: {
			destroy: false,
			create: false,
			init: false,
			interval: false,
			start: false,
			stop: false,
			reset: false
		},
		
		/**
		 * FlipClock timer count (how many intervals have passed)
		 */		
		 
		count: 0,
		
		/**
		 * The parent FlipClock.Factory object
		 */		
		 
		factory: false,
		
		/**
		 * Timer interval (1 second by default)
		 */		
		 
		interval: 1000,

		/**
		 * The rate of the animation in milliseconds (not currently in use)
		 */		
		 
		animationRate: 1000,
				
		/**
		 * Constructor
		 *
		 * @return	void
		 */		
		 
		constructor: function(factory, options) {
			this.base(options);
			this.factory = factory;
			this.callback(this.callbacks.init);	
			this.callback(this.callbacks.create);
		},
		
		/**
		 * This method gets the elapsed the time as an interger
		 *
		 * @return	void
		 */		
		 
		getElapsed: function() {
			return this.count * this.interval;
		},
		
		/**
		 * This method gets the elapsed the time as a Date object
		 *
		 * @return	void
		 */		
		 
		getElapsedTime: function() {
			return new Date(this.time + this.getElapsed());
		},
		
		/**
		 * This method is resets the timer
		 *
		 * @param 	callback  This method resets the timer back to 0
		 * @return	void
		 */		
		 
		reset: function(callback) {
			clearInterval(this.timer);
			this.count = 0;
			this._setInterval(callback);			
			this.callback(this.callbacks.reset);
		},
		
		/**
		 * This method is starts the timer
		 *
		 * @param 	callback  A function that is called once the timer is destroyed
		 * @return	void
		 */		
		 
		start: function(callback) {		
			this.factory.running = true;
			this._createTimer(callback);
			this.callback(this.callbacks.start);
		},
		
		/**
		 * This method is stops the timer
		 *
		 * @param 	callback  A function that is called once the timer is destroyed
		 * @return	void
		 */		
		 
		stop: function(callback) {
			this.factory.running = false;
			this._clearInterval(callback);
			this.callback(this.callbacks.stop);
			this.callback(callback);
		},
		
		/**
		 * Clear the timer interval
		 *
		 * @return	void
		 */		
		 
		_clearInterval: function() {
			clearInterval(this.timer);
		},
		
		/**
		 * Create the timer object
		 *
		 * @param 	callback  A function that is called once the timer is created
		 * @return	void
		 */		
		 
		_createTimer: function(callback) {
			this._setInterval(callback);		
		},
		
		/**
		 * Destroy the timer object
		 *
		 * @param 	callback  A function that is called once the timer is destroyed
		 * @return	void
		 */		
		 	
		_destroyTimer: function(callback) {
			this._clearInterval();			
			this.timer = false;
			this.callback(callback);
			this.callback(this.callbacks.destroy);
		},
		
		/**
		 * This method is called each time the timer interval is ran
		 *
		 * @param 	callback  A function that is called once the timer is destroyed
		 * @return	void
		 */		
		 
		_interval: function(callback) {
			this.callback(this.callbacks.interval);
			this.callback(callback);
			this.count++;
		},
		
		/**
		 * This sets the timer interval
		 *
		 * @param 	callback  A function that is called once the timer is destroyed
		 * @return	void
		 */		
		 
		_setInterval: function(callback) {
			var t = this;
	
			t._interval(callback);

			t.timer = setInterval(function() {		
				t._interval(callback);
			}, this.interval);
		}
			
	});
	
}(jQuery));

(function($) {
	
	/**
	 * Twenty-Four Hour Clock Face
	 *
	 * This class will generate a twenty-four our clock for FlipClock.js
	 *
	 * @param  object  The parent FlipClock.Factory object
	 * @param  object  An object of properties to override the default	
	 */
	 
	FlipClock.TwentyFourHourClockFace = FlipClock.Face.extend({

		/**
		 * Constructor
		 *
		 * @param  object  The parent FlipClock.Factory object
		 * @param  object  An object of properties to override the default	
		 */
		 
		constructor: function(factory, options) {
			this.base(factory, options);
		},

		/**
		 * Build the clock face
		 *
		 * @param  object  Pass the time that should be used to display on the clock.	
		 */
		 
		build: function(time) {
			var t        = this;
			var children = this.factory.$el.find('ul');

			if(!this.factory.time.time) {
				this.factory.original = new Date();

				this.factory.time = new FlipClock.Time(this.factory, this.factory.original);
			}

			var time = time ? time : this.factory.time.getMilitaryTime(false, this.showSeconds);

			if(time.length > children.length) {
				$.each(time, function(i, digit) {
					t.createList(digit);
				});
			}
			
			this.createDivider();
			this.createDivider();

			$(this.dividers[0]).insertBefore(this.lists[this.lists.length - 2].$el);
			$(this.dividers[1]).insertBefore(this.lists[this.lists.length - 4].$el);
			
			this.base();
		},
		
		/**
		 * Flip the clock face
		 */
		 
		flip: function(time, doNotAddPlayClass) {
			this.autoIncrement();
			
			time = time ? time : this.factory.time.getMilitaryTime(false, this.showSeconds);
			
			this.base(time, doNotAddPlayClass);	
		}
				
	});
	
}(jQuery));
(function($) {
		
	/**
	 * Counter Clock Face
	 *
	 * This class will generate a generice flip counter. The timer has been
	 * disabled. clock.increment() and clock.decrement() have been added.
	 *
	 * @param  object  The parent FlipClock.Factory object
	 * @param  object  An object of properties to override the default	
	 */
	 
	FlipClock.CounterFace = FlipClock.Face.extend({
		
		/**
		 * Tells the counter clock face if it should auto-increment
		 */

		shouldAutoIncrement: false,

		/**
		 * Constructor
		 *
		 * @param  object  The parent FlipClock.Factory object
		 * @param  object  An object of properties to override the default	
		 */
		 
		constructor: function(factory, options) {

			if(typeof options != "object") {
				options = {};
			}

			factory.autoStart = options.autoStart ? true : false;

			if(options.autoStart) {
				this.shouldAutoIncrement = true;
			}

			factory.increment = function() {
				factory.countdown = false;
				factory.setTime(factory.getTime().getTimeSeconds() + 1);
			};

			factory.decrement = function() {
				factory.countdown = true;
				var time = factory.getTime().getTimeSeconds();
				if(time > 0) {
					factory.setTime(time - 1);
				}
			};

			factory.setValue = function(digits) {
				factory.setTime(digits);
			};

			factory.setCounter = function(digits) {
				factory.setTime(digits);
			};

			this.base(factory, options);
		},

		/**
		 * Build the clock face	
		 */
		 
		build: function() {
			var t        = this;
			var children = this.factory.$el.find('ul');
			var time 	 = this.factory.getTime().digitize([this.factory.getTime().time]);

			if(time.length > children.length) {
				$.each(time, function(i, digit) {
					var list = t.createList(digit);

					list.select(digit);
				});
			
			}

			$.each(this.lists, function(i, list) {
				list.play();
			});

			this.base();
		},
		
		/**
		 * Flip the clock face
		 */
		 
		flip: function(time, doNotAddPlayClass) {			
			if(this.shouldAutoIncrement) {
				this.autoIncrement();
			}

			if(!time) {		
				time = this.factory.getTime().digitize([this.factory.getTime().time]);
			}

			this.base(time, doNotAddPlayClass);
		},

		/**
		 * Reset the clock face
		 */

		reset: function() {
			this.factory.time = new FlipClock.Time(
				this.factory, 
				this.factory.original ? Math.round(this.factory.original) : 0
			);

			this.flip();
		}
	});
	
}(jQuery));
(function($) {

	/**
	 * Daily Counter Clock Face
	 *
	 * This class will generate a daily counter for FlipClock.js. A
	 * daily counter will track days, hours, minutes, and seconds. If
	 * the number of available digits is exceeded in the count, a new
	 * digit will be created.
	 *
	 * @param  object  The parent FlipClock.Factory object
	 * @param  object  An object of properties to override the default
	 */

	FlipClock.DailyCounterFace = FlipClock.Face.extend({

		showSeconds: true,

		/**
		 * Constructor
		 *
		 * @param  object  The parent FlipClock.Factory object
		 * @param  object  An object of properties to override the default
		 */

		constructor: function(factory, options) {
			this.base(factory, options);
		},

		/**
		 * Build the clock face
		 */

		build: function(time) {
			var t = this;
			var children = this.factory.$el.find('ul');
			var offset = 0;

			time = time ? time : this.factory.time.getDayCounter(this.showSeconds);

			if(time.length > children.length) {
				$.each(time, function(i, digit) {
					t.createList(digit);
				});
			}

			if(this.showSeconds) {
				$(this.createDivider('Seconds')).insertBefore(this.lists[this.lists.length - 2].$el);
			}
			else
			{
				offset = 2;
			}

			$(this.createDivider('Minutes')).insertBefore(this.lists[this.lists.length - 4 + offset].$el);
			$(this.createDivider('Hours')).insertBefore(this.lists[this.lists.length - 6 + offset].$el);
			$(this.createDivider('Days', true)).insertBefore(this.lists[0].$el);

			this.base();
		},

		/**
		 * Flip the clock face
		 */

		flip: function(time, doNotAddPlayClass) {
			if(!time) {
				time = this.factory.time.getDayCounter(this.showSeconds);
			}

			this.autoIncrement();

			this.base(time, doNotAddPlayClass);
		}

	});

}(jQuery));
(function($) {
			
	/**
	 * Hourly Counter Clock Face
	 *
	 * This class will generate an hourly counter for FlipClock.js. An
	 * hour counter will track hours, minutes, and seconds. If number of
	 * available digits is exceeded in the count, a new digit will be 
	 * created.
	 *
	 * @param  object  The parent FlipClock.Factory object
	 * @param  object  An object of properties to override the default	
	 */
	 
	FlipClock.HourlyCounterFace = FlipClock.Face.extend({
			
		// clearExcessDigits: true,

		/**
		 * Constructor
		 *
		 * @param  object  The parent FlipClock.Factory object
		 * @param  object  An object of properties to override the default	
		 */
		 
		constructor: function(factory, options) {
			this.base(factory, options);
		},
		
		/**
		 * Build the clock face
		 */
		
		build: function(excludeHours, time) {
			var t = this;
			var children = this.factory.$el.find('ul');
			
			time = time ? time : this.factory.time.getHourCounter();
			
			if(time.length > children.length) {
				$.each(time, function(i, digit) {
					t.createList(digit);
				});
			}
			
			$(this.createDivider('Seconds')).insertBefore(this.lists[this.lists.length - 2].$el);
			$(this.createDivider('Minutes')).insertBefore(this.lists[this.lists.length - 4].$el);
			
			if(!excludeHours) {
				$(this.createDivider('Hours', true)).insertBefore(this.lists[0].$el);
			}
			
			this.base();
		},
		
		/**
		 * Flip the clock face
		 */
		 
		flip: function(time, doNotAddPlayClass) {
			if(!time) {
				time = this.factory.time.getHourCounter();
			}	

			this.autoIncrement();
		
			this.base(time, doNotAddPlayClass);
		},

		/**
		 * Append a newly created list to the clock
		 */

		appendDigitToClock: function(obj) {
			this.base(obj);

			this.dividers[0].insertAfter(this.dividers[0].next());
		}
		
	});
	
}(jQuery));
(function($) {
		
	/**
	 * Minute Counter Clock Face
	 *
	 * This class will generate a minute counter for FlipClock.js. A
	 * minute counter will track minutes and seconds. If an hour is 
	 * reached, the counter will reset back to 0. (4 digits max)
	 *
	 * @param  object  The parent FlipClock.Factory object
	 * @param  object  An object of properties to override the default	
	 */
	 
	FlipClock.MinuteCounterFace = FlipClock.HourlyCounterFace.extend({

		clearExcessDigits: false,

		/**
		 * Constructor
		 *
		 * @param  object  The parent FlipClock.Factory object
		 * @param  object  An object of properties to override the default	
		 */
		 
		constructor: function(factory, options) {
			this.base(factory, options);
		},
		
		/**
		 * Build the clock face	
		 */
		 
		build: function() {
			this.base(true, this.factory.time.getMinuteCounter());
		},
		
		/**
		 * Flip the clock face
		 */
		 
		flip: function(time, doNotAddPlayClass) {
			if(!time) {
				time = this.factory.time.getMinuteCounter();
			}

			this.base(time, doNotAddPlayClass);
		}

	});
	
}(jQuery));
(function($) {
		
	/**
	 * Twelve Hour Clock Face
	 *
	 * This class will generate a twelve hour clock for FlipClock.js
	 *
	 * @param  object  The parent FlipClock.Factory object
	 * @param  object  An object of properties to override the default	
	 */
	 
	FlipClock.TwelveHourClockFace = FlipClock.TwentyFourHourClockFace.extend({
		
		/**
		 * The meridium jQuery DOM object
		 */
		 
		meridium: false,
		
		/**
		 * The meridium text as string for easy access
		 */
		 
		meridiumText: 'AM',
					
		/**
		 * Build the clock face
		 *
		 * @param  object  Pass the time that should be used to display on the clock.	
		 */
		 
		build: function() {
			var t = this;

			var time = this.factory.time.getTime(false, this.showSeconds);

			this.base(time);			
			this.meridiumText = this.getMeridium();			
			this.meridium = $([
				'<ul class="flip-clock-meridium">',
					'<li>',
						'<a href="#">'+this.meridiumText+'</a>',
					'</li>',
				'</ul>'
			].join(''));
						
			this.meridium.insertAfter(this.lists[this.lists.length-1].$el);
		},
		
		/**
		 * Flip the clock face
		 */
		 
		flip: function(time, doNotAddPlayClass) {			
			if(this.meridiumText != this.getMeridium()) {
				this.meridiumText = this.getMeridium();
				this.meridium.find('a').html(this.meridiumText);	
			}
			this.base(this.factory.time.getTime(false, this.showSeconds), doNotAddPlayClass);	
		},
		
		/**
		 * Get the current meridium
		 *
		 * @return  string  Returns the meridium (AM|PM)
		 */
		 
		getMeridium: function() {
			return new Date().getHours() >= 12 ? 'PM' : 'AM';
		},
		
		/**
		 * Is it currently in the post-medirium?
		 *
		 * @return  bool  Returns true or false
		 */
		 
		isPM: function() {
			return this.getMeridium() == 'PM' ? true : false;
		},

		/**
		 * Is it currently before the post-medirium?
		 *
		 * @return  bool  Returns true or false
		 */
		 
		isAM: function() {
			return this.getMeridium() == 'AM' ? true : false;
		}
				
	});
	
}(jQuery));
(function($) {

    /**
     * FlipClock Arabic Language Pack
     *
     * This class will be used to translate tokens into the Arabic language.
     *
     */

    FlipClock.Lang.Arabic = {

      'years'   : 'سنوات',
      'months'  : 'شهور',
      'days'    : 'أيام',
      'hours'   : 'ساعات',
      'minutes' : 'دقائق',
      'seconds' : 'ثواني'

    };

    /* Create various aliases for convenience */

    FlipClock.Lang['ar']      = FlipClock.Lang.Arabic;
    FlipClock.Lang['ar-ar']   = FlipClock.Lang.Arabic;
    FlipClock.Lang['arabic']  = FlipClock.Lang.Arabic;

}(jQuery));
(function($) {
		
	/**
	 * FlipClock Danish Language Pack
	 *
	 * This class will used to translate tokens into the Danish language.
	 *	
	 */
	 
	FlipClock.Lang.Danish = {
		
		'years'   : 'År',
		'months'  : 'Måneder',
		'days'    : 'Dage',
		'hours'   : 'Timer',
		'minutes' : 'Minutter',
		'seconds' : 'Sekunder'	

	};
	
	/* Create various aliases for convenience */

	FlipClock.Lang['da']     = FlipClock.Lang.Danish;
	FlipClock.Lang['da-dk']  = FlipClock.Lang.Danish;
	FlipClock.Lang['danish'] = FlipClock.Lang.Danish;

}(jQuery));
(function($) {
		
	/**
	 * FlipClock German Language Pack
	 *
	 * This class will used to translate tokens into the German language.
	 *	
	 */
	 
	FlipClock.Lang.German = {
		
		'years'   : 'Jahre',
		'months'  : 'Monate',
		'days'    : 'Tage',
		'hours'   : 'Stunden',
		'minutes' : 'Minuten',
		'seconds' : 'Sekunden'	
 
	};
	
	/* Create various aliases for convenience */
 
	FlipClock.Lang['de']     = FlipClock.Lang.German;
	FlipClock.Lang['de-de']  = FlipClock.Lang.German;
	FlipClock.Lang['german'] = FlipClock.Lang.German;
 
}(jQuery));
(function($) {
		
	/**
	 * FlipClock English Language Pack
	 *
	 * This class will used to translate tokens into the English language.
	 *	
	 */
	 
	FlipClock.Lang.English = {
		
		'years'   : 'Years',
		'months'  : 'Months',
		'days'    : 'Days',
		'hours'   : 'Hours',
		'minutes' : 'Minutes',
		'seconds' : 'Seconds'	

	};
	
	/* Create various aliases for convenience */

	FlipClock.Lang['en']      = FlipClock.Lang.English;
	FlipClock.Lang['en-us']   = FlipClock.Lang.English;
	FlipClock.Lang['english'] = FlipClock.Lang.English;

}(jQuery));
(function($) {

	/**
	 * FlipClock Spanish Language Pack
	 *
	 * This class will used to translate tokens into the Spanish language.
	 *
	 */

	FlipClock.Lang.Spanish = {

		'years'   : 'Años',
		'months'  : 'Meses',
		'days'    : 'Días',
		'hours'   : 'Horas',
		'minutes' : 'Minutos',
		'seconds' : 'Segundos'

	};

	/* Create various aliases for convenience */

	FlipClock.Lang['es']      = FlipClock.Lang.Spanish;
	FlipClock.Lang['es-es']   = FlipClock.Lang.Spanish;
	FlipClock.Lang['spanish'] = FlipClock.Lang.Spanish;

}(jQuery));
(function($) {
		
	/**
	 * FlipClock Finnish Language Pack
	 *
	 * This class will used to translate tokens into the Finnish language.
	 *	
	 */
	 
	FlipClock.Lang.Finnish = {
		
		'years'   : 'Vuotta',
		'months'  : 'Kuukautta',
		'days'    : 'Päivää',
		'hours'   : 'Tuntia',
		'minutes' : 'Minuuttia',
		'seconds' : 'Sekuntia'	

	};
	
	/* Create various aliases for convenience */

	FlipClock.Lang['fi']      = FlipClock.Lang.Finnish;
	FlipClock.Lang['fi-fi']   = FlipClock.Lang.Finnish;
	FlipClock.Lang['finnish'] = FlipClock.Lang.Finnish;

}(jQuery));

(function($) {

  /**
   * FlipClock Canadian French Language Pack
   *
   * This class will used to translate tokens into the Canadian French language.
   *
   */

  FlipClock.Lang.French = {

    'years'   : 'Ans',
    'months'  : 'Mois',
    'days'    : 'Jours',
    'hours'   : 'Heures',
    'minutes' : 'Minutes',
    'seconds' : 'Secondes'

  };

  /* Create various aliases for convenience */

  FlipClock.Lang['fr']      = FlipClock.Lang.French;
  FlipClock.Lang['fr-ca']   = FlipClock.Lang.French;
  FlipClock.Lang['french']  = FlipClock.Lang.French;

}(jQuery));

(function($) {
		
	/**
	 * FlipClock Italian Language Pack
	 *
	 * This class will used to translate tokens into the Italian language.
	 *	
	 */
	 
	FlipClock.Lang.Italian = {
		
		'years'   : 'Anni',
		'months'  : 'Mesi',
		'days'    : 'Giorni',
		'hours'   : 'Ore',
		'minutes' : 'Minuti',
		'seconds' : 'Secondi'	

	};
	
	/* Create various aliases for convenience */

	FlipClock.Lang['it']      = FlipClock.Lang.Italian;
	FlipClock.Lang['it-it']   = FlipClock.Lang.Italian;
	FlipClock.Lang['italian'] = FlipClock.Lang.Italian;
	
}(jQuery));

(function($) {

  /**
   * FlipClock Latvian Language Pack
   *
   * This class will used to translate tokens into the Latvian language.
   *
   */

  FlipClock.Lang.Latvian = {

    'years'   : 'Gadi',
    'months'  : 'Mēneši',
    'days'    : 'Dienas',
    'hours'   : 'Stundas',
    'minutes' : 'Minūtes',
    'seconds' : 'Sekundes'

  };

  /* Create various aliases for convenience */

  FlipClock.Lang['lv']      = FlipClock.Lang.Latvian;
  FlipClock.Lang['lv-lv']   = FlipClock.Lang.Latvian;
  FlipClock.Lang['latvian'] = FlipClock.Lang.Latvian;

}(jQuery));
(function($) {

    /**
     * FlipClock Dutch Language Pack
     *
     * This class will used to translate tokens into the Dutch language.
     */

    FlipClock.Lang.Dutch = {

        'years'   : 'Jaren',
        'months'  : 'Maanden',
        'days'    : 'Dagen',
        'hours'   : 'Uren',
        'minutes' : 'Minuten',
        'seconds' : 'Seconden'

    };

    /* Create various aliases for convenience */

    FlipClock.Lang['nl']      = FlipClock.Lang.Dutch;
    FlipClock.Lang['nl-be']   = FlipClock.Lang.Dutch;
    FlipClock.Lang['dutch']   = FlipClock.Lang.Dutch;

}(jQuery));

(function($) {

	/**
	 * FlipClock Norwegian-Bokmål Language Pack
	 *
	 * This class will used to translate tokens into the Norwegian language.
	 *	
	 */

	FlipClock.Lang.Norwegian = {

		'years'   : 'År',
		'months'  : 'Måneder',
		'days'    : 'Dager',
		'hours'   : 'Timer',
		'minutes' : 'Minutter',
		'seconds' : 'Sekunder'	

	};

	/* Create various aliases for convenience */

	FlipClock.Lang['no']      = FlipClock.Lang.Norwegian;
	FlipClock.Lang['nb']      = FlipClock.Lang.Norwegian;
	FlipClock.Lang['no-nb']   = FlipClock.Lang.Norwegian;
	FlipClock.Lang['norwegian'] = FlipClock.Lang.Norwegian;

}(jQuery));

(function($) {

	/**
	 * FlipClock Portuguese Language Pack
	 *
	 * This class will used to translate tokens into the Portuguese language.
	 *
	 */

	FlipClock.Lang.Portuguese = {

		'years'   : 'Anos',
		'months'  : 'Meses',
		'days'    : 'Dias',
		'hours'   : 'Horas',
		'minutes' : 'Minutos',
		'seconds' : 'Segundos'

	};

	/* Create various aliases for convenience */

	FlipClock.Lang['pt']         = FlipClock.Lang.Portuguese;
	FlipClock.Lang['pt-br']      = FlipClock.Lang.Portuguese;
	FlipClock.Lang['portuguese'] = FlipClock.Lang.Portuguese;

}(jQuery));
(function($) {

  /**
   * FlipClock Russian Language Pack
   *
   * This class will used to translate tokens into the Russian language.
   *
   */

  FlipClock.Lang.Russian = {

    'years'   : 'лет',
    'months'  : 'месяцев',
    'days'    : 'дней',
    'hours'   : 'часов',
    'minutes' : 'минут',
    'seconds' : 'секунд'

  };

  /* Create various aliases for convenience */

  FlipClock.Lang['ru']      = FlipClock.Lang.Russian;
  FlipClock.Lang['ru-ru']   = FlipClock.Lang.Russian;
  FlipClock.Lang['russian']  = FlipClock.Lang.Russian;

}(jQuery));
(function($) {
		
	/**
	 * FlipClock Swedish Language Pack
	 *
	 * This class will used to translate tokens into the Swedish language.
	 *	
	 */
	 
	FlipClock.Lang.Swedish = {
		
		'years'   : 'År',
		'months'  : 'Månader',
		'days'    : 'Dagar',
		'hours'   : 'Timmar',
		'minutes' : 'Minuter',
		'seconds' : 'Sekunder'	

	};
	
	/* Create various aliases for convenience */

	FlipClock.Lang['sv']      = FlipClock.Lang.Swedish;
	FlipClock.Lang['sv-se']   = FlipClock.Lang.Swedish;
	FlipClock.Lang['swedish'] = FlipClock.Lang.Swedish;

}(jQuery));

(function($) {
		
	/**
	 * FlipClock Chinese Language Pack
	 *
	 * This class will used to translate tokens into the Chinese language.
	 *	
	 */
	 
	FlipClock.Lang.Chinese = {
		
		'years'   : '年',
		'months'  : '月',
		'days'    : '日',
		'hours'   : '时',
		'minutes' : '分',
		'seconds' : '秒'

	};
	
	/* Create various aliases for convenience */

	FlipClock.Lang['zh']      = FlipClock.Lang.Chinese;
	FlipClock.Lang['zh-cn']   = FlipClock.Lang.Chinese;
	FlipClock.Lang['chinese'] = FlipClock.Lang.Chinese;

}(jQuery));
(function (){ var aa="\n//# sourceURL=",ba='<script type="text/javascript" src="',b="American Samoa",ca="Antigua and Barbuda",k="Bolivia",p="Bosna i Hercegovina",q="Botswana",da="British Virgin Islands",ea="Cayman Islands",fa="Christmas Island",ga="Falkland Islands",r="Ghana",ha="Guin\u00e9e \u00e9quatoriale",ia="Guyane fran\u00e7aise",t="Honduras",u="Indonesia",v="Itoophiyaa",ja="Kalaallit Nunaat",w="Kiribati",ka="LocaleNameConstants",la="Luxembourg",ma="Madagascar",na="Marshall Islands",x="Micronesia",oa="Moldova, Republica",
pa="Nederlandse Antillen",qa="New Zealand",y="Nigeria",ra="Norfolk Island",sa="Northern Mariana Islands",ta="Nouvelle-Cal\u00e9donie",z="Papua New Guinea",ua="Paraguay",va="Philippines",wa="Polyn\u00e9sie fran\u00e7aise",xa="Puerto Rico",za="Rep\u00fablica Dominicana",A="Rwanda",Aa="Rywvaneth Unys",Ba="R\u00e9publique centrafricaine",Ca="R\u00e9publique d\u00e9mocratique du Congo",Da="Saint Kitts and Nevis",Ea="Saint Vincent and the Grenadines",Fa="Saint-Pierre-et-Miquelon",Ga="Serbia and Montenegro",
Ha="Seychelles",Ia="Slovensk\u00e1 republika",Ja="Solomon Islands",B="South Africa",Ka="Svalbard og Jan Mayen",La="Swaziland",Ma="S\u00e3o Tom\u00e9 e Pr\u00edncipe",C="S\u00e9n\u00e9gal",Na="Tanzania",Oa="Timor Leste",E="Tokelau",Pa="Turks and Caicos Islands",F="Tuvalu",G="T\u00fcrkiye",Qa="U.S. Virgin Islands",Ra="United Kingdom",Sa="United States",Ta="United States Minor Outlying Islands",Ua="Unknown or Invalid Region",H="Vanuatu",Va="Wallis-et-Futuna",Wa="annotatedtimeline",I="array",J="browserchart",
K="complete",L="corechart",M="dygraph",N="function",Xa="google.charts.load",O="imagechart",P="object",Ya="text/javascript",Q="ui",R="ui_base",S="webfontloader",Za="{css_prefix}/{cssFile}",$a="{prefix}",ab="{prefix}/{version}/third_party/{package}",bb="{version}",cb="\u010cesk\u00e1 republika",db="\u0411\u0435\u043b\u0430\u0440\u0443\u0441\u044c",eb="\u041a\u044b\u0440\u0433\u044b\u0437\u0441\u0442\u0430\u043d",fb="\u043c\u043e\u043d\u0433\u043e\u043b\u044c\u0441\u043a\u0438\u0439",gb="\u0540\u0561\u0575\u0561\u057d\u057f\u0561\u0576\u056b \u0540\u0561\u0576\u0580\u0561\u057a\u0565\u057f\u0578\u0582\u0569\u056b\u0582\u0576",
U="\u0627\u0641\u063a\u0627\u0646\u0633\u062a\u0627\u0646",hb="\u0627\u0644\u0627\u0645\u0627\u0631\u0627\u062a \u0627\u0644\u0639\u0631\u0628\u064a\u0629 \u0627\u0644\u0645\u062a\u062d\u062f\u0629",ib="\u0627\u0644\u0635\u062d\u0631\u0627\u0621 \u0627\u0644\u063a\u0631\u0628\u064a\u0629",jb="\u0627\u0644\u0645\u0645\u0644\u0643\u0629 \u0627\u0644\u0639\u0631\u0628\u064a\u0629 \u0627\u0644\u0633\u0639\u0648\u062f\u064a\u0629",kb="\u0627\u0644\u0648\u0644\u0627\u064a\u0627\u062a \u0627\u0644\u0645\u062a\u062d\u062f\u0629 \u0627\u0644\u0623\u0645\u0631\u064a\u0643\u064a\u0629",
lb="\u062c\u0632\u0631 \u0627\u0644\u0642\u0645\u0631",V="\u067e\u0627\u06a9\u0633\u062a\u0627\u0646",W="\u092d\u093e\u0930\u0924",X="\u12a2\u1275\u12ee\u1335\u12eb",mb="\uc870\uc120 \ubbfc\uc8fc\uc8fc\uc758 \uc778\ubbfc \uacf5\ud654\uad6d";function Y(){return function(){}}var Z=Z||{};Z.global=this;Z.N=function(a){return void 0!==a};
Z.xa=function(a,c,d){a=a.split(".");d=d||Z.global;a[0]in d||!d.execScript||d.execScript("var "+a[0]);for(var e;a.length&&(e=a.shift());)!a.length&&Z.N(c)?d[e]=c:d=d[e]?d[e]:d[e]={}};Z.Ge=function(a,c){Z.xa(a,c)};Z.G=!0;Z.Md="en";Z.oa=!0;Z.gc=!1;Z.Qb=!Z.G;Z.Ya=!1;Z.Of=function(a){if(Z.Ka())throw Error("goog.provide can not be used within a goog.module.");Z.gb(a)};Z.gb=function(a,c){Z.xa(a,c)};Z.qc=/^[a-zA-Z_$][a-zA-Z0-9._$]*$/;
Z.Oa=function(a){if(!Z.h(a)||!a||-1==a.search(Z.qc))throw Error("Invalid module identifier");if(!Z.Ka())throw Error("Module "+a+" has been loaded incorrectly. Note, modules cannot be loaded as normal scripts. They require some kind of pre-processing step. You're likely trying to load a module via a script tag or as a part of a concatenated bundle without rewriting the module. For more info see: https://github.com/google/closure-library/wiki/goog.module:-an-ES6-module-like-alternative-to-goog.provide.");
if(Z.l.Pa)throw Error("goog.module may only be called once per module.");Z.l.Pa=a};Z.Oa.get=Y();Z.Oa.Qe=Y();Z.l=null;Z.Ka=function(){return null!=Z.l};Z.Oa.ua=function(){Z.l.ua=!0};Z.jg=function(a){if(Z.Qb)throw a=a||"",Error("Importing test-only code into non-debug environment"+(a?": "+a:"."));};Z.Me=Y();Z.Ea=function(a){a=a.split(".");for(var c=Z.global,d;d=a.shift();)if(Z.Pc(c[d]))c=c[d];else return null;return c};Z.bf=function(a,c){c=c||Z.global;for(var d in a)c[d]=a[d]};
Z.Sd=function(a,c,d,e){if(Z.Wa){var f;a=a.replace(/\\/g,"/");var g=Z.j;e&&"boolean"!==typeof e||(e=e?{module:"goog"}:{});for(var h=0;f=c[h];h++)g.S[f]=a,g.La[a]=e;for(e=0;c=d[e];e++)a in g.F||(g.F[a]={}),g.F[a][c]=!0}};Z.Ig=!1;Z.Jd=!0;Z.bd=function(a){Z.global.console&&Z.global.console.error(a)};Z.dg=Y();Z.B="";Z.Jf=Y();Z.Rd=function(){throw Error("unimplemented abstract method");};Z.Td=function(a){a.Pe=function(){if(a.ub)return a.ub;Z.G&&(Z.vb[Z.vb.length]=a);return a.ub=new a}};Z.vb=[];Z.Xb=!0;
Z.ec=Z.G;Z.ad={};Z.Wa=!1;Z.$a="detect";Z.mc="transpile.js";
Z.Wa&&(Z.j={La:{},S:{},F:{},Ib:{},Va:{},W:{}},Z.sb=function(){var a=Z.global.document;return null!=a&&"write"in a},Z.Gc=function(){if(Z.N(Z.global.Mb))Z.B=Z.global.Mb;else if(Z.sb())for(var a=Z.global.document.getElementsByTagName("SCRIPT"),c=a.length-1;0<=c;--c){var d=a[c].src,e=d.lastIndexOf("?"),e=-1==e?d.length:e;if("base.js"==d.substr(e-7,7)){Z.B=d.substr(0,e-7);break}}},Z.Ga=function(a,c){(Z.global.Dd||Z.Ad)(a,c)&&(Z.j.Va[a]=!0)},Z.Wb=!(Z.global.atob||!Z.global.document||!Z.global.document.all),
Z.Nc=function(a,c,d){Z.Ga("",'goog.retrieveAndExec_("'+a+'", '+c+", "+d+");")},Z.Ra=[],Z.Kg=function(a,c){return Z.Xb&&Z.N(Z.global.JSON)?"goog.loadModule("+Z.global.JSON.stringify(c+aa+a+"\n")+");":'goog.loadModule(function(exports) {"use strict";'+c+"\n;return exports});\n//# sourceURL="+a+"\n"},Z.$c=function(){var a=Z.Ra.length;if(0<a){var c=Z.Ra;Z.Ra=[];for(var d=0;d<a;d++)Z.Ab(c[d])}},Z.Df=function(a){Z.wb(a)&&Z.rc(a)&&Z.Ab(Z.B+Z.Fa(a))},Z.wb=function(a){var c=(a=Z.Fa(a))&&Z.j.La[a]||{},d=c.lang||
"es3";return a&&("goog"==c.module||Z.Bb(d))?Z.B+a in Z.j.W:!1},Z.rc=function(a){if((a=Z.Fa(a))&&a in Z.j.F)for(var c in Z.j.F[a])if(!Z.Tc(c)&&!Z.wb(c))return!1;return!0},Z.Ab=function(a){if(a in Z.j.W){var c=Z.j.W[a];delete Z.j.W[a];Z.Mc(c)}},Z.Cf=Y(),Z.zd=function(a){Z.global.document.write(ba+a+'">\x3c/script>')},Z.tc=function(a){var c=Z.global.document,d=c.createElement("script");d.type=Ya;d.src=a;d.defer=!1;d.async=!1;c.head.appendChild(d)},Z.Ad=function(a,c){if(Z.sb()){var d=Z.global.document;
if(!Z.Ya&&d.readyState==K){if(/\bdeps.js$/.test(a))return!1;throw Error('Cannot write "'+a+'" after document load');}void 0===c?Z.Wb?(c=" onreadystatechange='goog.onScriptLoad_(this, "+ ++Z.yb+")' ",d.write(ba+a+'"'+c+">\x3c/script>")):Z.Ya?Z.tc(a):Z.zd(a):d.write('<script type="text/javascript">'+Z.kd(c)+"\x3c/script>");return!0}return!1},Z.kd=function(a){return a.replace(/<\/(SCRIPT)>/ig,"\\x3c\\$1>")},Z.Bb=function(a){if("always"==Z.$a)return!0;if("never"==Z.$a)return!1;Z.ea||(Z.ea=Z.zc());if(a in
Z.ea)return Z.ea[a];throw Error("Unknown language mode: "+a);},Z.ea=null,Z.yb=0,Z.Lf=function(a,c){a.readyState==K&&Z.yb==c&&Z.$c();return!0},Z.Lg=function(a){function c(a){if(!(a in f.Va||a in f.Ib)){f.Ib[a]=!0;if(a in f.F)for(var g in f.F[a])if(!Z.Tc(g))if(g in f.S)c(f.S[g]);else throw Error("Undefined nameToPath for "+g);a in e||(e[a]=!0,d.push(a))}}var d=[],e={},f=Z.j;c(a);for(a=0;a<d.length;a++){var g=d[a];Z.j.Va[g]=!0}var h=Z.l;Z.l=null;for(a=0;a<d.length;a++)if(g=d[a]){var l=f.La[g]||{},m=
Z.Bb(l.lang||"es3");"goog"==l.module||m?Z.Nc(Z.B+g,"goog"==l.module,m):Z.Ga(Z.B+g)}else throw Z.l=h,Error("Undefined script input");Z.l=h},Z.Fa=function(a){return a in Z.j.S?Z.j.S[a]:null},Z.Gc(),Z.global.Ed||Z.Ga(Z.B+"deps.js"));
Z.Bf=function(a){var c=Z.l;try{Z.l={Pa:void 0,ua:!1};var d;if(Z.xb(a))d=a.call(void 0,{});else if(Z.h(a))d=Z.Yc.call(void 0,a);else throw Error("Invalid module definition");var e=Z.l.Pa;if(!Z.h(e)||!e)throw Error('Invalid module name "'+e+'"');Z.l.ua?Z.gb(e,d):Z.ec&&Object.seal&&Z.O(d)&&Object.seal(d);Z.ad[e]=d}finally{Z.l=c}};Z.Yc=function(a){eval(a);return{}};Z.Gf=function(a){a=a.split("/");for(var c=0;c<a.length;)"."==a[c]?a.splice(c,1):c&&".."==a[c]&&a[c-1]&&".."!=a[c-1]?a.splice(--c,2):c++;return a.join("/")};
Z.Xc=function(a){if(Z.global.Ob)return Z.global.Ob(a);try{var c=new Z.global.XMLHttpRequest;c.open("get",a,!1);c.send();return 0==c.status||200==c.status?c.responseText:null}catch(d){return null}};Z.eg=Y();
Z.Dg=function(a,c){var d=Z.global.$jscomp;d||(Z.global.$jscomp=d={});var e=d.Ua;if(!e){var f=Z.B+Z.mc,g=Z.Xc(f);if(g){eval(g+aa+f);if(Z.global.$gwtExport&&Z.global.$gwtExport.$jscomp&&!Z.global.$gwtExport.$jscomp.transpile)throw Error('The transpiler did not properly export the "transpile" method. $gwtExport: '+JSON.stringify(Z.global.$gwtExport));Z.global.$jscomp.Ua=Z.global.$gwtExport.$jscomp.transpile;d=Z.global.$jscomp;e=d.Ua}}if(!e)var h=" requires transpilation but no transpiler was found.",
h=h+' Please add "//javascript/closure:transpiler" as a data dependency to ensure it is included.',e=d.Ua=function(a,c){Z.bd(c+h);return a};return e(a,c)};
Z.u=function(a){var c=typeof a;if(c==P)if(a){if(a instanceof Array)return I;if(a instanceof Object)return c;var d=Object.prototype.toString.call(a);if("[object Window]"==d)return P;if("[object Array]"==d||"number"==typeof a.length&&"undefined"!=typeof a.splice&&"undefined"!=typeof a.propertyIsEnumerable&&!a.propertyIsEnumerable("splice"))return I;if("[object Function]"==d||"undefined"!=typeof a.call&&"undefined"!=typeof a.propertyIsEnumerable&&!a.propertyIsEnumerable("call"))return N}else return"null";
else if(c==N&&"undefined"==typeof a.call)return P;return c};Z.sf=function(a){return null===a};Z.Pc=function(a){return null!=a};Z.isArray=function(a){return Z.u(a)==I};Z.Ia=function(a){var c=Z.u(a);return c==I||c==P&&"number"==typeof a.length};Z.nf=function(a){return Z.O(a)&&typeof a.getFullYear==N};Z.h=function(a){return"string"==typeof a};Z.Oc=function(a){return"boolean"==typeof a};Z.Sc=function(a){return"number"==typeof a};Z.xb=function(a){return Z.u(a)==N};
Z.O=function(a){var c=typeof a;return c==P&&null!=a||c==N};Z.qb=function(a){return a[Z.D]||(a[Z.D]=++Z.vd)};Z.cf=function(a){return!!a[Z.D]};Z.ld=function(a){null!==a&&"removeAttribute"in a&&a.removeAttribute(Z.D);try{delete a[Z.D]}catch(c){}};Z.D="closure_uid_"+(1E9*Math.random()>>>0);Z.vd=0;Z.Oe=Z.qb;Z.Zf=Z.ld;Z.yc=function(a){var c=Z.u(a);if(c==P||c==I){if(a.clone)return a.clone();var c=c==I?[]:{},d;for(d in a)c[d]=Z.yc(a[d]);return c}return a};Z.wc=function(a,c,d){return a.call.apply(a.bind,arguments)};
Z.vc=function(a,c,d){if(!a)throw Error();if(2<arguments.length){var e=Array.prototype.slice.call(arguments,2);return function(){var d=Array.prototype.slice.call(arguments);Array.prototype.unshift.apply(d,e);return a.apply(c,d)}}return function(){return a.apply(c,arguments)}};Z.bind=function(a,c,d){Z.bind=Function.prototype.bind&&-1!=Function.prototype.bind.toString().indexOf("native code")?Z.wc:Z.vc;return Z.bind.apply(null,arguments)};
Z.fd=function(a,c){var d=Array.prototype.slice.call(arguments,1);return function(){var c=d.slice();c.push.apply(c,arguments);return a.apply(this,c)}};Z.Ef=function(a,c){for(var d in c)a[d]=c[d]};Z.now=Z.oa&&Date.now||function(){return+new Date};
Z.Mc=function(a){if(Z.global.execScript)Z.global.execScript(a,"JavaScript");else if(Z.global.eval){if(null==Z.X)if(Z.global.eval("var _evalTest_ = 1;"),"undefined"!=typeof Z.global._evalTest_){try{delete Z.global._evalTest_}catch(e){}Z.X=!0}else Z.X=!1;if(Z.X)Z.global.eval(a);else{var c=Z.global.document,d=c.createElement("SCRIPT");d.type=Ya;d.defer=!1;d.appendChild(c.createTextNode(a));c.body.appendChild(d);c.body.removeChild(d)}}else throw Error("goog.globalEval not available");};Z.X=null;
Z.Ne=function(a,c){function d(a){a=a.split("-");for(var c=[],d=0;d<a.length;d++)c.push(e(a[d]));return c.join("-")}function e(a){return Z.hb[a]||a}if("."==String(a).charAt(0))throw Error('className passed in goog.getCssName must not start with ".". You passed: '+a);var f;f=Z.hb?"BY_WHOLE"==Z.Cc?e:d:function(a){return a};a=c?a+"-"+f(c):f(a);return Z.global.Nb?Z.global.Nb(a):a};Z.fg=function(a,c){Z.hb=a;Z.Cc=c};
Z.Te=function(a,c){c&&(a=a.replace(/\{\$([^}]+)}/g,function(a,e){return null!=c&&e in c?c[e]:a}));return a};Z.Ue=function(a){return a};Z.ya=function(a,c){Z.xa(a,c,void 0)};Z.Ke=function(a,c,d){a[c]=d};Z.Ha=function(a,c){function d(){}d.prototype=c.prototype;a.ma=c.prototype;a.prototype=new d;a.prototype.constructor=a;a.uc=function(a,d,g){for(var e=Array(arguments.length-2),f=2;f<arguments.length;f++)e[f-2]=arguments[f];return c.prototype[d].apply(a,e)}};
Z.uc=function(a,c,d){var e=arguments.callee.caller;if(Z.gc||Z.G&&!e)throw Error("arguments.caller not defined.  goog.base() cannot be used with strict mode code. See http://www.ecma-international.org/ecma-262/5.1/#sec-C");if(e.ma){for(var f=Array(arguments.length-1),g=1;g<arguments.length;g++)f[g-1]=arguments[g];return e.ma.constructor.apply(a,f)}f=Array(arguments.length-2);for(g=2;g<arguments.length;g++)f[g-2]=arguments[g];for(var g=!1,h=a.constructor;h;h=h.ma&&h.ma.constructor)if(h.prototype[c]===
e)g=!0;else if(g)return h.prototype[c].apply(a,f);if(a[c]===e)return a.constructor.prototype[c].apply(a,f);throw Error("goog.base called from a method of one name to a method of a different name");};Z.scope=function(a){if(Z.Ka())throw Error("goog.scope is not supported within a goog.module.");a.call(Z.global)};
Z.o=function(a,c){var d=c.constructor,e=c.qd;d&&d!=Object.prototype.constructor||(d=function(){throw Error("cannot instantiate an interface (no constructor defined).");});d=Z.o.Ac(d,a);a&&Z.Ha(d,a);delete c.constructor;delete c.qd;Z.o.bb(d.prototype,c);null!=e&&(e instanceof Function?e(d):Z.o.bb(d,e));return d};Z.o.dc=Z.G;
Z.o.Ac=function(a,c){function d(){var c=a.apply(this,arguments)||this;c[Z.D]=c[Z.D];this.constructor===d&&e&&Object.seal instanceof Function&&Object.seal(c);return c}if(!Z.o.dc)return a;var e=!Z.o.Uc(c);return d};Z.o.Uc=function(a){return a&&a.prototype&&a.prototype[Z.nc]};Z.o.Za="constructor hasOwnProperty isPrototypeOf propertyIsEnumerable toLocaleString toString valueOf".split(" ");
Z.o.bb=function(a,c){for(var d in c)Object.prototype.hasOwnProperty.call(c,d)&&(a[d]=c[d]);for(var e=0;e<Z.o.Za.length;e++)d=Z.o.Za[e],Object.prototype.hasOwnProperty.call(c,d)&&(a[d]=c[d])};Z.xg=Y();Z.nc="goog_defineClass_legacy_unsealable";
Z.zc=function(){function a(a,c){e?d[a]=!0:c()?d[a]=!1:e=d[a]=!0}function c(a){try{return!!eval(a)}catch(g){return!1}}var d={es3:!1},e=!1;a("es5",function(){return c("[1,].length==1")});a("es6",function(){return c('(()=>{"use strict";class X{constructor(){if(new.target!=String)throw 1;this.x=42}}let q=Reflect.construct(X,[],String);if(q.x!=42||!(q instanceof String))throw 1;for(const a of[2,3]){if(a==2)continue;function f(z={a}){let a=0;return z.a}{function f(){return 0;}}return f()==3}})()')});a("es6-impl",
function(){return!0});a("es7",function(){return c("2 ** 2 == 4")});a("es8",function(){return c("async () => 1, true")});return d};Z.debug={};Z.debug.Error=function(a){if(Error.captureStackTrace)Error.captureStackTrace(this,Z.debug.Error);else{var c=Error().stack;c&&(this.stack=c)}a&&(this.message=String(a))};Z.Ha(Z.debug.Error,Error);Z.debug.Error.prototype.name="CustomError";Z.ib={};Z.ib.bc={Rb:1,Bd:2,Qd:3,Cd:4,Ld:5,Kd:6,Pd:7,Fd:8,DOCUMENT:9,Id:10,Hd:11,Nd:12};Z.b={};Z.b.Xa=!1;Z.b.Tb=!1;Z.b.pc={$b:"\u00a0"};Z.b.startsWith=function(a,c){return 0==a.lastIndexOf(c,0)};Z.b.endsWith=function(a,c){var d=a.length-c.length;return 0<=d&&a.indexOf(c,d)==d};Z.b.xe=function(a,c){return 0==Z.b.fb(c,a.substr(0,c.length))};Z.b.ue=function(a,c){return 0==Z.b.fb(c,a.substr(a.length-c.length,c.length))};Z.b.we=function(a,c){return a.toLowerCase()==c.toLowerCase()};
Z.b.rd=function(a,c){for(var d=a.split("%s"),e="",f=Array.prototype.slice.call(arguments,1);f.length&&1<d.length;)e+=d.shift()+f.shift();return e+d.join("%s")};Z.b.ze=function(a){return a.replace(/[\s\xa0]+/g," ").replace(/^\s+|\s+$/g,"")};Z.b.Ja=function(a){return/^[\s\xa0]*$/.test(a)};Z.b.qf=function(a){return 0==a.length};Z.b.Qc=Z.b.Ja;Z.b.Rc=function(a){return Z.b.Ja(Z.b.cd(a))};Z.b.pf=Z.b.Rc;Z.b.mf=function(a){return!/[^\t\n\r ]/.test(a)};Z.b.kf=function(a){return!/[^a-zA-Z]/.test(a)};
Z.b.tf=function(a){return!/[^0-9]/.test(a)};Z.b.lf=function(a){return!/[^a-zA-Z0-9]/.test(a)};Z.b.wf=function(a){return" "==a};Z.b.xf=function(a){return 1==a.length&&" "<=a&&"~">=a||"\u0080"<=a&&"\ufffd">=a};Z.b.vg=function(a){return a.replace(/(\r\n|\r|\n)+/g," ")};Z.b.pe=function(a){return a.replace(/(\r\n|\r|\n)/g,"\n")};Z.b.If=function(a){return a.replace(/\xa0|\s/g," ")};Z.b.Hf=function(a){return a.replace(/\xa0|[ \t]+/g," ")};
Z.b.ye=function(a){return a.replace(/[\t\r\n ]+/g," ").replace(/^[\t\r\n ]+|[\t\r\n ]+$/g,"")};Z.b.trim=Z.oa&&String.prototype.trim?function(a){return a.trim()}:function(a){return a.replace(/^[\s\xa0]+|[\s\xa0]+$/g,"")};Z.b.trimLeft=function(a){return a.replace(/^[\s\xa0]+/,"")};Z.b.trimRight=function(a){return a.replace(/[\s\xa0]+$/,"")};Z.b.fb=function(a,c){a=String(a).toLowerCase();c=String(c).toLowerCase();return a<c?-1:a==c?0:1};
Z.b.Db=function(a,c,d){if(a==c)return 0;if(!a)return-1;if(!c)return 1;for(var e=a.toLowerCase().match(d),f=c.toLowerCase().match(d),g=Math.min(e.length,f.length),h=0;h<g;h++){d=e[h];var l=f[h];if(d!=l)return a=parseInt(d,10),!isNaN(a)&&(c=parseInt(l,10),!isNaN(c)&&a-c)?a-c:d<l?-1:1}return e.length!=f.length?e.length-f.length:a<c?-1:1};Z.b.hf=function(a,c){return Z.b.Db(a,c,/\d+|\D+/g)};Z.b.Jc=function(a,c){return Z.b.Db(a,c,/\d+|\.\d+|\D+/g)};Z.b.Kf=Z.b.Jc;Z.b.Hg=function(a){return encodeURIComponent(String(a))};
Z.b.Gg=function(a){return decodeURIComponent(a.replace(/\+/g," "))};Z.b.dd=function(a,c){return a.replace(/(\r\n|\r|\n)/g,c?"<br />":"<br>")};
Z.b.rb=function(a){if(!Z.b.Kb.test(a))return a;-1!=a.indexOf("&")&&(a=a.replace(Z.b.Lb,"&amp;"));-1!=a.indexOf("<")&&(a=a.replace(Z.b.Zb,"&lt;"));-1!=a.indexOf(">")&&(a=a.replace(Z.b.Ub,"&gt;"));-1!=a.indexOf('"')&&(a=a.replace(Z.b.cc,"&quot;"));-1!=a.indexOf("'")&&(a=a.replace(Z.b.fc,"&#39;"));-1!=a.indexOf("\x00")&&(a=a.replace(Z.b.ac,"&#0;"));Z.b.Xa&&-1!=a.indexOf("e")&&(a=a.replace(Z.b.Sb,"&#101;"));return a};Z.b.Lb=/&/g;Z.b.Zb=/</g;Z.b.Ub=/>/g;Z.b.cc=/"/g;Z.b.fc=/'/g;Z.b.ac=/\x00/g;Z.b.Sb=/e/g;
Z.b.Kb=Z.b.Xa?/[\x00&<>"'e]/:/[\x00&<>"']/;Z.b.Gb=function(a){return Z.b.contains(a,"&")?!Z.b.Tb&&"document"in Z.global?Z.b.Hb(a):Z.b.wd(a):a};Z.b.Fg=function(a,c){return Z.b.contains(a,"&")?Z.b.Hb(a,c):a};
Z.b.Hb=function(a,c){var d={"&amp;":"&","&lt;":"<","&gt;":">","&quot;":'"'},e;e=c?c.createElement("div"):Z.global.document.createElement("div");return a.replace(Z.b.Vb,function(a,c){var f=d[a];if(f)return f;"#"==c.charAt(0)&&(c=Number("0"+c.substr(1)),isNaN(c)||(f=String.fromCharCode(c)));f||(e.innerHTML=a+" ",f=e.firstChild.nodeValue.slice(0,-1));return d[a]=f})};
Z.b.wd=function(a){return a.replace(/&([^;]+);/g,function(a,d){switch(d){case "amp":return"&";case "lt":return"<";case "gt":return">";case "quot":return'"';default:return"#"!=d.charAt(0)||(d=Number("0"+d.substr(1)),isNaN(d))?a:String.fromCharCode(d)}})};Z.b.Vb=/&([^;\s<&]+);?/g;Z.b.Jg=function(a,c){return Z.b.dd(a.replace(/  /g," &#160;"),c)};Z.b.Nf=function(a){return a.replace(/(^|[\n ]) /g,"$1"+Z.b.pc.$b)};
Z.b.wg=function(a,c){for(var d=c.length,e=0;e<d;e++){var f=1==d?c:c.charAt(e);if(a.charAt(0)==f&&a.charAt(a.length-1)==f)return a.substring(1,a.length-1)}return a};Z.b.truncate=function(a,c,d){d&&(a=Z.b.Gb(a));a.length>c&&(a=a.substring(0,c-3)+"...");d&&(a=Z.b.rb(a));return a};Z.b.Eg=function(a,c,d,e){d&&(a=Z.b.Gb(a));e&&a.length>c?(e>c&&(e=c),a=a.substring(0,c-e)+"..."+a.substring(a.length-e)):a.length>c&&(e=Math.floor(c/2),a=a.substring(0,e+c%2)+"..."+a.substring(a.length-e));d&&(a=Z.b.rb(a));return a};
Z.b.Ta={"\x00":"\\0","\b":"\\b","\f":"\\f","\n":"\\n","\r":"\\r","\t":"\\t","\x0B":"\\x0B",'"':'\\"',"\\":"\\\\","<":"<"};Z.b.Z={"'":"\\'"};Z.b.quote=function(a){a=String(a);for(var c=['"'],d=0;d<a.length;d++){var e=a.charAt(d),f=e.charCodeAt(0);c[d+1]=Z.b.Ta[e]||(31<f&&127>f?e:Z.b.jb(e))}c.push('"');return c.join("")};Z.b.Je=function(a){for(var c=[],d=0;d<a.length;d++)c[d]=Z.b.jb(a.charAt(d));return c.join("")};
Z.b.jb=function(a){if(a in Z.b.Z)return Z.b.Z[a];if(a in Z.b.Ta)return Z.b.Z[a]=Z.b.Ta[a];var c,d=a.charCodeAt(0);if(31<d&&127>d)c=a;else{if(256>d){if(c="\\x",16>d||256<d)c+="0"}else c="\\u",4096>d&&(c+="0");c+=d.toString(16).toUpperCase()}return Z.b.Z[a]=c};Z.b.contains=function(a,c){return-1!=a.indexOf(c)};Z.b.re=function(a,c){return Z.b.contains(a.toLowerCase(),c.toLowerCase())};Z.b.Ee=function(a,c){return a&&c?a.split(c).length-1:0};
Z.b.L=function(a,c,d){var e=a;0<=c&&c<a.length&&0<d&&(e=a.substr(0,c)+a.substr(c+d,a.length-c-d));return e};Z.b.remove=function(a,c){return a.replace(c,"")};Z.b.Wf=function(a,c){c=new RegExp(Z.b.Sa(c),"g");return a.replace(c,"")};Z.b.cg=function(a,c,d){c=new RegExp(Z.b.Sa(c),"g");return a.replace(c,d.replace(/\$/g,"$$$$"))};Z.b.Sa=function(a){return String(a).replace(/([-()\[\]{}+?*.$\^|,:#<!\\])/g,"\\$1").replace(/\x08/g,"\\x08")};
Z.b.repeat=String.prototype.repeat?function(a,c){return a.repeat(c)}:function(a,c){return Array(c+1).join(a)};Z.b.Mf=function(a,c,d){a=Z.N(d)?a.toFixed(d):String(a);d=a.indexOf(".");-1==d&&(d=a.length);return Z.b.repeat("0",Math.max(0,c-d))+a};Z.b.cd=function(a){return null==a?"":String(a)};Z.b.oe=function(a){return Array.prototype.join.call(arguments,"")};Z.b.Xe=function(){return Math.floor(2147483648*Math.random()).toString(36)+Math.abs(Math.floor(2147483648*Math.random())^Z.now()).toString(36)};
Z.b.Be=function(a,c){var d=0;a=Z.b.trim(String(a)).split(".");c=Z.b.trim(String(c)).split(".");for(var e=Math.max(a.length,c.length),f=0;0==d&&f<e;f++){var g=a[f]||"",h=c[f]||"";do{g=/(\d*)(\D*)(.*)/.exec(g)||["","","",""];h=/(\d*)(\D*)(.*)/.exec(h)||["","","",""];if(0==g[0].length&&0==h[0].length)break;d=Z.b.qa(0==g[1].length?0:parseInt(g[1],10),0==h[1].length?0:parseInt(h[1],10))||Z.b.qa(0==g[2].length,0==h[2].length)||Z.b.qa(g[2],h[2]);g=g[3];h=h[3]}while(0==d)}return d};
Z.b.qa=function(a,c){return a<c?-1:a>c?1:0};Z.b.df=function(a){for(var c=0,d=0;d<a.length;++d)c=31*c+a.charCodeAt(d)>>>0;return c};Z.b.xd=2147483648*Math.random()|0;Z.b.Fe=function(){return"goog_"+Z.b.xd++};Z.b.zg=function(a){var c=Number(a);return 0==c&&Z.b.Ja(a)?NaN:c};Z.b.rf=function(a){return/^[a-z]+([A-Z][a-z]*)*$/.test(a)};Z.b.yf=function(a){return/^([A-Z][a-z]*)+$/.test(a)};Z.b.yg=function(a){return String(a).replace(/\-([a-z])/g,function(a,d){return d.toUpperCase()})};
Z.b.Bg=function(a){return String(a).replace(/([A-Z])/g,"-$1").toLowerCase()};Z.b.Cg=function(a,c){c=Z.h(c)?Z.b.Sa(c):"\\s";return a.replace(new RegExp("(^"+(c?"|["+c+"]+":"")+")([a-z])","g"),function(a,c,f){return c+f.toUpperCase()})};Z.b.qe=function(a){return String(a.charAt(0)).toUpperCase()+String(a.substr(1)).toLowerCase()};Z.b.parseInt=function(a){isFinite(a)&&(a=String(a));return Z.h(a)?/^\s*-?0x/i.test(a)?parseInt(a,16):parseInt(a,10):NaN};
Z.b.qg=function(a,c,d){a=a.split(c);for(var e=[];0<d&&a.length;)e.push(a.shift()),d--;a.length&&e.push(a.join(c));return e};Z.b.Af=function(a,c){if(c)"string"==typeof c&&(c=[c]);else return a;for(var d=-1,e=0;e<c.length;e++)if(""!=c[e]){var f=a.lastIndexOf(c[e]);f>d&&(d=f)}return-1==d?a:a.slice(d+1)};
Z.b.He=function(a,c){var d=[],e=[];if(a==c)return 0;if(!a.length||!c.length)return Math.max(a.length,c.length);for(var f=0;f<c.length+1;f++)d[f]=f;for(f=0;f<a.length;f++){e[0]=f+1;for(var g=0;g<c.length;g++)e[g+1]=Math.min(e[g]+1,d[g+1]+1,d[g]+Number(a[f]!=c[g]));for(g=0;g<d.length;g++)d[g]=e[g]}return e[c.length]};Z.g={};Z.g.s=Z.G;Z.g.U=function(a,c){c.unshift(a);Z.debug.Error.call(this,Z.b.rd.apply(null,c));c.shift()};Z.Ha(Z.g.U,Z.debug.Error);Z.g.U.prototype.name="AssertionError";Z.g.Pb=function(a){throw a;};Z.g.va=Z.g.Pb;Z.g.A=function(a,c,d,e){var f="Assertion failed";if(d)var f=f+(": "+d),g=e;else a&&(f+=": "+a,g=c);a=new Z.g.U(""+f,g||[]);Z.g.va(a)};Z.g.gg=function(a){Z.g.s&&(Z.g.va=a)};Z.g.assert=function(a,c,d){Z.g.s&&!a&&Z.g.A("",null,c,Array.prototype.slice.call(arguments,2));return a};
Z.g.Aa=function(a,c){Z.g.s&&Z.g.va(new Z.g.U("Failure"+(a?": "+a:""),Array.prototype.slice.call(arguments,1)))};Z.g.Zd=function(a,c,d){Z.g.s&&!Z.Sc(a)&&Z.g.A("Expected number but got %s: %s.",[Z.u(a),a],c,Array.prototype.slice.call(arguments,2));return a};Z.g.ge=function(a,c,d){Z.g.s&&!Z.h(a)&&Z.g.A("Expected string but got %s: %s.",[Z.u(a),a],c,Array.prototype.slice.call(arguments,2));return a};
Z.g.Xd=function(a,c,d){Z.g.s&&!Z.xb(a)&&Z.g.A("Expected function but got %s: %s.",[Z.u(a),a],c,Array.prototype.slice.call(arguments,2));return a};Z.g.$d=function(a,c,d){Z.g.s&&!Z.O(a)&&Z.g.A("Expected object but got %s: %s.",[Z.u(a),a],c,Array.prototype.slice.call(arguments,2));return a};Z.g.Ud=function(a,c,d){Z.g.s&&!Z.isArray(a)&&Z.g.A("Expected array but got %s: %s.",[Z.u(a),a],c,Array.prototype.slice.call(arguments,2));return a};
Z.g.Vd=function(a,c,d){Z.g.s&&!Z.Oc(a)&&Z.g.A("Expected boolean but got %s: %s.",[Z.u(a),a],c,Array.prototype.slice.call(arguments,2));return a};Z.g.Wd=function(a,c,d){!Z.g.s||Z.O(a)&&a.nodeType==Z.ib.bc.Rb||Z.g.A("Expected Element but got %s: %s.",[Z.u(a),a],c,Array.prototype.slice.call(arguments,2));return a};Z.g.Yd=function(a,c,d,e){!Z.g.s||a instanceof c||Z.g.A("Expected instanceof %s but got %s.",[Z.g.pb(c),Z.g.pb(a)],d,Array.prototype.slice.call(arguments,3));return a};
Z.g.fe=function(){for(var a in Object.prototype)Z.g.Aa(a+" should not be enumerable in Object.prototype.")};Z.g.pb=function(a){return a instanceof Function?a.displayName||a.name||"unknown type name":a instanceof Object?a.constructor.displayName||a.constructor.name||Object.prototype.toString.call(a):null===a?"null":typeof a};Z.f={};Z.w=Z.oa;Z.f.v=!1;Z.f.hd=function(a){return a[a.length-1]};Z.f.zf=Z.f.hd;Z.f.indexOf=Z.w&&(Z.f.v||Array.prototype.indexOf)?function(a,c,d){return Array.prototype.indexOf.call(a,c,d)}:function(a,c,d){d=null==d?0:0>d?Math.max(0,a.length+d):d;if(Z.h(a))return Z.h(c)&&1==c.length?a.indexOf(c,d):-1;for(;d<a.length;d++)if(d in a&&a[d]===c)return d;return-1};
Z.f.lastIndexOf=Z.w&&(Z.f.v||Array.prototype.lastIndexOf)?function(a,c,d){return Array.prototype.lastIndexOf.call(a,c,null==d?a.length-1:d)}:function(a,c,d){d=null==d?a.length-1:d;0>d&&(d=Math.max(0,a.length+d));if(Z.h(a))return Z.h(c)&&1==c.length?a.lastIndexOf(c,d):-1;for(;0<=d;d--)if(d in a&&a[d]===c)return d;return-1};
Z.f.forEach=Z.w&&(Z.f.v||Array.prototype.forEach)?function(a,c,d){Array.prototype.forEach.call(a,c,d)}:function(a,c,d){for(var e=a.length,f=Z.h(a)?a.split(""):a,g=0;g<e;g++)g in f&&c.call(d,f[g],g,a)};Z.f.kb=function(a,c){for(var d=Z.h(a)?a.split(""):a,e=a.length-1;0<=e;--e)e in d&&c.call(void 0,d[e],e,a)};
Z.f.filter=Z.w&&(Z.f.v||Array.prototype.filter)?function(a,c,d){return Array.prototype.filter.call(a,c,d)}:function(a,c,d){for(var e=a.length,f=[],g=0,h=Z.h(a)?a.split(""):a,l=0;l<e;l++)if(l in h){var m=h[l];c.call(d,m,l,a)&&(f[g++]=m)}return f};Z.f.map=Z.w&&(Z.f.v||Array.prototype.map)?function(a,c,d){return Array.prototype.map.call(a,c,d)}:function(a,c,d){for(var e=a.length,f=Array(e),g=Z.h(a)?a.split(""):a,h=0;h<e;h++)h in g&&(f[h]=c.call(d,g[h],h,a));return f};
Z.f.reduce=Z.w&&(Z.f.v||Array.prototype.reduce)?function(a,c,d,e){e&&(c=Z.bind(c,e));return Array.prototype.reduce.call(a,c,d)}:function(a,c,d,e){var f=d;Z.f.forEach(a,function(d,h){f=c.call(e,f,d,h,a)});return f};Z.f.reduceRight=Z.w&&(Z.f.v||Array.prototype.reduceRight)?function(a,c,d,e){e&&(c=Z.bind(c,e));return Array.prototype.reduceRight.call(a,c,d)}:function(a,c,d,e){var f=d;Z.f.kb(a,function(d,h){f=c.call(e,f,d,h,a)});return f};
Z.f.some=Z.w&&(Z.f.v||Array.prototype.some)?function(a,c,d){return Array.prototype.some.call(a,c,d)}:function(a,c,d){for(var e=a.length,f=Z.h(a)?a.split(""):a,g=0;g<e;g++)if(g in f&&c.call(d,f[g],g,a))return!0;return!1};Z.f.every=Z.w&&(Z.f.v||Array.prototype.every)?function(a,c,d){return Array.prototype.every.call(a,c,d)}:function(a,c,d){for(var e=a.length,f=Z.h(a)?a.split(""):a,g=0;g<e;g++)if(g in f&&!c.call(d,f[g],g,a))return!1;return!0};
Z.f.count=function(a,c,d){var e=0;Z.f.forEach(a,function(a,g,h){c.call(d,a,g,h)&&++e},d);return e};Z.f.find=function(a,c,d){c=Z.f.findIndex(a,c,d);return 0>c?null:Z.h(a)?a.charAt(c):a[c]};Z.f.findIndex=function(a,c,d){for(var e=a.length,f=Z.h(a)?a.split(""):a,g=0;g<e;g++)if(g in f&&c.call(d,f[g],g,a))return g;return-1};Z.f.Le=function(a,c,d){c=Z.f.Hc(a,c,d);return 0>c?null:Z.h(a)?a.charAt(c):a[c]};
Z.f.Hc=function(a,c,d){for(var e=Z.h(a)?a.split(""):a,f=a.length-1;0<=f;f--)if(f in e&&c.call(d,e[f],f,a))return f;return-1};Z.f.contains=function(a,c){return 0<=Z.f.indexOf(a,c)};Z.f.Qc=function(a){return 0==a.length};Z.f.clear=function(a){if(!Z.isArray(a))for(var c=a.length-1;0<=c;c--)delete a[c];a.length=0};Z.f.ef=function(a,c){Z.f.contains(a,c)||a.push(c)};Z.f.tb=function(a,c,d){Z.f.splice(a,d,0,c)};Z.f.gf=function(a,c,d){Z.fd(Z.f.splice,a,d,0).apply(null,c)};
Z.f.insertBefore=function(a,c,d){var e;2==arguments.length||0>(e=Z.f.indexOf(a,d))?a.push(c):Z.f.tb(a,c,e)};Z.f.remove=function(a,c){c=Z.f.indexOf(a,c);var d;(d=0<=c)&&Z.f.L(a,c);return d};Z.f.ag=function(a,c){c=Z.f.lastIndexOf(a,c);return 0<=c?(Z.f.L(a,c),!0):!1};Z.f.L=function(a,c){return 1==Array.prototype.splice.call(a,c,1).length};Z.f.$f=function(a,c,d){c=Z.f.findIndex(a,c,d);return 0<=c?(Z.f.L(a,c),!0):!1};
Z.f.Xf=function(a,c,d){var e=0;Z.f.kb(a,function(f,g){c.call(d,f,g,a)&&Z.f.L(a,g)&&e++});return e};Z.f.concat=function(a){return Array.prototype.concat.apply(Array.prototype,arguments)};Z.f.join=function(a){return Array.prototype.concat.apply(Array.prototype,arguments)};Z.f.ud=function(a){var c=a.length;if(0<c){for(var d=Array(c),e=0;e<c;e++)d[e]=a[e];return d}return[]};Z.f.clone=Z.f.ud;
Z.f.extend=function(a,c){for(var d=1;d<arguments.length;d++){var e=arguments[d];if(Z.Ia(e)){var f=a.length||0,g=e.length||0;a.length=f+g;for(var h=0;h<g;h++)a[f+h]=e[h]}else a.push(e)}};Z.f.splice=function(a,c,d,e){return Array.prototype.splice.apply(a,Z.f.slice(arguments,1))};Z.f.slice=function(a,c,d){return 2>=arguments.length?Array.prototype.slice.call(a,c):Array.prototype.slice.call(a,c,d)};
Z.f.Yf=function(a,c,d){function e(a){return Z.O(a)?"o"+Z.qb(a):(typeof a).charAt(0)+a}c=c||a;d=d||e;for(var f={},g=0,h=0;h<a.length;){var l=a[h++],m=d(l);Object.prototype.hasOwnProperty.call(f,m)||(f[m]=!0,c[g++]=l)}c.length=g};Z.f.cb=function(a,c,d){return Z.f.eb(a,d||Z.f.C,!1,c)};Z.f.le=function(a,c,d){return Z.f.eb(a,c,!0,void 0,d)};Z.f.eb=function(a,c,d,e,f){for(var g=0,h=a.length,l;g<h;){var m=g+h>>1,n;n=d?c.call(f,a[m],m,a):c(e,a[m]);0<n?g=m+1:(h=m,l=!n)}return l?g:~g};
Z.f.sort=function(a,c){a.sort(c||Z.f.C)};Z.f.rg=function(a,c){for(var d=Array(a.length),e=0;e<a.length;e++)d[e]={index:e,value:a[e]};var f=c||Z.f.C;Z.f.sort(d,function(a,c){return f(a.value,c.value)||a.index-c.index});for(e=0;e<a.length;e++)a[e]=d[e].value};Z.f.pd=function(a,c,d){var e=d||Z.f.C;Z.f.sort(a,function(a,d){return e(c(a),c(d))})};Z.f.pg=function(a,c,d){Z.f.pd(a,function(a){return a[c]},d)};
Z.f.vf=function(a,c,d){c=c||Z.f.C;for(var e=1;e<a.length;e++){var f=c(a[e-1],a[e]);if(0<f||0==f&&d)return!1}return!0};Z.f.Ie=function(a,c,d){if(!Z.Ia(a)||!Z.Ia(c)||a.length!=c.length)return!1;var e=a.length;d=d||Z.f.Dc;for(var f=0;f<e;f++)if(!d(a[f],c[f]))return!1;return!0};Z.f.Ae=function(a,c,d){d=d||Z.f.C;for(var e=Math.min(a.length,c.length),f=0;f<e;f++){var g=d(a[f],c[f]);if(0!=g)return g}return Z.f.C(a.length,c.length)};Z.f.C=function(a,c){return a>c?1:a<c?-1:0};
Z.f.jf=function(a,c){return-Z.f.C(a,c)};Z.f.Dc=function(a,c){return a===c};Z.f.je=function(a,c,d){d=Z.f.cb(a,c,d);return 0>d?(Z.f.tb(a,c,-(d+1)),!0):!1};Z.f.ke=function(a,c,d){c=Z.f.cb(a,c,d);return 0<=c?Z.f.L(a,c):!1};Z.f.me=function(a,c,d){for(var e={},f=0;f<a.length;f++){var g=a[f],h=c.call(d,g,f,a);Z.N(h)&&(e[h]||(e[h]=[])).push(g)}return e};Z.f.Ag=function(a,c,d){var e={};Z.f.forEach(a,function(f,g){e[c.call(d,f,g,a)]=f});return e};
Z.f.Pf=function(a,c,d){var e=[],f=0,g=a;d=d||1;void 0!==c&&(f=a,g=c);if(0>d*(g-f))return[];if(0<d)for(a=f;a<g;a+=d)e.push(a);else for(a=f;a>g;a+=d)e.push(a);return e};Z.f.repeat=function(a,c){for(var d=[],e=0;e<c;e++)d[e]=a;return d};Z.f.Ic=function(a){for(var c=[],d=0;d<arguments.length;d++){var e=arguments[d];if(Z.isArray(e))for(var f=0;f<e.length;f+=8192)for(var g=Z.f.Ic.apply(null,Z.f.slice(e,f,f+8192)),h=0;h<g.length;h++)c.push(g[h]);else c.push(e)}return c};
Z.f.rotate=function(a,c){a.length&&(c%=a.length,0<c?Array.prototype.unshift.apply(a,a.splice(-c,c)):0>c&&Array.prototype.push.apply(a,a.splice(0,-c)));return a};Z.f.Ff=function(a,c,d){c=Array.prototype.splice.call(a,c,1);Array.prototype.splice.call(a,d,0,c[0])};
Z.f.Mg=function(a){if(!arguments.length)return[];for(var c=[],d=arguments[0].length,e=1;e<arguments.length;e++)arguments[e].length<d&&(d=arguments[e].length);for(e=0;e<d;e++){for(var f=[],g=0;g<arguments.length;g++)f.push(arguments[g][e]);c.push(f)}return c};Z.f.og=function(a,c){c=c||Math.random;for(var d=a.length-1;0<d;d--){var e=Math.floor(c()*(d+1)),f=a[d];a[d]=a[e];a[e]=f}};Z.f.De=function(a,c){var d=[];Z.f.forEach(c,function(c){d.push(a[c])});return d};
Z.f.Ce=function(a,c,d){return Z.f.concat.apply([],Z.f.map(a,c,d))};Z.locale={};
Z.locale.J={COUNTRY:{AD:"Andorra",AE:hb,AF:U,AG:ca,AI:"Anguilla",AL:"Shqip\u00ebria",AM:gb,AN:pa,AO:"Angola",AQ:"Antarctica",AR:"Argentina",AS:b,AT:"\u00d6sterreich",AU:"Australia",AW:"Aruba",AX:"\u00c5land",AZ:"Az\u0259rbaycan",BA:p,BB:"Barbados",BD:"\u09ac\u09be\u0982\u09b2\u09be\u09a6\u09c7\u09b6",BE:"Belgi\u00eb",BF:"Burkina Faso",BG:"\u0411\u044a\u043b\u0433\u0430\u0440\u0438\u044f",BH:"\u0627\u0644\u0628\u062d\u0631\u064a\u0646",BI:"Burundi",BJ:"B\u00e9nin",BM:"Bermuda",BN:"Brunei",BO:k,BR:"Brasil",
BS:"Bahamas",BT:"\u092d\u0942\u091f\u093e\u0928",BV:"Bouvet Island",BW:q,BY:db,BZ:"Belize",CA:"Canada",CC:"Cocos (Keeling) Islands",CD:Ca,CF:Ba,CG:"Congo",CH:"Schweiz",CI:"C\u00f4te d\u2019Ivoire",CK:"Cook Islands",CL:"Chile",CM:"Cameroun",CN:"\u4e2d\u56fd",CO:"Colombia",CR:"Costa Rica",CS:Ga,CU:"Cuba",CV:"Cabo Verde",CX:fa,CY:"\u039a\u03cd\u03c0\u03c1\u03bf\u03c2",CZ:cb,DD:"East Germany",DE:"Deutschland",DJ:"Jabuuti",DK:"Danmark",DM:"Dominica",DO:za,DZ:"\u0627\u0644\u062c\u0632\u0627\u0626\u0631",
EC:"Ecuador",EE:"Eesti",EG:"\u0645\u0635\u0631",EH:ib,ER:"\u0627\u0631\u064a\u062a\u0631\u064a\u0627",ES:"Espa\u00f1a",ET:X,FI:"Suomi",FJ:"\u092b\u093f\u091c\u0940",FK:ga,FM:x,FO:"F\u00f8royar",FR:"France",FX:"Metropolitan France",GA:"Gabon",GB:Ra,GD:"Grenada",GE:"\u10e1\u10d0\u10e5\u10d0\u10e0\u10d7\u10d5\u10d4\u10da\u10dd",GF:ia,GG:"Guernsey",GH:r,GI:"Gibraltar",GL:ja,GM:"Gambia",GN:"Guin\u00e9e",GP:"Guadeloupe",GQ:ha,GR:"\u0395\u03bb\u03bb\u03ac\u03b4\u03b1",GS:"South Georgia and the South Sandwich Islands",
GT:"Guatemala",GU:"Guam",GW:"Guin\u00e9 Bissau",GY:"Guyana",HK:"\u9999\u6e2f",HM:"Heard Island and McDonald Islands",HN:t,HR:"Hrvatska",HT:"Ha\u00efti",HU:"Magyarorsz\u00e1g",ID:u,IE:"Ireland",IL:"\u05d9\u05e9\u05e8\u05d0\u05dc",IM:"Isle of Man",IN:W,IO:"British Indian Ocean Territory",IQ:"\u0627\u0644\u0639\u0631\u0627\u0642",IR:"\u0627\u06cc\u0631\u0627\u0646",IS:"\u00cdsland",IT:"Italia",JE:"Jersey",JM:"Jamaica",JO:"\u0627\u0644\u0623\u0631\u062f\u0646",JP:"\u65e5\u672c",KE:"Kenya",KG:eb,KH:"\u1780\u1798\u17d2\u1796\u17bb\u1787\u17b6",
KI:w,KM:lb,KN:Da,KP:mb,KR:"\ub300\ud55c\ubbfc\uad6d",KW:"\u0627\u0644\u0643\u0648\u064a\u062a",KY:ea,KZ:"\u041a\u0430\u0437\u0430\u0445\u0441\u0442\u0430\u043d",LA:"\u0e25\u0e32\u0e27",LB:"\u0644\u0628\u0646\u0627\u0646",LC:"Saint Lucia",LI:"Liechtenstein",LK:"\u0b87\u0bb2\u0b99\u0bcd\u0b95\u0bc8",LR:"Liberia",LS:"Lesotho",LT:"Lietuva",LU:la,LV:"Latvija",LY:"\u0644\u064a\u0628\u064a\u0627",MA:"\u0627\u0644\u0645\u063a\u0631\u0628",MC:"Monaco",MD:oa,ME:"\u0426\u0440\u043d\u0430 \u0413\u043e\u0440\u0430",
MG:ma,MH:na,MK:"\u041c\u0430\u043a\u0435\u0434\u043e\u043d\u0438\u0458\u0430",ML:"\u0645\u0627\u0644\u064a",MM:"Myanmar",MN:"\u8499\u53e4",MO:"\u6fb3\u95e8",MP:sa,MQ:"Martinique",MR:"\u0645\u0648\u0631\u064a\u062a\u0627\u0646\u064a\u0627",MS:"Montserrat",MT:"Malta",MU:"Mauritius",MV:"Maldives",MW:"Malawi",MX:"M\u00e9xico",MY:"Malaysia",MZ:"Mo\u00e7ambique",NA:"Namibia",NC:ta,NE:"Niger",NF:ra,NG:y,NI:"Nicaragua",NL:"Nederland",NO:"Norge",NP:"\u0928\u0947\u092a\u093e\u0932",NR:"Nauru",NT:"Neutral Zone",
NU:"Niue",NZ:qa,OM:"\u0639\u0645\u0627\u0646",PA:"Panam\u00e1",PE:"Per\u00fa",PF:wa,PG:z,PH:va,PK:V,PL:"Polska",PM:Fa,PN:"Pitcairn",PR:xa,PS:"\u0641\u0644\u0633\u0637\u064a\u0646",PT:"Portugal",PW:"Palau",PY:ua,QA:"\u0642\u0637\u0631",QO:"Outlying Oceania",QU:"European Union",RE:"R\u00e9union",RO:"Rom\u00e2nia",RS:"\u0421\u0440\u0431\u0438\u0458\u0430",RU:"\u0420\u043e\u0441\u0441\u0438\u044f",RW:A,SA:jb,SB:Ja,SC:Ha,SD:"\u0627\u0644\u0633\u0648\u062f\u0627\u0646",SE:"Sverige",SG:"\u65b0\u52a0\u5761",
SH:"Saint Helena",SI:"Slovenija",SJ:Ka,SK:Ia,SL:"Sierra Leone",SM:"San Marino",SN:C,SO:"Somali",SR:"Suriname",ST:Ma,SU:"Union of Soviet Socialist Republics",SV:"El Salvador",SY:"\u0633\u0648\u0631\u064a\u0627",SZ:La,TC:Pa,TD:"\u062a\u0634\u0627\u062f",TF:"French Southern Territories",TG:"Togo",TH:"\u0e1b\u0e23\u0e30\u0e40\u0e17\u0e28\u0e44\u0e17\u0e22",TJ:"\u062a\u0627\u062c\u06cc\u06a9\u0633\u062a\u0627\u0646",TK:E,TL:Oa,TM:"\u0422\u0443\u0440\u043a\u043c\u0435\u043d\u0438\u0441\u0442\u0430\u043d",
TN:"\u062a\u0648\u0646\u0633",TO:"Tonga",TR:G,TT:"Trinidad y Tobago",TV:F,TW:"\u53f0\u6e7e",TZ:Na,UA:"\u0423\u043a\u0440\u0430\u0457\u043d\u0430",UG:"Uganda",UM:Ta,US:Sa,UY:"Uruguay",UZ:"\u040e\u0437\u0431\u0435\u043a\u0438\u0441\u0442\u043e\u043d",VA:"Vaticano",VC:Ea,VE:"Venezuela",VG:da,VI:Qa,VN:"Vi\u1ec7t Nam",VU:H,WF:Va,WS:"Samoa",YD:"People's Democratic Republic of Yemen",YE:"\u0627\u0644\u064a\u0645\u0646",YT:"Mayotte",ZA:B,ZM:"Zambia",ZW:"Zimbabwe",ZZ:Ua,aa_DJ:"Jabuuti",aa_ER:"\u00c9rythr\u00e9e",
aa_ER_SAAHO:"\u00c9rythr\u00e9e",aa_ET:v,af_NA:"Namibi\u00eb",af_ZA:"Suid-Afrika",ak_GH:r,am_ET:X,ar_AE:hb,ar_BH:"\u0627\u0644\u0628\u062d\u0631\u064a\u0646",ar_DJ:"\u062c\u064a\u0628\u0648\u062a\u064a",ar_DZ:"\u0627\u0644\u062c\u0632\u0627\u0626\u0631",ar_EG:"\u0645\u0635\u0631",ar_EH:ib,ar_ER:"\u0627\u0631\u064a\u062a\u0631\u064a\u0627",ar_IL:"\u0627\u0633\u0631\u0627\u0626\u064a\u0644",ar_IQ:"\u0627\u0644\u0639\u0631\u0627\u0642",ar_JO:"\u0627\u0644\u0623\u0631\u062f\u0646",ar_KM:lb,ar_KW:"\u0627\u0644\u0643\u0648\u064a\u062a",
ar_LB:"\u0644\u0628\u0646\u0627\u0646",ar_LY:"\u0644\u064a\u0628\u064a\u0627",ar_MA:"\u0627\u0644\u0645\u063a\u0631\u0628",ar_MR:"\u0645\u0648\u0631\u064a\u062a\u0627\u0646\u064a\u0627",ar_OM:"\u0639\u0645\u0627\u0646",ar_PS:"\u0641\u0644\u0633\u0637\u064a\u0646",ar_QA:"\u0642\u0637\u0631",ar_SA:jb,ar_SD:"\u0627\u0644\u0633\u0648\u062f\u0627\u0646",ar_SY:"\u0633\u0648\u0631\u064a\u0627",ar_TD:"\u062a\u0634\u0627\u062f",ar_TN:"\u062a\u0648\u0646\u0633",ar_YE:"\u0627\u0644\u064a\u0645\u0646",as_IN:"\u09ad\u09be\u09f0\u09a4",
ay_BO:k,az_AZ:"Az\u0259rbaycan",az_Cyrl_AZ:"\u0410\u0437\u04d9\u0440\u0431\u0430\u0458\u04b9\u0430\u043d",az_Latn_AZ:"Azerbaycan",be_BY:db,bg_BG:"\u0411\u044a\u043b\u0433\u0430\u0440\u0438\u044f",bi_VU:H,bn_BD:"\u09ac\u09be\u0982\u09b2\u09be\u09a6\u09c7\u09b6",bn_IN:"\u09ad\u09be\u09b0\u09a4",bo_CN:"\u0f62\u0f92\u0fb1\u0f0b\u0f53\u0f42",bo_IN:"\u0f62\u0f92\u0fb1\u0f0b\u0f42\u0f62\u0f0b",bs_BA:p,byn_ER:"\u12a4\u122d\u1275\u122b",ca_AD:"Andorra",ca_ES:"Espanya",cch_NG:y,ch_GU:"Guam",chk_FM:x,cop_Arab_EG:"\u0645\u0635\u0631",
cop_Arab_US:kb,cop_EG:"\u0645\u0635\u0631",cop_US:kb,cs_CZ:cb,cy_GB:"Prydain Fawr",da_DK:"Danmark",da_GL:"Gr\u00f8nland",de_AT:"\u00d6sterreich",de_BE:"Belgien",de_CH:"Schweiz",de_DE:"Deutschland",de_LI:"Liechtenstein",de_LU:"Luxemburg",dv_MV:"Maldives",dz_BT:"Bhutan",ee_GH:r,ee_TG:"Togo",efi_NG:y,el_CY:"\u039a\u03cd\u03c0\u03c1\u03bf\u03c2",el_GR:"\u0395\u03bb\u03bb\u03ac\u03b4\u03b1",en_AG:ca,en_AI:"Anguilla",en_AS:b,en_AU:"Australia",en_BB:"Barbados",en_BE:"Belgium",en_BM:"Bermuda",en_BS:"Bahamas",
en_BW:q,en_BZ:"Belize",en_CA:"Canada",en_CC:"Cocos Islands",en_CK:"Cook Islands",en_CM:"Cameroon",en_CX:fa,en_DM:"Dominica",en_FJ:"Fiji",en_FK:ga,en_FM:x,en_GB:Ra,en_GD:"Grenada",en_GG:"Guernsey",en_GH:r,en_GI:"Gibraltar",en_GM:"Gambia",en_GU:"Guam",en_GY:"Guyana",en_HK:"Hong Kong",en_HN:t,en_IE:"Ireland",en_IM:"Isle of Man",en_IN:"India",en_JE:"Jersey",en_JM:"Jamaica",en_KE:"Kenya",en_KI:w,en_KN:Da,en_KY:ea,en_LC:"Saint Lucia",en_LR:"Liberia",en_LS:"Lesotho",en_MH:na,en_MP:sa,en_MS:"Montserrat",
en_MT:"Malta",en_MU:"Mauritius",en_MW:"Malawi",en_NA:"Namibia",en_NF:ra,en_NG:y,en_NR:"Nauru",en_NU:"Niue",en_NZ:qa,en_PG:z,en_PH:va,en_PK:"Pakistan",en_PN:"Pitcairn",en_PR:xa,en_RW:A,en_SB:Ja,en_SC:Ha,en_SG:"Singapore",en_SH:"Saint Helena",en_SL:"Sierra Leone",en_SZ:La,en_TC:Pa,en_TK:E,en_TO:"Tonga",en_TT:"Trinidad and Tobago",en_TV:F,en_TZ:Na,en_UG:"Uganda",en_UM:Ta,en_US:Sa,en_US_POSIX:Sa,en_VC:Ea,en_VG:da,en_VI:Qa,en_VU:H,en_WS:"Samoa",en_ZA:B,en_ZM:"Zambia",en_ZW:"Zimbabwe",es_AR:"Argentina",
es_BO:k,es_CL:"Chile",es_CO:"Colombia",es_CR:"Costa Rica",es_CU:"Cuba",es_DO:za,es_EC:"Ecuador",es_ES:"Espa\u00f1a",es_GQ:"Guinea Ecuatorial",es_GT:"Guatemala",es_HN:t,es_MX:"M\u00e9xico",es_NI:"Nicaragua",es_PA:"Panam\u00e1",es_PE:"Per\u00fa",es_PH:"Filipinas",es_PR:xa,es_PY:ua,es_SV:"El Salvador",es_US:"Estados Unidos",es_UY:"Uruguay",es_VE:"Venezuela",et_EE:"Eesti",eu_ES:"Espainia",fa_AF:U,fa_IR:"\u0627\u06cc\u0631\u0627\u0646",fi_FI:"Suomi",fil_PH:va,fj_FJ:"Fiji",fo_FO:"F\u00f8royar",fr_BE:"Belgique",
fr_BF:"Burkina Faso",fr_BI:"Burundi",fr_BJ:"B\u00e9nin",fr_CA:"Canada",fr_CD:Ca,fr_CF:Ba,fr_CG:"Congo",fr_CH:"Suisse",fr_CI:"C\u00f4te d\u2019Ivoire",fr_CM:"Cameroun",fr_DJ:"Djibouti",fr_DZ:"Alg\u00e9rie",fr_FR:"France",fr_GA:"Gabon",fr_GF:ia,fr_GN:"Guin\u00e9e",fr_GP:"Guadeloupe",fr_GQ:ha,fr_HT:"Ha\u00efti",fr_KM:"Comores",fr_LU:la,fr_MA:"Maroc",fr_MC:"Monaco",fr_MG:ma,fr_ML:"Mali",fr_MQ:"Martinique",fr_MU:"Maurice",fr_NC:ta,fr_NE:"Niger",fr_PF:wa,fr_PM:Fa,fr_RE:"R\u00e9union",fr_RW:A,fr_SC:Ha,fr_SN:C,
fr_SY:"Syrie",fr_TD:"Tchad",fr_TG:"Togo",fr_TN:"Tunisie",fr_VU:H,fr_WF:Va,fr_YT:"Mayotte",fur_IT:"Italia",ga_IE:"\u00c9ire",gaa_GH:r,gez_ER:"\u12a4\u122d\u1275\u122b",gez_ET:X,gil_KI:w,gl_ES:"Espa\u00f1a",gn_PY:ua,gu_IN:"\u0aad\u0abe\u0ab0\u0aa4",gv_GB:Aa,ha_Arab_NG:"\u0646\u064a\u062c\u064a\u0631\u064a\u0627",ha_GH:"\u063a\u0627\u0646\u0627",ha_Latn_GH:r,ha_Latn_NE:"Niger",ha_Latn_NG:"Nig\u00e9ria",ha_NE:"\u0627\u0644\u0646\u064a\u062c\u0631",ha_NG:"\u0646\u064a\u062c\u064a\u0631\u064a\u0627",haw_US:"\u02bbAmelika Hui P\u016b \u02bbIa",
he_IL:"\u05d9\u05e9\u05e8\u05d0\u05dc",hi_IN:W,ho_PG:z,hr_BA:p,hr_HR:"Hrvatska",ht_HT:"Ha\u00efti",hu_HU:"Magyarorsz\u00e1g",hy_AM:gb,hy_AM_REVISED:gb,id_ID:u,ig_NG:y,ii_CN:"\ua34f\ua1e9",is_IS:"\u00cdsland",it_CH:"Svizzera",it_IT:"Italia",it_SM:"San Marino",ja_JP:"\u65e5\u672c",ka_GE:"\u10e1\u10d0\u10e5\u10d0\u10e0\u10d7\u10d5\u10d4\u10da\u10dd",kaj_NG:y,kam_KE:"Kenya",kcg_NG:y,kfo_NG:"Nig\u00e9ria",kk_KZ:"\u049a\u0430\u0437\u0430\u049b\u0441\u0442\u0430\u043d",kl_GL:ja,km_KH:"\u1780\u1798\u17d2\u1796\u17bb\u1787\u17b6",
kn_IN:"\u0cad\u0cbe\u0cb0\u0ca4",ko_KP:mb,ko_KR:"\ub300\ud55c\ubbfc\uad6d",kok_IN:W,kos_FM:x,kpe_GN:"Guin\u00e9e",kpe_LR:"Lib\u00e9ria",ks_IN:W,ku_IQ:"Irak",ku_IR:"\u0130ran",ku_Latn_IQ:"Irak",ku_Latn_IR:"\u0130ran",ku_Latn_SY:"Suriye",ku_Latn_TR:G,ku_SY:"Suriye",ku_TR:G,kw_GB:Aa,ky_Cyrl_KG:eb,ky_KG:"K\u0131rg\u0131zistan",la_VA:"Vaticano",lb_LU:la,ln_CD:Ca,ln_CG:"Kongo",lo_LA:"Laos",lt_LT:"Lietuva",lv_LV:"Latvija",mg_MG:ma,mh_MH:na,mi_NZ:qa,mk_MK:"\u041c\u0430\u043a\u0435\u0434\u043e\u043d\u0438\u0458\u0430",
ml_IN:"\u0d07\u0d28\u0d4d\u0d24\u0d4d\u0d2f",mn_Cyrl_MN:"\u041c\u043e\u043d\u0433\u043e\u043b\u0438\u044f",mn_MN:"\u041c\u043e\u043d\u0433\u043e\u043b\u0438\u044f",mr_IN:W,ms_BN:"Brunei",ms_MY:"Malaysia",ms_SG:"Singapura",mt_MT:"Malta",my_MM:"Myanmar",na_NR:"Nauru",nb_NO:"Norge",nb_SJ:Ka,ne_NP:"\u0928\u0947\u092a\u093e\u0932",niu_NU:"Niue",nl_AN:pa,nl_AW:"Aruba",nl_BE:"Belgi\u00eb",nl_NL:"Nederland",nl_SR:"Suriname",nn_NO:"Noreg",nr_ZA:B,nso_ZA:B,ny_MW:"Malawi",om_ET:v,om_KE:"Keeniyaa",or_IN:"\u0b2d\u0b3e\u0b30\u0b24",
pa_Arab_PK:V,pa_Guru_IN:"\u0a2d\u0a3e\u0a30\u0a24",pa_IN:"\u0a2d\u0a3e\u0a30\u0a24",pa_PK:V,pap_AN:pa,pau_PW:"Palau",pl_PL:"Polska",pon_FM:x,ps_AF:U,pt_AO:"Angola",pt_BR:"Brasil",pt_CV:"Cabo Verde",pt_GW:"Guin\u00e9 Bissau",pt_MZ:"Mo\u00e7ambique",pt_PT:"Portugal",pt_ST:Ma,pt_TL:Oa,qu_BO:k,qu_PE:"Per\u00fa",rm_CH:"Schweiz",rn_BI:"Burundi",ro_MD:oa,ro_RO:"Rom\u00e2nia",ru_BY:db,ru_KG:eb,ru_KZ:"\u041a\u0430\u0437\u0430\u0445\u0441\u0442\u0430\u043d",ru_RU:"\u0420\u043e\u0441\u0441\u0438\u044f",ru_UA:"\u0423\u043a\u0440\u0430\u0438\u043d\u0430",
rw_RW:A,sa_IN:W,sd_Deva_IN:W,sd_IN:W,se_FI:"Finland",se_NO:"Norge",sg_CF:Ba,sh_BA:"Bosnia and Herzegovina",sh_CS:Ga,si_LK:"Sri Lanka",sid_ET:v,sk_SK:Ia,sl_SI:"Slovenija",sm_AS:b,sm_WS:"Samoa",so_DJ:"Jabuuti",so_ET:"Itoobiya",so_KE:"Kiiniya",so_SO:"Soomaaliya",sq_AL:"Shqip\u00ebria",sr_BA:"\u0411\u043e\u0441\u043d\u0430 \u0438 \u0425\u0435\u0440\u0446\u0435\u0433\u043e\u0432\u0438\u043d\u0430",sr_CS:"\u0421\u0440\u0431\u0438\u0458\u0430 \u0438 \u0426\u0440\u043d\u0430 \u0413\u043e\u0440\u0430",sr_Cyrl_BA:"\u0411\u043e\u0441\u043d\u0438\u044f",
sr_Cyrl_CS:"\u0421\u0435\u0440\u0431\u0438\u044f \u0438 \u0427\u0435\u0440\u043d\u043e\u0433\u043e\u0440\u0438\u044f",sr_Cyrl_ME:"\u0427\u0435\u0440\u043d\u043e\u0433\u043e\u0440\u0438\u044f",sr_Cyrl_RS:"\u0421\u0435\u0440\u0431\u0438\u044f",sr_Latn_BA:p,sr_Latn_CS:"Srbija i Crna Gora",sr_Latn_ME:"Crna Gora",sr_Latn_RS:"Srbija",sr_ME:"\u0426\u0440\u043d\u0430 \u0413\u043e\u0440\u0430",sr_RS:"\u0421\u0440\u0431\u0438\u0458\u0430",ss_SZ:La,ss_ZA:B,st_LS:"Lesotho",st_ZA:B,su_ID:u,sv_AX:"\u00c5land",
sv_FI:"Finland",sv_SE:"Sverige",sw_KE:"Kenya",sw_TZ:Na,sw_UG:"Uganda",swb_KM:lb,syr_SY:"Syria",ta_IN:"\u0b87\u0ba8\u0bcd\u0ba4\u0bbf\u0baf\u0bbe",ta_LK:"\u0b87\u0bb2\u0b99\u0bcd\u0b95\u0bc8",ta_SG:"\u0b9a\u0bbf\u0b99\u0bcd\u0b95\u0baa\u0bcd\u0baa\u0bc2\u0bb0\u0bcd",te_IN:"\u0c2d\u0c3e\u0c30\u0c24 \u0c26\u0c47\u0c33\u0c02",tet_TL:Oa,tg_Cyrl_TJ:"\u0422\u0430\u0434\u0436\u0438\u043a\u0438\u0441\u0442\u0430\u043d",tg_TJ:"\u062a\u0627\u062c\u06a9\u0633\u062a\u0627\u0646",th_TH:"\u0e1b\u0e23\u0e30\u0e40\u0e17\u0e28\u0e44\u0e17\u0e22",
ti_ER:"\u12a4\u122d\u1275\u122b",ti_ET:X,tig_ER:"\u12a4\u122d\u1275\u122b",tk_TM:"\u062a\u0631\u06a9\u0645\u0646\u0633\u062a\u0627\u0646",tkl_TK:E,tn_BW:q,tn_ZA:B,to_TO:"Tonga",tpi_PG:z,tr_CY:"G\u00fcney K\u0131br\u0131s Rum Kesimi",tr_TR:G,ts_ZA:B,tt_RU:"\u0420\u043e\u0441\u0441\u0438\u044f",tvl_TV:F,ty_PF:wa,uk_UA:"\u0423\u043a\u0440\u0430\u0457\u043d\u0430",uli_FM:x,und_ZZ:Ua,ur_IN:"\u0628\u06be\u0627\u0631\u062a",ur_PK:V,uz_AF:"Afganistan",uz_Arab_AF:U,uz_Cyrl_UZ:"\u0423\u0437\u0431\u0435\u043a\u0438\u0441\u0442\u0430\u043d",
uz_Latn_UZ:"O\u02bfzbekiston",uz_UZ:"\u040e\u0437\u0431\u0435\u043a\u0438\u0441\u0442\u043e\u043d",ve_ZA:B,vi_VN:"Vi\u1ec7t Nam",wal_ET:X,wo_Arab_SN:"\u0627\u0644\u0633\u0646\u063a\u0627\u0644",wo_Latn_SN:C,wo_SN:C,xh_ZA:B,yap_FM:x,yo_NG:y,zh_CN:"\u4e2d\u56fd",zh_HK:"\u9999\u6e2f",zh_Hans_CN:"\u4e2d\u56fd",zh_Hans_SG:"\u65b0\u52a0\u5761",zh_Hant_HK:"\u4e2d\u83ef\u4eba\u6c11\u5171\u548c\u570b\u9999\u6e2f\u7279\u5225\u884c\u653f\u5340",zh_Hant_MO:"\u6fb3\u9580",zh_Hant_TW:"\u81fa\u7063",zh_MO:"\u6fb3\u95e8",
zh_SG:"\u65b0\u52a0\u5761",zh_TW:"\u53f0\u6e7e",zu_ZA:B},LANGUAGE:{aa:"afar",ab:"\u0430\u0431\u0445\u0430\u0437\u0441\u043a\u0438\u0439",ace:"Aceh",ach:"Acoli",ada:"Adangme",ady:"\u0430\u0434\u044b\u0433\u0435\u0439\u0441\u043a\u0438\u0439",ae:"Avestan",af:"Afrikaans",afa:"Afro-Asiatic Language",afh:"Afrihili",ain:"Ainu",ak:"Akan",akk:"Akkadian",ale:"Aleut",alg:"Algonquian Language",alt:"Southern Altai",am:"\u12a0\u121b\u122d\u129b",an:"Aragonese",ang:"Old English",anp:"Angika",apa:"Apache Language",
ar:"\u0627\u0644\u0639\u0631\u0628\u064a\u0629",arc:"Aramaic",arn:"Araucanian",arp:"Arapaho",art:"Artificial Language",arw:"Arawak",as:"\u0985\u09b8\u09ae\u09c0\u09af\u09bc\u09be",ast:"asturiano",ath:"Athapascan Language",aus:"Australian Language",av:"\u0430\u0432\u0430\u0440\u0441\u043a\u0438\u0439",awa:"Awadhi",ay:"aimara",az:"az\u0259rbaycanca",az_Arab:"\u062a\u0631\u06a9\u06cc \u0622\u0630\u0631\u0628\u0627\u06cc\u062c\u0627\u0646\u06cc",az_Cyrl:"\u0410\u0437\u04d9\u0440\u0431\u0430\u0458\u04b9\u0430\u043d",
az_Latn:"Azerice",ba:"\u0431\u0430\u0448\u043a\u0438\u0440\u0441\u043a\u0438\u0439",bad:"Banda",bai:"Bamileke Language",bal:"\u0628\u0644\u0648\u0686\u06cc",ban:"Balin",bas:"Basa",bat:"Baltic Language",be:"\u0431\u0435\u043b\u0430\u0440\u0443\u0441\u043a\u0430\u044f",bej:"Beja",bem:"Bemba",ber:"Berber",bg:"\u0431\u044a\u043b\u0433\u0430\u0440\u0441\u043a\u0438",bh:"\u092c\u093f\u0939\u093e\u0930\u0940",bho:"Bhojpuri",bi:"bichelamar ; bislama",bik:"Bikol",bin:"Bini",bla:"Siksika",bm:"bambara",bn:"\u09ac\u09be\u0982\u09b2\u09be",
bnt:"Bantu",bo:"\u0f54\u0f7c\u0f51\u0f0b\u0f66\u0f90\u0f51\u0f0b",br:"breton",bra:"Braj",bs:"Bosanski",btk:"Batak",bua:"Buriat",bug:"Bugis",byn:"\u1265\u120a\u1295",ca:"catal\u00e0",cad:"Caddo",cai:"Central American Indian Language",car:"Carib",cau:"Caucasian Language",cch:"Atsam",ce:"\u0447\u0435\u0447\u0435\u043d\u0441\u043a\u0438\u0439",ceb:"Cebuano",cel:"Celtic Language",ch:"Chamorro",chb:"Chibcha",chg:"Chagatai",chk:"Chuukese",chm:"\u043c\u0430\u0440\u0438\u0439\u0441\u043a\u0438\u0439 (\u0447\u0435\u0440\u0435\u043c\u0438\u0441\u0441\u043a\u0438\u0439)",
chn:"Chinook Jargon",cho:"Choctaw",chp:"Chipewyan",chr:"Cherokee",chy:"Cheyenne",cmc:"Chamic Language",co:"corse",cop:"\u0642\u0628\u0637\u064a\u0629",cop_Arab:"\u0642\u0628\u0637\u064a\u0629",cpe:"English-based Creole or Pidgin",cpf:"French-based Creole or Pidgin",cpp:"Portuguese-based Creole or Pidgin",cr:"Cree",crh:"Crimean Turkish",crp:"Creole or Pidgin",cs:"\u010de\u0161tina",csb:"Kashubian",cu:"Church Slavic",cus:"Cushitic Language",cv:"\u0447\u0443\u0432\u0430\u0448\u0441\u043a\u0438\u0439",
cy:"Cymraeg",da:"dansk",dak:"Dakota",dar:"\u0434\u0430\u0440\u0433\u0432\u0430",day:"Dayak",de:"Deutsch",del:"Delaware",den:"Slave",dgr:"Dogrib",din:"Dinka",doi:"\u0627\u0644\u062f\u0648\u062c\u0631\u0649",dra:"Dravidian Language",dsb:"Lower Sorbian",dua:"Duala",dum:"Middle Dutch",dv:"Divehi",dyu:"dioula",dz:"\u0f62\u0fab\u0f7c\u0f44\u0f0b\u0f41",ee:"Ewe",efi:"Efik",egy:"Ancient Egyptian",eka:"Ekajuk",el:"\u0395\u03bb\u03bb\u03b7\u03bd\u03b9\u03ba\u03ac",elx:"Elamite",en:"English",enm:"Middle English",
eo:"esperanto",es:"espa\u00f1ol",et:"eesti",eu:"euskara",ewo:"Ewondo",fa:"\u0641\u0627\u0631\u0633\u06cc",fan:"fang",fat:"Fanti",ff:"Fulah",fi:"suomi",fil:"Filipino",fiu:"Finno-Ugrian Language",fj:"Fijian",fo:"f\u00f8royskt",fon:"Fon",fr:"fran\u00e7ais",frm:"Middle French",fro:"Old French",frr:"Northern Frisian",frs:"Eastern Frisian",fur:"friulano",fy:"Fries",ga:"Gaeilge",gaa:"Ga",gay:"Gayo",gba:"Gbaya",gd:"Scottish Gaelic",gem:"Germanic Language",gez:"\u130d\u12d5\u12dd\u129b",gil:"Gilbertese",gl:"galego",
gmh:"Middle High German",gn:"guaran\u00ed",goh:"Old High German",gon:"Gondi",gor:"Gorontalo",got:"Gothic",grb:"Grebo",grc:"\u0391\u03c1\u03c7\u03b1\u03af\u03b1 \u0395\u03bb\u03bb\u03b7\u03bd\u03b9\u03ba\u03ac",gsw:"Schweizerdeutsch",gu:"\u0a97\u0ac1\u0a9c\u0ab0\u0abe\u0aa4\u0ac0",gv:"Gaelg",gwi:"Gwich\u02bcin",ha:"\u0627\u0644\u0647\u0648\u0633\u0627",ha_Arab:"\u0627\u0644\u0647\u0648\u0633\u0627",ha_Latn:"haoussa",hai:"Haida",haw:"\u02bb\u014dlelo Hawai\u02bbi",he:"\u05e2\u05d1\u05e8\u05d9\u05ea",
hi:"\u0939\u093f\u0902\u0926\u0940",hil:"Hiligaynon",him:"Himachali",hit:"Hittite",hmn:"Hmong",ho:"Hiri Motu",hr:"hrvatski",hsb:"Upper Sorbian",ht:"ha\u00eftien",hu:"magyar",hup:"Hupa",hy:"\u0540\u0561\u0575\u0565\u0580\u0567\u0576",hz:"Herero",ia:"interlingvao",iba:"Iban",id:"Bahasa Indonesia",ie:"Interlingue",ig:"Igbo",ii:"\ua188\ua320\ua259",ijo:"Ijo",ik:"Inupiaq",ilo:"Iloko",inc:"Indic Language",ine:"Indo-European Language",inh:"\u0438\u043d\u0433\u0443\u0448\u0441\u043a\u0438\u0439",io:"Ido",
ira:"Iranian Language",iro:"Iroquoian Language",is:"\u00edslenska",it:"italiano",iu:"Inuktitut",ja:"\u65e5\u672c\u8a9e",jbo:"Lojban",jpr:"Judeo-Persian",jrb:"Judeo-Arabic",jv:"Jawa",ka:"\u10e5\u10d0\u10e0\u10d7\u10e3\u10da\u10d8",kaa:"\u043a\u0430\u0440\u0430\u043a\u0430\u043b\u043f\u0430\u043a\u0441\u043a\u0438\u0439",kab:"kabyle",kac:"Kachin",kaj:"Jju",kam:"Kamba",kar:"Karen",kaw:"Kawi",kbd:"\u043a\u0430\u0431\u0430\u0440\u0434\u0438\u043d\u0441\u043a\u0438\u0439",kcg:"Tyap",kfo:"koro",kg:"Kongo",
kha:"Khasi",khi:"Khoisan Language",kho:"Khotanese",ki:"Kikuyu",kj:"Kuanyama",kk:"\u049a\u0430\u0437\u0430\u049b",kl:"kalaallisut",km:"\u1797\u17b6\u179f\u17b6\u1781\u17d2\u1798\u17c2\u179a",kmb:"quimbundo",kn:"\u0c95\u0ca8\u0ccd\u0ca8\u0ca1",ko:"\ud55c\uad6d\uc5b4",kok:"\u0915\u094b\u0902\u0915\u0923\u0940",kos:"Kosraean",kpe:"kpell\u00e9",kr:"Kanuri",krc:"\u043a\u0430\u0440\u0430\u0447\u0430\u0435\u0432\u043e-\u0431\u0430\u043b\u043a\u0430\u0440\u0441\u043a\u0438\u0439",krl:"\u043a\u0430\u0440\u0435\u043b\u044c\u0441\u043a\u0438\u0439",
kro:"Kru",kru:"Kurukh",ks:"\u0915\u093e\u0936\u094d\u092e\u093f\u0930\u0940",ku:"K\u00fcrt\u00e7e",ku_Arab:"\u0627\u0644\u0643\u0631\u062f\u064a\u0629",ku_Latn:"K\u00fcrt\u00e7e",kum:"\u043a\u0443\u043c\u044b\u043a\u0441\u043a\u0438\u0439",kut:"Kutenai",kv:"Komi",kw:"kernewek",ky:"K\u0131rg\u0131zca",ky_Arab:"\u0627\u0644\u0642\u064a\u0631\u063a\u0633\u062a\u0627\u0646\u064a\u0629",ky_Cyrl:"\u043a\u0438\u0440\u0433\u0438\u0437\u0441\u043a\u0438\u0439",la:"latino",lad:"\u05dc\u05d3\u05d9\u05e0\u05d5",
lah:"\u0644\u0627\u0647\u0646\u062f\u0627",lam:"Lamba",lb:"luxembourgeois",lez:"\u043b\u0435\u0437\u0433\u0438\u043d\u0441\u043a\u0438\u0439",lg:"Ganda",li:"Limburgs",ln:"lingala",lo:"Lao",lol:"mongo",loz:"Lozi",lt:"lietuvi\u0173",lu:"luba-katanga",lua:"luba-lulua",lui:"Luiseno",lun:"Lunda",luo:"Luo",lus:"Lushai",lv:"latvie\u0161u",mad:"Madura",mag:"Magahi",mai:"Maithili",mak:"Makassar",man:"Mandingo",map:"Austronesian",mas:"Masai",mdf:"\u043c\u043e\u043a\u0448\u0430",mdr:"Mandar",men:"Mende",mg:"malgache",
mga:"Middle Irish",mh:"Marshallese",mi:"Maori",mic:"Micmac",min:"Minangkabau",mis:"Miscellaneous Language",mk:"\u043c\u0430\u043a\u0435\u0434\u043e\u043d\u0441\u043a\u0438",mkh:"Mon-Khmer Language",ml:"\u0d2e\u0d32\u0d2f\u0d3e\u0d33\u0d02",mn:fb,mn_Cyrl:fb,mn_Mong:fb,mnc:"Manchu",mni:"Manipuri",mno:"Manobo Language",mo:"Moldavian",moh:"Mohawk",mos:"mor\u00e9 ; mossi",mr:"\u092e\u0930\u093e\u0920\u0940",ms:"Bahasa Melayu",mt:"Malti",mul:"Multiple Languages",mun:"Munda Language",mus:"Creek",mwl:"Mirandese",
mwr:"Marwari",my:"Burmese",myn:"Mayan Language",myv:"\u044d\u0440\u0437\u044f",na:"Nauru",nah:"Nahuatl",nai:"North American Indian Language",nap:"napoletano",nb:"norsk bokm\u00e5l",nd:"North Ndebele",nds:"Low German",ne:"\u0928\u0947\u092a\u093e\u0932\u0940","new":"Newari",ng:"Ndonga",nia:"Nias",nic:"Niger-Kordofanian Language",niu:"Niuean",nl:"Nederlands",nn:"nynorsk",no:"Norwegian",nog:"\u043d\u043e\u0433\u0430\u0439\u0441\u043a\u0438\u0439",non:"Old Norse",nqo:"N\u2019Ko",nr:"South Ndebele",nso:"Northern Sotho",
nub:"Nubian Language",nv:"Navajo",nwc:"Classical Newari",ny:"nianja; chicheua; cheua",nym:"Nyamwezi",nyn:"Nyankole",nyo:"Nyoro",nzi:"Nzima",oc:"occitan",oj:"Ojibwa",om:"Oromoo",or:"\u0b13\u0b21\u0b3c\u0b3f\u0b06",os:"\u043e\u0441\u0435\u0442\u0438\u043d\u0441\u043a\u0438\u0439",osa:"Osage",ota:"Ottoman Turkish",oto:"Otomian Language",pa:"\u0a2a\u0a70\u0a1c\u0a3e\u0a2c\u0a40",pa_Arab:"\u067e\u0646\u062c\u0627\u0628",pa_Guru:"\u0a2a\u0a70\u0a1c\u0a3e\u0a2c\u0a40",paa:"Papuan Language",pag:"Pangasinan",
pal:"Pahlavi",pam:"Pampanga",pap:"Papiamento",pau:"Palauan",peo:"Old Persian",phi:"Philippine Language",phn:"Phoenician",pi:"\u0e1a\u0e32\u0e25\u0e35",pl:"polski",pon:"Pohnpeian",pra:"Prakrit Language",pro:"Old Proven\u00e7al",ps:"\u067e\u069a\u062a\u0648",pt:"portugu\u00eas",qu:"quechua",raj:"Rajasthani",rap:"Rapanui",rar:"Rarotongan",rm:"R\u00e4toromanisch",rn:"roundi",ro:"rom\u00e2n\u0103",roa:"Romance Language",rom:"Romany",ru:"\u0440\u0443\u0441\u0441\u043a\u0438\u0439",rup:"Aromanian",rw:"rwanda",
sa:"\u0938\u0902\u0938\u094d\u0915\u0943\u0924 \u092d\u093e\u0937\u093e",sad:"Sandawe",sah:"\u044f\u043a\u0443\u0442\u0441\u043a\u0438\u0439",sai:"South American Indian Language",sal:"Salishan Language",sam:"\u05d0\u05e8\u05de\u05d9\u05ea \u05e9\u05d5\u05de\u05e8\u05d5\u05e0\u05d9\u05ea",sas:"Sasak",sat:"Santali",sc:"Sardinian",scn:"siciliano",sco:"Scots",sd:"\u0938\u093f\u0928\u094d\u0927\u0940",sd_Arab:"\u0633\u0646\u062f\u06cc",sd_Deva:"\u0938\u093f\u0928\u094d\u0927\u0940",se:"nordsamiska",sel:"\u0441\u0435\u043b\u044c\u043a\u0443\u043f\u0441\u043a\u0438\u0439",
sem:"Semitic Language",sg:"sangho",sga:"Old Irish",sgn:"Sign Language",sh:"Serbo-Croatian",shn:"Shan",si:"Sinhalese",sid:"Sidamo",sio:"Siouan Language",sit:"Sino-Tibetan Language",sk:"slovensk\u00fd",sl:"sloven\u0161\u010dina",sla:"Slavic Language",sm:"Samoan",sma:"sydsamiska",smi:"Sami Language",smj:"lulesamiska",smn:"Inari Sami",sms:"Skolt Sami",sn:"Shona",snk:"sonink\u00e9",so:"Soomaali",sog:"Sogdien",son:"Songhai",sq:"shqipe",sr:"\u0421\u0440\u043f\u0441\u043a\u0438",sr_Cyrl:"\u0441\u0435\u0440\u0431\u0441\u043a\u0438\u0439",
sr_Latn:"Srpski",srn:"Sranantongo",srr:"s\u00e9r\u00e8re",ss:"Swati",ssa:"Nilo-Saharan Language",st:"Sesotho",su:"Sundan",suk:"Sukuma",sus:"soussou",sux:"Sumerian",sv:"svenska",sw:"Kiswahili",syc:"Classical Syriac",syr:"Syriac",ta:"\u0ba4\u0bae\u0bbf\u0bb4\u0bcd",tai:"Tai Language",te:"\u0c24\u0c46\u0c32\u0c41\u0c17\u0c41",tem:"Timne",ter:"Tereno",tet:"t\u00e9tum",tg:"\u062a\u0627\u062c\u06a9",tg_Arab:"\u062a\u0627\u062c\u06a9",tg_Cyrl:"\u0442\u0430\u0434\u0436\u0438\u043a\u0441\u043a\u0438\u0439",
th:"\u0e44\u0e17\u0e22",ti:"\u1275\u130d\u122d\u129b",tig:"\u1275\u130d\u1228",tiv:"Tiv",tk:"\u062a\u0631\u06a9\u0645\u0646\u06cc",tkl:E,tl:"Tagalog",tlh:"Klingon",tli:"Tlingit",tmh:"tamacheq",tn:"Tswana",to:"Tonga",tog:"Nyasa Tonga",tpi:"Tok Pisin",tr:"T\u00fcrk\u00e7e",ts:"Tsonga",tsi:"Tsimshian",tt:"\u0442\u0430\u0442\u0430\u0440\u0441\u043a\u0438\u0439",tum:"Tumbuka",tup:"Tupi Language",tut:"\u0430\u043b\u0442\u0430\u0439\u0441\u043a\u0438\u0435 (\u0434\u0440\u0443\u0433\u0438\u0435)",tvl:F,tw:"Twi",
ty:"tahitien",tyv:"\u0442\u0443\u0432\u0438\u043d\u0441\u043a\u0438\u0439",udm:"\u0443\u0434\u043c\u0443\u0440\u0442\u0441\u043a\u0438\u0439",ug:"\u0443\u0439\u0433\u0443\u0440\u0441\u043a\u0438\u0439",uga:"Ugaritic",uk:"\u0443\u043a\u0440\u0430\u0457\u043d\u0441\u044c\u043a\u0430",umb:"umbundu",und:"English",ur:"\u0627\u0631\u062f\u0648",uz:"\u040e\u0437\u0431\u0435\u043a",uz_Arab:"\u0627\u06c9\u0632\u0628\u06d0\u06a9",uz_Cyrl:"\u0443\u0437\u0431\u0435\u043a\u0441\u043a\u0438\u0439",uz_Latn:"o'zbekcha",
vai:"Vai",ve:"Venda",vi:"Ti\u1ebfng Vi\u1ec7t",vo:"volapuko",vot:"Votic",wa:"Wallonisch",wak:"Wakashan Language",wal:"Walamo",war:"Waray",was:"Washo",wen:"Sorbian Language",wo:"wolof",wo_Arab:"\u0627\u0644\u0648\u0644\u0648\u0641",wo_Latn:"wolof",xal:"\u043a\u0430\u043b\u043c\u044b\u0446\u043a\u0438\u0439",xh:"Xhosa",yao:"iao",yap:"Yapese",yi:"\u05d9\u05d9\u05d3\u05d9\u05e9",yo:"Yoruba",ypk:"Yupik Language",za:"Zhuang",zap:"Zapotec",zen:"Zenaga",zh:"\u4e2d\u6587",zh_Hans:"\u4e2d\u6587",zh_Hant:"\u4e2d\u6587",
znd:"Zande",zu:"Zulu",zun:"Zuni",zxx:"No linguistic content",zza:"Zaza"}};Z.locale.hg=function(a){a=a.replace(/-/g,"_");Z.locale.M=a};Z.locale.Da=function(){Z.locale.M||(Z.locale.M="en");return Z.locale.M};Z.locale.H={Gd:"DateTimeConstants",Od:"NumberFormatConstants",ic:"TimeZoneConstants",Yb:ka,jc:"TimeZoneSelectedIds",lc:"TimeZoneSelectedShortNames",kc:"TimeZoneSelectedLongNames",hc:"TimeZoneAllLongNames"};Z.locale.Ca=function(a){return(a=a.match(/^\w{2,3}([-_]|$)/))?a[0].replace(/[_-]/g,""):""};
Z.locale.mb=function(a){return(a=a.match(/[-_]([a-zA-Z]{2}|\d{3})([-_]|$)/))?a[0].replace(/[_-]/g,""):""};Z.locale.Ze=function(a){a=a.split(/[-_]/g);return 1<a.length&&a[1].match(/^[a-zA-Z]{4}$/)?a[1]:""};Z.locale.$e=function(a){return(a=a.match(/[-_]([a-z]{2,})/))?a[1]:""};Z.locale.Ve=function(a){var c=Z.locale.Ca(a)+"_"+Z.locale.mb(a);return c in Z.locale.J.COUNTRY?Z.locale.J.COUNTRY[c]:a};Z.locale.Re=function(a,c){var d=Z.locale.mb(a);c=Z.locale.Lc(d,c);return c==d?a:c};
Z.locale.Lc=function(a,c){c||(c=Z.locale.ob());return a in c.COUNTRY?c.COUNTRY[a]:a};Z.locale.We=function(a){if(a in Z.locale.J.LANGUAGE)return Z.locale.J.LANGUAGE[a];var c=Z.locale.Ca(a);return c in Z.locale.J.LANGUAGE?Z.locale.J.LANGUAGE[c]:a};Z.locale.Se=function(a,c){c||(c=Z.locale.ob());if(a in c.LANGUAGE)return c.LANGUAGE[a];var d=Z.locale.Ca(a);return d in c.LANGUAGE?c.LANGUAGE[d]:a};Z.locale.K=function(a,c,d){Z.locale.m[c]||(Z.locale.m[c]={});Z.locale.m[c][d]=a;Z.locale.M||(Z.locale.M=d)};
Z.locale.uf=function(a,c){return a in Z.locale.m&&c in Z.locale.m[a]};Z.locale.m={};Z.locale.Sf=function(a,c){Z.locale.K(a,Z.locale.H.ic,c)};Z.locale.Qf=function(a,c){Z.locale.K(a,Z.locale.H.Yb,c)};Z.locale.Tf=function(a,c){Z.locale.K(a,Z.locale.H.jc,c)};Z.locale.Vf=function(a,c){Z.locale.K(a,Z.locale.H.lc,c)};Z.locale.Uf=function(a,c){Z.locale.K(a,Z.locale.H.kc,c)};Z.locale.Rf=function(a,c){Z.locale.K(a,Z.locale.H.hc,c)};Z.locale.ob=function(){var a=Z.locale.Da(),a=a?a:Z.locale.Da();if(ka in Z.locale.m)return Z.locale.m.LocaleNameConstants[a]};
Z.locale.Ye=function(a,c){c=c?c:Z.locale.Da();if(a in Z.locale.m){if(c in Z.locale.m[a])return Z.locale.m[a][c];c=c.split("_");return 1<c.length&&c[0]in Z.locale.m[a]?Z.locale.m[a][c[0]]:Z.locale.m[a].en}};var google={a:{}};google.a.c={};
google.a.c.languages={af:!0,am:!0,az:!0,ar:!0,arb:"ar",bg:!0,bn:!0,ca:!0,cs:!0,cmn:"zh",da:!0,de:!0,el:!0,en:!0,en_gb:!0,es:!0,es_419:!0,et:!0,eu:!0,fa:!0,fi:!0,fil:!0,fr:!0,fr_ca:!0,gl:!0,ka:!0,gu:!0,he:"iw",hi:!0,hr:!0,hu:!0,hy:!0,id:!0,"in":"id",is:!0,it:!0,iw:!0,ja:!0,ji:"yi",jv:!1,jw:"jv",km:!0,kn:!0,ko:!0,lo:!0,lt:!0,lv:!0,ml:!0,mn:!0,mo:"ro",mr:!0,ms:!0,nb:"no",ne:!0,nl:!0,no:!0,pl:!0,pt:"pt_br",pt_br:!0,pt_pt:!0,ro:!0,ru:!0,si:!0,sk:!0,sl:!0,sr:!0,sv:!0,sw:!0,swh:"sw",ta:!0,te:!0,th:!0,tl:"fil",
tr:!0,uk:!0,ur:!0,vi:!0,yi:!1,zh:"zh_cn",zh_cn:!0,zh_hk:!0,zh_tw:!0,zsm:"ms",zu:!0};google.a.c.P={};google.a.c.I="unknown";google.a.c.log=Y();google.a.c.error=Y();google.a.c.Y=!1;google.a.c.window=window;google.a.c.yd={1:"1.0","1.0":"current","1.1":"upcoming",current:"45",upcoming:"45",41:"top",42:"top",43:"top",44:"top"};
google.a.c.Eb={gstatic:{prefix:"https://www.gstatic.com/charts",debug:"{prefix}/debug/{version}/jsapi_debug_{package}_module.js",compiled:"{prefix}/{version}/js/jsapi_compiled_{package}_module.js",i18n:"{prefix}/{version}/i18n/jsapi_compiled_i18n_{package}_module__{language}.js",css_prefix:"{prefix}/{version}/css",css:Za,css_debug:Za,third_party:ab,third_party_gen:ab}};google.a.c.i=google.a.c.Eb.gstatic;
google.a.c.Ec={format:[],"default":["format"],ui:["default"],ui_base:["default"],annotatedtimeline:[Q],annotationchart:[Q,"controls",L,"table"],areachart:[Q,J],bar:[Q,M,S],barchart:[Q,J],browserchart:[Q],calendar:[Q],charteditor:[Q,L,O,Wa,"gauge","motionchart","orgchart","table"],charteditor_base:[R,L,O,Wa,"gauge","motionchart","orgchart","table_base"],columnchart:[Q,J],controls:[Q],controls_base:[R],corechart:[Q],gantt:[Q,M],gauge:[Q],geochart:[Q],geomap:[Q],geomap_base:[R],helloworld:[Q],imageareachart:[Q,
O],imagebarchart:[Q,O],imagelinechart:[Q,O],imagechart:[Q],imagepiechart:[Q,O],imagesparkline:[Q,O],intensitymap:[Q],line:[Q,M,S],linechart:[Q,J],map:[Q],motionchart:[Q],orgchart:[Q],overtimecharts:[Q,L],piechart:[Q,J],sankey:["d3","d3.sankey",Q],scatter:[Q,M,S],scatterchart:[Q,J],table:[Q],table_base:[R],timeline:[Q,M],treemap:[Q],wordtree:[Q]};google.a.c.td={d3:"d3/d3.js","d3.sankey":"d3/d3.sankey.js",webfontloader:"webfontloader/webfont.js"};google.a.c.Fb={dygraph:"dygraphs/dygraph-tickers-combined.js"};
google.a.c.Bc={annotatedtimeline:"/annotatedtimeline/annotatedtimeline.css",annotationchart:"annotationchart/annotationchart.css",charteditor:"charteditor/charteditor.css",charteditor_base:"charteditor/charteditor_base.css",controls:"controls/controls.css",imagesparkline:"imagesparkline/imagesparkline.css",intensitymap:"intensitymap/intensitymap.css",orgchart:"orgchart/orgchart.css",table:"table/table.css",table_base:"table/table_base.css",ui:["util/util.css","core/tooltip.css"],ui_base:"util/util_base.css"};
google.a.c.ra=function(a,c){c=c||{};for(var d=[],e=0;e<a.length;e++){var f=a[e];if(!c[f]){c[f]=!0;var g=google.a.c.Ec[f]||[];0<g.length&&(d=d.concat(google.a.c.ra(g,c)));d.push(f)}}return d};google.a.c.Kc=function(a){for(var c={},d=[],e=0;e<a.length;e++){var f=google.a.c.Bc[a[e]];Z.isArray(f)||(f=[f]);for(var g=0;g<f.length;g++){var h=f[g];h&&!c[h]&&(c[h]=!0,d.push(h))}}return d};
google.a.c.md=function(a,c){if(c)if("undefined"===typeof a.onload){var d=!1;a.onreadystatechange=function(){d||(a.readyState&&a.readyState!==K?google.a.c.window.setTimeout(a.onreadystatechange,0):(d=!0,delete a.onreadystatechange,google.a.c.window.setTimeout(c,0)))}}else a.onload=c};
google.a.c.zb=function(a,c,d){google.a.c.log("loadScript: "+a);var e=c.createElement("script");e.type=Ya;e.language="javascript";e.async=!1;e.defer=!1;c=c.body||c.head||c.getElementsByTagName("HEAD").item(0)||c.documentElement;c.insertBefore(e,c.lastChild);d&&google.a.c.md(e,d);e.src=a;google.a.c.log("end loadScript: "+a)};
google.a.c.Zc=function(a,c){a=google.a.c.ra(a);for(var d=[],e=0;e<a.length;e++){var f=a[e];google.a.c.P[f]||d.push(f)}a=d;if(0===a.length)c();else{google.a.c.log("Load packages + dependencies - previous: "+a);var g=google.a.c.i.prefix,h=google.a.c.i.debug,l=google.a.c.i.compiled,m=google.a.c.i.i18n,n=google.a.c.I,T=google.a.c.Ma;"en"===T&&(T=null);var D={},ya=a.length,ob=function(){for(var d=[],e=0;e<a.length;e++)d.push(D[a[e]]);eval("0,(function(){"+d.join("")+"})")();google.a.c.window.setTimeout(c,
0)};google.a.c.ed=function(a,c){google.a.c.log("callback after loading "+a);D[a]=c;google.a.c.P[a]=!0;ya--;0===ya&&ob()};var nb=function(c){var d=google.a.c.Fc,e=a[c++];if(e){var f=e,D=google.a.c.td[e];D?(f=D,e===S&&(d=window.document),e=google.a.c.i.third_party):google.a.c.Fb[e]?(f=google.a.c.Fb[e],e=google.a.c.i.third_party_gen):e=google.a.c.Y?h:T?m:l;f=e.replace($a,g).replace(bb,n).replace("{language}",T).replace("{package}",f);google.a.c.zb(f,d);nb(c)}};nb(0)}};
google.a.c.V=function(a){function c(){g=!0;for(var a=e.length,c=0;c<a;c++)e[c]()}function d(){h=!0;for(var a=f.length,c=0;c<a;c++)f[c]()}var e=[],f=[],g=!1,h=!1;google.a.c.V.count||(google.a.c.V.count=0);var l="load-css-"+google.a.c.V.count++,m={done:function(a){e.push(a);g&&a();return m},Aa:function(a){f.push(a);h&&a();return m}},n=document.createElement("link");n.setAttribute("id",l);n.setAttribute("rel","stylesheet");n.setAttribute("type","text/css");"undefined"!==typeof n.addEventListener?(n.addEventListener("load",
c,!1),n.addEventListener("error",d,!1)):"undefined"!==typeof n.attachEvent&&n.attachEvent("onload",function(){var a,e=document.styleSheets.length;try{for(;e--;)if(a=document.styleSheets[e],a.id===l){c();return}}catch(ya){}g||d()});document.getElementsByTagName("head")[0].appendChild(n);n.setAttribute("href",a);return m};google.a.c.Vc=function(a,c){google.a.c.log("loadCSSFile: "+a);google.a.c.V(a).done(c).Aa(function(){google.a.c.error("loading css failed: "+a)})};
google.a.c.Wc=function(a,c){a=google.a.c.ra(a);var d=google.a.c.Kc(a);if(null===d||0===d.length)c();else{google.a.c.log("Loading css files: "+d.join(", "));var e=google.a.c.i.prefix,f=google.a.c.i.css;google.a.c.Y&&(f=google.a.c.i.css_debug||f);var g=google.a.c.I,h=function(a){var m=d[a],l;l=a<d.length-1?function(){h(a+1)}:c;google.a.c.P[m]?(google.a.c.log("Already loaded "+m),google.a.c.window.setTimeout(l,0)):(google.a.c.P[m]=!0,m=f.replace("{css_prefix}",google.a.c.i.css_prefix).replace($a,e).replace(bb,
g).replace("{cssFile}",m),google.a.c.Vc(m,l))};h(0)}};google.a.c.Cb=function(a){for(var c=a.replace(/-/g,"_").toLowerCase();Z.h(c);)a=c,c=google.a.c.languages[c],c===a&&(c=!1);c||(a.match(/_[^_]+$/)?(a=a.replace(/_[^_]+$/,""),a=google.a.c.Cb(a)):a="en");return a};
google.a.c.jd=function(a,c){c.log&&(google.a.c.log=c.log);c.error&&(google.a.c.error=c.error);var d=c.debug,e=c.language||"",e=google.a.c.Cb(e);a||(a=c.version||"unknown");if(google.a.c.I&&google.a.c.I!==a||google.a.c.Ma&&google.a.c.Ma!==e||google.a.c.Y!==d)google.a.c.P={};google.a.c.I=a;google.a.c.Ma=e;google.a.c.Y=d};google.a.c.$=!0;google.a.c.R=!1;
google.a.c.load=function(a,c){if(google.a.c.R)google.a.c.T(function(){google.a.c.load(a,c)});else{var d;d=a.match(/^(testing\/)?(.*)/);var e=d[1]||"";for(a=d[2];;){d=google.a.c.yd[a];if(null==d||"top"===d)break;a=d}google.a.c.i=google.a.c.Eb.gstatic;a=e+a;google.a.c.R=!0;var f=function(){google.a.c.R=!1;google.a.c.Na()};if(null==d)e=function(){var d=Z.Ea("google.charts.versionSpecific.load"),e=Z.Ea("google.charts.versionSpecific.setOnLoadCallback");d(a,c);e(f)},google.a.c.$?(google.a.c.$=!1,google.a.c.zb(google.a.c.i.prefix+
"/"+a+"/loader.js",window.document,e)):e();else{if(!google.a.c.$)throw Error("google.charts.load() cannot be called more than once with version 44 or earlier.");google.a.c.$=!1;google.a.c.jd(a,c);google.a.c.log("google.charts.load version "+a);window.google=window.google||{};google.visualization=google.visualization||{};google.visualization.ModulePath=google.a.c.i.prefix;google.visualization.CssPath=google.a.c.i.css_prefix.replace($a,google.a.c.i.prefix).replace(bb,google.a.c.I);google.a.c.window=
window;google.a.c.Fc=document;var g=c.packages;g&&0!==g.length||(g=["default"]);google.a.c.T(c.callback);google.a.c.Wc(g,function(){google.a.c.Zc(g,f)})}}};google.a.c.od=function(a){if(window.addEventListener)window.addEventListener("load",a,!1);else if(window.attachEvent)window.attachEvent("onload",a);else{var c=window.onload;window.onload=function(d){c&&c(d);a()}}};google.a.c.Jb=document&&document.readyState===K;google.a.c.od(function(){google.a.c.Jb=!0;google.a.c.Na()});
google.a.c.Na=function(){!google.a.c.R&&google.a.c.Jb&&google.a.c.xc()};google.a.c.Ba=[];google.a.c.T=function(a){a&&google.a.c.Ba.push(a);google.a.c.R||google.a.c.Na()};google.a.c.xc=function(){var a=google.a.c.Ba;for(google.a.c.Ba=[];0<a.length;)a.shift()()};google.a.c.Qa=function(a,c){google.a.c.ed(a,c)};if(Z.Ea(Xa))throw Error("Google Charts loader.js can only be loaded once.");google.a.load=function(){var a=0;"visualization"===arguments[a]&&a++;var c="current";Z.h(arguments[a])&&(c=arguments[a],a++);var d={};arguments.length>a&&(d=arguments[a]);google.a.c.load(c,d)};google.a.T=function(a){google.a.c.T(a)};google.a.Qa=function(a,c){google.a.c.Qa(a,c)};Z.ya(Xa,google.a.load);Z.ya("google.charts.setOnLoadCallback",google.a.T);Z.ya("google.charts.packageLoadedCallback",google.a.Qa); })();
/*!
 * The Final Countdown for jQuery v2.0.4 (http://hilios.github.io/jQuery.countdown/)
 * Copyright (c) 2014 Edson Hilios
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
 * the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 * FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 * IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 * CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
(function(factory) {
    "use strict";
    if (typeof define === "function" && define.amd) {
        define([ "jquery" ], factory);
    } else {
        factory(jQuery);
    }
})(function($) {
    "use strict";
    var PRECISION = 100;
    var instances = [], matchers = [];
    matchers.push(/^[0-9]*$/.source);
    matchers.push(/([0-9]{1,2}\/){2}[0-9]{4}( [0-9]{1,2}(:[0-9]{2}){2})?/.source);
    matchers.push(/[0-9]{4}([\/\-][0-9]{1,2}){2}( [0-9]{1,2}(:[0-9]{2}){2})?/.source);
    matchers = new RegExp(matchers.join("|"));
    function parseDateString(dateString) {
        if (dateString instanceof Date) {
            return dateString;
        }
        if (String(dateString).match(matchers)) {
            if (String(dateString).match(/^[0-9]*$/)) {
                dateString = Number(dateString);
            }
            if (String(dateString).match(/\-/)) {
                dateString = String(dateString).replace(/\-/g, "/");
            }
            return new Date(dateString);
        } else {
            throw new Error("Couldn't cast `" + dateString + "` to a date object.");
        }
    }
    var DIRECTIVE_KEY_MAP = {
        Y: "years",
        m: "months",
        w: "weeks",
        d: "days",
        D: "totalDays",
        H: "hours",
        M: "minutes",
        S: "seconds"
    };
    function strftime(offsetObject) {
        return function(format) {
            var directives = format.match(/%(-|!)?[A-Z]{1}(:[^;]+;)?/gi);
            if (directives) {
                for (var i = 0, len = directives.length; i < len; ++i) {
                    var directive = directives[i].match(/%(-|!)?([a-zA-Z]{1})(:[^;]+;)?/), regexp = new RegExp(directive[0]), modifier = directive[1] || "", plural = directive[3] || "", value = null;
                    directive = directive[2];
                    if (DIRECTIVE_KEY_MAP.hasOwnProperty(directive)) {
                        value = DIRECTIVE_KEY_MAP[directive];
                        value = Number(offsetObject[value]);
                    }
                    if (value !== null) {
                        if (modifier === "!") {
                            value = pluralize(plural, value);
                        }
                        if (modifier === "") {
                            if (value < 10) {
                                value = "0" + value.toString();
                            }
                        }
                        format = format.replace(regexp, value.toString());
                    }
                }
            }
            format = format.replace(/%%/, "%");
            return format;
        };
    }
    function pluralize(format, count) {
        var plural = "s", singular = "";
        if (format) {
            format = format.replace(/(:|;|\s)/gi, "").split(/\,/);
            if (format.length === 1) {
                plural = format[0];
            } else {
                singular = format[0];
                plural = format[1];
            }
        }
        if (Math.abs(count) === 1) {
            return singular;
        } else {
            return plural;
        }
    }
    var Countdown = function(el, finalDate, callback) {
        this.el = el;
        this.$el = $(el);
        this.interval = null;
        this.offset = {};
        this.instanceNumber = instances.length;
        instances.push(this);
        this.$el.data("countdown-instance", this.instanceNumber);
        if (callback) {
            this.$el.on("update.countdown", callback);
            this.$el.on("stoped.countdown", callback);
            this.$el.on("finish.countdown", callback);
        }
        this.setFinalDate(finalDate);
        this.start();
    };
    $.extend(Countdown.prototype, {
        start: function() {
            if (this.interval !== null) {
                clearInterval(this.interval);
            }
            var self = this;
            this.update();
            this.interval = setInterval(function() {
                self.update.call(self);
            }, PRECISION);
        },
        stop: function() {
            clearInterval(this.interval);
            this.interval = null;
            this.dispatchEvent("stoped");
        },
        pause: function() {
            this.stop.call(this);
        },
        resume: function() {
            this.start.call(this);
        },
        remove: function() {
            this.stop();
            instances[this.instanceNumber] = null;
            delete this.$el.data().countdownInstance;
        },
        setFinalDate: function(value) {
            this.finalDate = parseDateString(value);
        },
        update: function() {
            if (this.$el.closest("html").length === 0) {
                this.remove();
                return;
            }
            this.totalSecsLeft = this.finalDate.getTime() - new Date().getTime();
            this.totalSecsLeft = Math.ceil(this.totalSecsLeft / 1e3);
            this.totalSecsLeft = this.totalSecsLeft < 0 ? 0 : this.totalSecsLeft;
            this.offset = {
                seconds: this.totalSecsLeft % 60,
                minutes: Math.floor(this.totalSecsLeft / 60) % 60,
                hours: Math.floor(this.totalSecsLeft / 60 / 60) % 24,
                days: Math.floor(this.totalSecsLeft / 60 / 60 / 24) % 7,
                totalDays: Math.floor(this.totalSecsLeft / 60 / 60 / 24),
                weeks: Math.floor(this.totalSecsLeft / 60 / 60 / 24 / 7),
                months: Math.floor(this.totalSecsLeft / 60 / 60 / 24 / 30),
                years: Math.floor(this.totalSecsLeft / 60 / 60 / 24 / 365)
            };
            if (this.totalSecsLeft === 0) {
                this.stop();
                this.dispatchEvent("finish");
            } else {
                this.dispatchEvent("update");
            }
        },
        dispatchEvent: function(eventName) {
            var event = $.Event(eventName + ".countdown");
            event.finalDate = this.finalDate;
            event.offset = $.extend({}, this.offset);
            event.strftime = strftime(this.offset);
            this.$el.trigger(event);
        }
    });
    $.fn.countdown = function() {
        var argumentsArray = Array.prototype.slice.call(arguments, 0);
        return this.each(function() {
            var instanceNumber = $(this).data("countdown-instance");
            if (instanceNumber !== undefined) {
                var instance = instances[instanceNumber], method = argumentsArray[0];
                if (Countdown.prototype.hasOwnProperty(method)) {
                    instance[method].apply(instance, argumentsArray.slice(1));
                } else if (String(method).match(/^[$A-Z_][0-9A-Z_$]*$/i) === null) {
                    instance.setFinalDate.call(instance, method);
                    instance.start();
                } else {
                    $.error("Method %s does not exist on jQuery.countdown".replace(/\%s/gi, method));
                }
            } else {
                new Countdown(this, argumentsArray[0], argumentsArray[1]);
            }
        });
    };
});
/*!
 * Bootstrap-select v1.12.4 (http://silviomoreto.github.io/bootstrap-select)
 *
 * Copyright 2013-2017 bootstrap-select
 * Licensed under MIT (https://github.com/silviomoreto/bootstrap-select/blob/master/LICENSE)
 */

(function (root, factory) {
  if (typeof define === 'function' && define.amd) {
    // AMD. Register as an anonymous module unless amdModuleId is set
    define(["jquery"], function (a0) {
      return (factory(a0));
    });
  } else if (typeof module === 'object' && module.exports) {
    // Node. Does not work with strict CommonJS, but
    // only CommonJS-like environments that support module.exports,
    // like Node.
    module.exports = factory(require("jquery"));
  } else {
    factory(root["jQuery"]);
  }
}(this, function (jQuery) {

(function ($) {
  'use strict';

  //<editor-fold desc="Shims">
  if (!String.prototype.includes) {
    (function () {
      'use strict'; // needed to support `apply`/`call` with `undefined`/`null`
      var toString = {}.toString;
      var defineProperty = (function () {
        // IE 8 only supports `Object.defineProperty` on DOM elements
        try {
          var object = {};
          var $defineProperty = Object.defineProperty;
          var result = $defineProperty(object, object, object) && $defineProperty;
        } catch (error) {
        }
        return result;
      }());
      var indexOf = ''.indexOf;
      var includes = function (search) {
        if (this == null) {
          throw new TypeError();
        }
        var string = String(this);
        if (search && toString.call(search) == '[object RegExp]') {
          throw new TypeError();
        }
        var stringLength = string.length;
        var searchString = String(search);
        var searchLength = searchString.length;
        var position = arguments.length > 1 ? arguments[1] : undefined;
        // `ToInteger`
        var pos = position ? Number(position) : 0;
        if (pos != pos) { // better `isNaN`
          pos = 0;
        }
        var start = Math.min(Math.max(pos, 0), stringLength);
        // Avoid the `indexOf` call if no match is possible
        if (searchLength + start > stringLength) {
          return false;
        }
        return indexOf.call(string, searchString, pos) != -1;
      };
      if (defineProperty) {
        defineProperty(String.prototype, 'includes', {
          'value': includes,
          'configurable': true,
          'writable': true
        });
      } else {
        String.prototype.includes = includes;
      }
    }());
  }

  if (!String.prototype.startsWith) {
    (function () {
      'use strict'; // needed to support `apply`/`call` with `undefined`/`null`
      var defineProperty = (function () {
        // IE 8 only supports `Object.defineProperty` on DOM elements
        try {
          var object = {};
          var $defineProperty = Object.defineProperty;
          var result = $defineProperty(object, object, object) && $defineProperty;
        } catch (error) {
        }
        return result;
      }());
      var toString = {}.toString;
      var startsWith = function (search) {
        if (this == null) {
          throw new TypeError();
        }
        var string = String(this);
        if (search && toString.call(search) == '[object RegExp]') {
          throw new TypeError();
        }
        var stringLength = string.length;
        var searchString = String(search);
        var searchLength = searchString.length;
        var position = arguments.length > 1 ? arguments[1] : undefined;
        // `ToInteger`
        var pos = position ? Number(position) : 0;
        if (pos != pos) { // better `isNaN`
          pos = 0;
        }
        var start = Math.min(Math.max(pos, 0), stringLength);
        // Avoid the `indexOf` call if no match is possible
        if (searchLength + start > stringLength) {
          return false;
        }
        var index = -1;
        while (++index < searchLength) {
          if (string.charCodeAt(start + index) != searchString.charCodeAt(index)) {
            return false;
          }
        }
        return true;
      };
      if (defineProperty) {
        defineProperty(String.prototype, 'startsWith', {
          'value': startsWith,
          'configurable': true,
          'writable': true
        });
      } else {
        String.prototype.startsWith = startsWith;
      }
    }());
  }

  if (!Object.keys) {
    Object.keys = function (
      o, // object
      k, // key
      r  // result array
      ){
      // initialize object and result
      r=[];
      // iterate over object keys
      for (k in o)
          // fill result array with non-prototypical keys
        r.hasOwnProperty.call(o, k) && r.push(k);
      // return result
      return r;
    };
  }

  // set data-selected on select element if the value has been programmatically selected
  // prior to initialization of bootstrap-select
  // * consider removing or replacing an alternative method *
  var valHooks = {
    useDefault: false,
    _set: $.valHooks.select.set
  };

  $.valHooks.select.set = function(elem, value) {
    if (value && !valHooks.useDefault) $(elem).data('selected', true);

    return valHooks._set.apply(this, arguments);
  };

  var changed_arguments = null;

  var EventIsSupported = (function() {
    try {
      new Event('change');
      return true;
    } catch (e) {
      return false;
    }
  })();

  $.fn.triggerNative = function (eventName) {
    var el = this[0],
        event;

    if (el.dispatchEvent) { // for modern browsers & IE9+
      if (EventIsSupported) {
        // For modern browsers
        event = new Event(eventName, {
          bubbles: true
        });
      } else {
        // For IE since it doesn't support Event constructor
        event = document.createEvent('Event');
        event.initEvent(eventName, true, false);
      }

      el.dispatchEvent(event);
    } else if (el.fireEvent) { // for IE8
      event = document.createEventObject();
      event.eventType = eventName;
      el.fireEvent('on' + eventName, event);
    } else {
      // fall back to jQuery.trigger
      this.trigger(eventName);
    }
  };
  //</editor-fold>

  // Case insensitive contains search
  $.expr.pseudos.icontains = function (obj, index, meta) {
    var $obj = $(obj).find('a');
    var haystack = ($obj.data('tokens') || $obj.text()).toString().toUpperCase();
    return haystack.includes(meta[3].toUpperCase());
  };

  // Case insensitive begins search
  $.expr.pseudos.ibegins = function (obj, index, meta) {
    var $obj = $(obj).find('a');
    var haystack = ($obj.data('tokens') || $obj.text()).toString().toUpperCase();
    return haystack.startsWith(meta[3].toUpperCase());
  };

  // Case and accent insensitive contains search
  $.expr.pseudos.aicontains = function (obj, index, meta) {
    var $obj = $(obj).find('a');
    var haystack = ($obj.data('tokens') || $obj.data('normalizedText') || $obj.text()).toString().toUpperCase();
    return haystack.includes(meta[3].toUpperCase());
  };

  // Case and accent insensitive begins search
  $.expr.pseudos.aibegins = function (obj, index, meta) {
    var $obj = $(obj).find('a');
    var haystack = ($obj.data('tokens') || $obj.data('normalizedText') || $obj.text()).toString().toUpperCase();
    return haystack.startsWith(meta[3].toUpperCase());
  };

  /**
   * Remove all diatrics from the given text.
   * @access private
   * @param {String} text
   * @returns {String}
   */
  function normalizeToBase(text) {
    var rExps = [
      {re: /[\xC0-\xC6]/g, ch: "A"},
      {re: /[\xE0-\xE6]/g, ch: "a"},
      {re: /[\xC8-\xCB]/g, ch: "E"},
      {re: /[\xE8-\xEB]/g, ch: "e"},
      {re: /[\xCC-\xCF]/g, ch: "I"},
      {re: /[\xEC-\xEF]/g, ch: "i"},
      {re: /[\xD2-\xD6]/g, ch: "O"},
      {re: /[\xF2-\xF6]/g, ch: "o"},
      {re: /[\xD9-\xDC]/g, ch: "U"},
      {re: /[\xF9-\xFC]/g, ch: "u"},
      {re: /[\xC7-\xE7]/g, ch: "c"},
      {re: /[\xD1]/g, ch: "N"},
      {re: /[\xF1]/g, ch: "n"}
    ];
    $.each(rExps, function () {
      text = text ? text.replace(this.re, this.ch) : '';
    });
    return text;
  }


  // List of HTML entities for escaping.
  var escapeMap = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#x27;',
    '`': '&#x60;'
  };
  
  var unescapeMap = {
    '&amp;': '&',
    '&lt;': '<',
    '&gt;': '>',
    '&quot;': '"',
    '&#x27;': "'",
    '&#x60;': '`'
  };

  // Functions for escaping and unescaping strings to/from HTML interpolation.
  var createEscaper = function(map) {
    var escaper = function(match) {
      return map[match];
    };
    // Regexes for identifying a key that needs to be escaped.
    var source = '(?:' + Object.keys(map).join('|') + ')';
    var testRegexp = RegExp(source);
    var replaceRegexp = RegExp(source, 'g');
    return function(string) {
      string = string == null ? '' : '' + string;
      return testRegexp.test(string) ? string.replace(replaceRegexp, escaper) : string;
    };
  };

  var htmlEscape = createEscaper(escapeMap);
  var htmlUnescape = createEscaper(unescapeMap);

  var Selectpicker = function (element, options) {
    // bootstrap-select has been initialized - revert valHooks.select.set back to its original function
    if (!valHooks.useDefault) {
      $.valHooks.select.set = valHooks._set;
      valHooks.useDefault = true;
    }

    this.$element = $(element);
    this.$newElement = null;
    this.$button = null;
    this.$menu = null;
    this.$lis = null;
    this.options = options;

    // If we have no title yet, try to pull it from the html title attribute (jQuery doesnt' pick it up as it's not a
    // data-attribute)
    if (this.options.title === null) {
      this.options.title = this.$element.attr('title');
    }

    // Format window padding
    var winPad = this.options.windowPadding;
    if (typeof winPad === 'number') {
      this.options.windowPadding = [winPad, winPad, winPad, winPad];
    }

    //Expose public methods
    this.val = Selectpicker.prototype.val;
    this.render = Selectpicker.prototype.render;
    this.refresh = Selectpicker.prototype.refresh;
    this.setStyle = Selectpicker.prototype.setStyle;
    this.selectAll = Selectpicker.prototype.selectAll;
    this.deselectAll = Selectpicker.prototype.deselectAll;
    this.destroy = Selectpicker.prototype.destroy;
    this.remove = Selectpicker.prototype.remove;
    this.show = Selectpicker.prototype.show;
    this.hide = Selectpicker.prototype.hide;

    this.init();
  };

  Selectpicker.VERSION = '1.12.4';

  // part of this is duplicated in i18n/defaults-en_US.js. Make sure to update both.
  Selectpicker.DEFAULTS = {
    noneSelectedText: 'Nothing selected',
    noneResultsText: 'No results matched {0}',
    countSelectedText: function (numSelected, numTotal) {
      return (numSelected == 1) ? "{0} item selected" : "{0} items selected";
    },
    maxOptionsText: function (numAll, numGroup) {
      return [
        (numAll == 1) ? 'Limit reached ({n} item max)' : 'Limit reached ({n} items max)',
        (numGroup == 1) ? 'Group limit reached ({n} item max)' : 'Group limit reached ({n} items max)'
      ];
    },
    selectAllText: 'Select All',
    deselectAllText: 'Deselect All',
    doneButton: false,
    doneButtonText: 'Close',
    multipleSeparator: ', ',
    styleBase: 'btn',
    style: 'btn-default',
    size: 'auto',
    title: null,
    selectedTextFormat: 'values',
    width: false,
    container: false,
    hideDisabled: false,
    showSubtext: false,
    showIcon: true,
    showContent: true,
    dropupAuto: true,
    header: false,
    liveSearch: false,
    liveSearchPlaceholder: null,
    liveSearchNormalize: false,
    liveSearchStyle: 'contains',
    actionsBox: false,
    iconBase: 'glyphicon',
    tickIcon: 'glyphicon-ok',
    showTick: false,
    template: {
      caret: '<span class="caret"></span>'
    },
    maxOptions: false,
    mobile: false,
    selectOnTab: false,
    dropdownAlignRight: false,
    windowPadding: 0
  };

  Selectpicker.prototype = {

    constructor: Selectpicker,

    init: function () {
      var that = this,
          id = this.$element.attr('id');

      this.$element.addClass('bs-select-hidden');

      // store originalIndex (key) and newIndex (value) in this.liObj for fast accessibility
      // allows us to do this.$lis.eq(that.liObj[index]) instead of this.$lis.filter('[data-original-index="' + index + '"]')
      this.liObj = {};
      this.multiple = this.$element.prop('multiple');
      this.autofocus = this.$element.prop('autofocus');
      this.$newElement = this.createView();
      this.$element
        .after(this.$newElement)
        .appendTo(this.$newElement);
      this.$button = this.$newElement.children('button');
      this.$menu = this.$newElement.children('.dropdown-menu');
      this.$menuInner = this.$menu.children('.inner');
      this.$searchbox = this.$menu.find('input');

      this.$element.removeClass('bs-select-hidden');

      if (this.options.dropdownAlignRight === true) this.$menu.addClass('dropdown-menu-right');

      if (typeof id !== 'undefined') {
        this.$button.attr('data-id', id);
        $('label[for="' + id + '"]').click(function (e) {
          e.preventDefault();
          that.$button.focus();
        });
      }

      this.checkDisabled();
      this.clickListener();
      if (this.options.liveSearch) this.liveSearchListener();
      this.render();
      this.setStyle();
      this.setWidth();
      if (this.options.container) this.selectPosition();
      this.$menu.data('this', this);
      this.$newElement.data('this', this);
      if (this.options.mobile) this.mobile();

      this.$newElement.on({
        'hide.bs.dropdown': function (e) {
          that.$menuInner.attr('aria-expanded', false);
          that.$element.trigger('hide.bs.select', e);
        },
        'hidden.bs.dropdown': function (e) {
          that.$element.trigger('hidden.bs.select', e);
        },
        'show.bs.dropdown': function (e) {
          that.$menuInner.attr('aria-expanded', true);
          that.$element.trigger('show.bs.select', e);
        },
        'shown.bs.dropdown': function (e) {
          that.$element.trigger('shown.bs.select', e);
        }
      });

      if (that.$element[0].hasAttribute('required')) {
        this.$element.on('invalid', function () {
          that.$button.addClass('bs-invalid');

          that.$element.on({
            'focus.bs.select': function () {
              that.$button.focus();
              that.$element.off('focus.bs.select');
            },
            'shown.bs.select': function () {
              that.$element
                .val(that.$element.val()) // set the value to hide the validation message in Chrome when menu is opened
                .off('shown.bs.select');
            },
            'rendered.bs.select': function () {
              // if select is no longer invalid, remove the bs-invalid class
              if (this.validity.valid) that.$button.removeClass('bs-invalid');
              that.$element.off('rendered.bs.select');
            }
          });

          that.$button.on('blur.bs.select', function() {
            that.$element.focus().blur();
            that.$button.off('blur.bs.select');
          });
        });
      }

      setTimeout(function () {
        that.$element.trigger('loaded.bs.select');
      });
    },

    createDropdown: function () {
      // Options
      // If we are multiple or showTick option is set, then add the show-tick class
      var showTick = (this.multiple || this.options.showTick) ? ' show-tick' : '',
          inputGroup = this.$element.parent().hasClass('input-group') ? ' input-group-btn' : '',
          autofocus = this.autofocus ? ' autofocus' : '';
      // Elements
      var header = this.options.header ? '<div class="popover-title"><button type="button" class="close" aria-hidden="true">&times;</button>' + this.options.header + '</div>' : '';
      var searchbox = this.options.liveSearch ?
      '<div class="bs-searchbox">' +
      '<input type="text" class="form-control" autocomplete="off"' +
      (null === this.options.liveSearchPlaceholder ? '' : ' placeholder="' + htmlEscape(this.options.liveSearchPlaceholder) + '"') + ' role="textbox" aria-label="Search">' +
      '</div>'
          : '';
      var actionsbox = this.multiple && this.options.actionsBox ?
      '<div class="bs-actionsbox">' +
      '<div class="btn-group btn-group-sm btn-block">' +
      '<button type="button" class="actions-btn bs-select-all btn btn-default">' +
      this.options.selectAllText +
      '</button>' +
      '<button type="button" class="actions-btn bs-deselect-all btn btn-default">' +
      this.options.deselectAllText +
      '</button>' +
      '</div>' +
      '</div>'
          : '';
      var donebutton = this.multiple && this.options.doneButton ?
      '<div class="bs-donebutton">' +
      '<div class="btn-group btn-block">' +
      '<button type="button" class="btn btn-sm btn-default">' +
      this.options.doneButtonText +
      '</button>' +
      '</div>' +
      '</div>'
          : '';
      var drop =
          '<div class="btn-group bootstrap-select' + showTick + inputGroup + '">' +
          '<button type="button" class="' + this.options.styleBase + ' dropdown-toggle" data-toggle="dropdown"' + autofocus + ' role="button">' +
          '<span class="filter-option pull-left"></span>&nbsp;' +
          '<span class="bs-caret">' +
          this.options.template.caret +
          '</span>' +
          '</button>' +
          '<div class="dropdown-menu open" role="combobox">' +
          header +
          searchbox +
          actionsbox +
          '<ul class="dropdown-menu inner" role="listbox" aria-expanded="false">' +
          '</ul>' +
          donebutton +
          '</div>' +
          '</div>';

      return $(drop);
    },

    createView: function () {
      var $drop = this.createDropdown(),
          li = this.createLi();

      $drop.find('ul')[0].innerHTML = li;
      return $drop;
    },

    reloadLi: function () {
      // rebuild
      var li = this.createLi();
      this.$menuInner[0].innerHTML = li;
    },

    createLi: function () {
      var that = this,
          _li = [],
          optID = 0,
          titleOption = document.createElement('option'),
          liIndex = -1; // increment liIndex whenever a new <li> element is created to ensure liObj is correct

      // Helper functions
      /**
       * @param content
       * @param [index]
       * @param [classes]
       * @param [optgroup]
       * @returns {string}
       */
      var generateLI = function (content, index, classes, optgroup) {
        return '<li' +
            ((typeof classes !== 'undefined' && '' !== classes) ? ' class="' + classes + '"' : '') +
            ((typeof index !== 'undefined' && null !== index) ? ' data-original-index="' + index + '"' : '') +
            ((typeof optgroup !== 'undefined' && null !== optgroup) ? 'data-optgroup="' + optgroup + '"' : '') +
            '>' + content + '</li>';
      };

      /**
       * @param text
       * @param [classes]
       * @param [inline]
       * @param [tokens]
       * @returns {string}
       */
      var generateA = function (text, classes, inline, tokens) {
        return '<a tabindex="0"' +
            (typeof classes !== 'undefined' ? ' class="' + classes + '"' : '') +
            (inline ? ' style="' + inline + '"' : '') +
            (that.options.liveSearchNormalize ? ' data-normalized-text="' + normalizeToBase(htmlEscape($(text).html())) + '"' : '') +
            (typeof tokens !== 'undefined' || tokens !== null ? ' data-tokens="' + tokens + '"' : '') +
            ' role="option">' + text +
            '<span class="' + that.options.iconBase + ' ' + that.options.tickIcon + ' check-mark"></span>' +
            '</a>';
      };

      if (this.options.title && !this.multiple) {
        // this option doesn't create a new <li> element, but does add a new option, so liIndex is decreased
        // since liObj is recalculated on every refresh, liIndex needs to be decreased even if the titleOption is already appended
        liIndex--;

        if (!this.$element.find('.bs-title-option').length) {
          // Use native JS to prepend option (faster)
          var element = this.$element[0];
          titleOption.className = 'bs-title-option';
          titleOption.innerHTML = this.options.title;
          titleOption.value = '';
          element.insertBefore(titleOption, element.firstChild);
          // Check if selected or data-selected attribute is already set on an option. If not, select the titleOption option.
          // the selected item may have been changed by user or programmatically before the bootstrap select plugin runs,
          // if so, the select will have the data-selected attribute
          var $opt = $(element.options[element.selectedIndex]);
          if ($opt.attr('selected') === undefined && this.$element.data('selected') === undefined) {
            titleOption.selected = true;
          }
        }
      }

      var $selectOptions = this.$element.find('option');

      $selectOptions.each(function (index) {
        var $this = $(this);

        liIndex++;

        if ($this.hasClass('bs-title-option')) return;

        // Get the class and text for the option
        var optionClass = this.className || '',
            inline = htmlEscape(this.style.cssText),
            text = $this.data('content') ? $this.data('content') : $this.html(),
            tokens = $this.data('tokens') ? $this.data('tokens') : null,
            subtext = typeof $this.data('subtext') !== 'undefined' ? '<small class="text-muted">' + $this.data('subtext') + '</small>' : '',
            icon = typeof $this.data('icon') !== 'undefined' ? '<span class="' + that.options.iconBase + ' ' + $this.data('icon') + '"></span> ' : '',
            $parent = $this.parent(),
            isOptgroup = $parent[0].tagName === 'OPTGROUP',
            isOptgroupDisabled = isOptgroup && $parent[0].disabled,
            isDisabled = this.disabled || isOptgroupDisabled,
            prevHiddenIndex;

        if (icon !== '' && isDisabled) {
          icon = '<span>' + icon + '</span>';
        }

        if (that.options.hideDisabled && (isDisabled && !isOptgroup || isOptgroupDisabled)) {
          // set prevHiddenIndex - the index of the first hidden option in a group of hidden options
          // used to determine whether or not a divider should be placed after an optgroup if there are
          // hidden options between the optgroup and the first visible option
          prevHiddenIndex = $this.data('prevHiddenIndex');
          $this.next().data('prevHiddenIndex', (prevHiddenIndex !== undefined ? prevHiddenIndex : index));

          liIndex--;
          return;
        }

        if (!$this.data('content')) {
          // Prepend any icon and append any subtext to the main text.
          text = icon + '<span class="text">' + text + subtext + '</span>';
        }

        if (isOptgroup && $this.data('divider') !== true) {
          if (that.options.hideDisabled && isDisabled) {
            if ($parent.data('allOptionsDisabled') === undefined) {
              var $options = $parent.children();
              $parent.data('allOptionsDisabled', $options.filter(':disabled').length === $options.length);
            }

            if ($parent.data('allOptionsDisabled')) {
              liIndex--;
              return;
            }
          }

          var optGroupClass = ' ' + $parent[0].className || '';

          if ($this.index() === 0) { // Is it the first option of the optgroup?
            optID += 1;

            // Get the opt group label
            var label = $parent[0].label,
                labelSubtext = typeof $parent.data('subtext') !== 'undefined' ? '<small class="text-muted">' + $parent.data('subtext') + '</small>' : '',
                labelIcon = $parent.data('icon') ? '<span class="' + that.options.iconBase + ' ' + $parent.data('icon') + '"></span> ' : '';

            label = labelIcon + '<span class="text">' + htmlEscape(label) + labelSubtext + '</span>';

            if (index !== 0 && _li.length > 0) { // Is it NOT the first option of the select && are there elements in the dropdown?
              liIndex++;
              _li.push(generateLI('', null, 'divider', optID + 'div'));
            }
            liIndex++;
            _li.push(generateLI(label, null, 'dropdown-header' + optGroupClass, optID));
          }

          if (that.options.hideDisabled && isDisabled) {
            liIndex--;
            return;
          }

          _li.push(generateLI(generateA(text, 'opt ' + optionClass + optGroupClass, inline, tokens), index, '', optID));
        } else if ($this.data('divider') === true) {
          _li.push(generateLI('', index, 'divider'));
        } else if ($this.data('hidden') === true) {
          // set prevHiddenIndex - the index of the first hidden option in a group of hidden options
          // used to determine whether or not a divider should be placed after an optgroup if there are
          // hidden options between the optgroup and the first visible option
          prevHiddenIndex = $this.data('prevHiddenIndex');
          $this.next().data('prevHiddenIndex', (prevHiddenIndex !== undefined ? prevHiddenIndex : index));

          _li.push(generateLI(generateA(text, optionClass, inline, tokens), index, 'hidden is-hidden'));
        } else {
          var showDivider = this.previousElementSibling && this.previousElementSibling.tagName === 'OPTGROUP';

          // if previous element is not an optgroup and hideDisabled is true
          if (!showDivider && that.options.hideDisabled) {
            prevHiddenIndex = $this.data('prevHiddenIndex');

            if (prevHiddenIndex !== undefined) {
              // select the element **before** the first hidden element in the group
              var prevHidden = $selectOptions.eq(prevHiddenIndex)[0].previousElementSibling;
              
              if (prevHidden && prevHidden.tagName === 'OPTGROUP' && !prevHidden.disabled) {
                showDivider = true;
              }
            }
          }

          if (showDivider) {
            liIndex++;
            _li.push(generateLI('', null, 'divider', optID + 'div'));
          }
          _li.push(generateLI(generateA(text, optionClass, inline, tokens), index));
        }

        that.liObj[index] = liIndex;
      });

      //If we are not multiple, we don't have a selected item, and we don't have a title, select the first element so something is set in the button
      if (!this.multiple && this.$element.find('option:selected').length === 0 && !this.options.title) {
        this.$element.find('option').eq(0).prop('selected', true).attr('selected', 'selected');
      }

      return _li.join('');
    },

    findLis: function () {
      if (this.$lis == null) this.$lis = this.$menu.find('li');
      return this.$lis;
    },

    /**
     * @param [updateLi] defaults to true
     */
    render: function (updateLi) {
      var that = this,
          notDisabled,
          $selectOptions = this.$element.find('option');

      //Update the LI to match the SELECT
      if (updateLi !== false) {
        $selectOptions.each(function (index) {
          var $lis = that.findLis().eq(that.liObj[index]);

          that.setDisabled(index, this.disabled || this.parentNode.tagName === 'OPTGROUP' && this.parentNode.disabled, $lis);
          that.setSelected(index, this.selected, $lis);
        });
      }

      this.togglePlaceholder();

      this.tabIndex();

      var selectedItems = $selectOptions.map(function () {
        if (this.selected) {
          if (that.options.hideDisabled && (this.disabled || this.parentNode.tagName === 'OPTGROUP' && this.parentNode.disabled)) return;

          var $this = $(this),
              icon = $this.data('icon') && that.options.showIcon ? '<i class="' + that.options.iconBase + ' ' + $this.data('icon') + '"></i> ' : '',
              subtext;

          if (that.options.showSubtext && $this.data('subtext') && !that.multiple) {
            subtext = ' <small class="text-muted">' + $this.data('subtext') + '</small>';
          } else {
            subtext = '';
          }
          if (typeof $this.attr('title') !== 'undefined') {
            return $this.attr('title');
          } else if ($this.data('content') && that.options.showContent) {
            return $this.data('content').toString();
          } else {
            return icon + $this.html() + subtext;
          }
        }
      }).toArray();

      //Fixes issue in IE10 occurring when no default option is selected and at least one option is disabled
      //Convert all the values into a comma delimited string
      var title = !this.multiple ? selectedItems[0] : selectedItems.join(this.options.multipleSeparator);

      //If this is multi select, and the selectText type is count, the show 1 of 2 selected etc..
      if (this.multiple && this.options.selectedTextFormat.indexOf('count') > -1) {
        var max = this.options.selectedTextFormat.split('>');
        if ((max.length > 1 && selectedItems.length > max[1]) || (max.length == 1 && selectedItems.length >= 2)) {
          notDisabled = this.options.hideDisabled ? ', [disabled]' : '';
          var totalCount = $selectOptions.not('[data-divider="true"], [data-hidden="true"]' + notDisabled).length,
              tr8nText = (typeof this.options.countSelectedText === 'function') ? this.options.countSelectedText(selectedItems.length, totalCount) : this.options.countSelectedText;
          title = tr8nText.replace('{0}', selectedItems.length.toString()).replace('{1}', totalCount.toString());
        }
      }

      if (this.options.title == undefined) {
        this.options.title = this.$element.attr('title');
      }

      if (this.options.selectedTextFormat == 'static') {
        title = this.options.title;
      }

      //If we dont have a title, then use the default, or if nothing is set at all, use the not selected text
      if (!title) {
        title = typeof this.options.title !== 'undefined' ? this.options.title : this.options.noneSelectedText;
      }

      //strip all HTML tags and trim the result, then unescape any escaped tags
      this.$button.attr('title', htmlUnescape($.trim(title.replace(/<[^>]*>?/g, ''))));
      this.$button.children('.filter-option').html(title);

      this.$element.trigger('rendered.bs.select');
    },

    /**
     * @param [style]
     * @param [status]
     */
    setStyle: function (style, status) {
      if (this.$element.attr('class')) {
        this.$newElement.addClass(this.$element.attr('class').replace(/selectpicker|mobile-device|bs-select-hidden|validate\[.*\]/gi, ''));
      }

      var buttonClass = style ? style : this.options.style;

      if (status == 'add') {
        this.$button.addClass(buttonClass);
      } else if (status == 'remove') {
        this.$button.removeClass(buttonClass);
      } else {
        this.$button.removeClass(this.options.style);
        this.$button.addClass(buttonClass);
      }
    },

    liHeight: function (refresh) {
      if (!refresh && (this.options.size === false || this.sizeInfo)) return;

      var newElement = document.createElement('div'),
          menu = document.createElement('div'),
          menuInner = document.createElement('ul'),
          divider = document.createElement('li'),
          li = document.createElement('li'),
          a = document.createElement('a'),
          text = document.createElement('span'),
          header = this.options.header && this.$menu.find('.popover-title').length > 0 ? this.$menu.find('.popover-title')[0].cloneNode(true) : null,
          search = this.options.liveSearch ? document.createElement('div') : null,
          actions = this.options.actionsBox && this.multiple && this.$menu.find('.bs-actionsbox').length > 0 ? this.$menu.find('.bs-actionsbox')[0].cloneNode(true) : null,
          doneButton = this.options.doneButton && this.multiple && this.$menu.find('.bs-donebutton').length > 0 ? this.$menu.find('.bs-donebutton')[0].cloneNode(true) : null;

      text.className = 'text';
      newElement.className = this.$menu[0].parentNode.className + ' open';
      menu.className = 'dropdown-menu open';
      menuInner.className = 'dropdown-menu inner';
      divider.className = 'divider';

      text.appendChild(document.createTextNode('Inner text'));
      a.appendChild(text);
      li.appendChild(a);
      menuInner.appendChild(li);
      menuInner.appendChild(divider);
      if (header) menu.appendChild(header);
      if (search) {
        var input = document.createElement('input');
        search.className = 'bs-searchbox';
        input.className = 'form-control';
        search.appendChild(input);
        menu.appendChild(search);
      }
      if (actions) menu.appendChild(actions);
      menu.appendChild(menuInner);
      if (doneButton) menu.appendChild(doneButton);
      newElement.appendChild(menu);

      document.body.appendChild(newElement);

      var liHeight = a.offsetHeight,
          headerHeight = header ? header.offsetHeight : 0,
          searchHeight = search ? search.offsetHeight : 0,
          actionsHeight = actions ? actions.offsetHeight : 0,
          doneButtonHeight = doneButton ? doneButton.offsetHeight : 0,
          dividerHeight = $(divider).outerHeight(true),
          // fall back to jQuery if getComputedStyle is not supported
          menuStyle = typeof getComputedStyle === 'function' ? getComputedStyle(menu) : false,
          $menu = menuStyle ? null : $(menu),
          menuPadding = {
            vert: parseInt(menuStyle ? menuStyle.paddingTop : $menu.css('paddingTop')) +
                  parseInt(menuStyle ? menuStyle.paddingBottom : $menu.css('paddingBottom')) +
                  parseInt(menuStyle ? menuStyle.borderTopWidth : $menu.css('borderTopWidth')) +
                  parseInt(menuStyle ? menuStyle.borderBottomWidth : $menu.css('borderBottomWidth')),
            horiz: parseInt(menuStyle ? menuStyle.paddingLeft : $menu.css('paddingLeft')) +
                  parseInt(menuStyle ? menuStyle.paddingRight : $menu.css('paddingRight')) +
                  parseInt(menuStyle ? menuStyle.borderLeftWidth : $menu.css('borderLeftWidth')) +
                  parseInt(menuStyle ? menuStyle.borderRightWidth : $menu.css('borderRightWidth'))
          },
          menuExtras =  {
            vert: menuPadding.vert +
                  parseInt(menuStyle ? menuStyle.marginTop : $menu.css('marginTop')) +
                  parseInt(menuStyle ? menuStyle.marginBottom : $menu.css('marginBottom')) + 2,
            horiz: menuPadding.horiz +
                  parseInt(menuStyle ? menuStyle.marginLeft : $menu.css('marginLeft')) +
                  parseInt(menuStyle ? menuStyle.marginRight : $menu.css('marginRight')) + 2
          }

      document.body.removeChild(newElement);

      this.sizeInfo = {
        liHeight: liHeight,
        headerHeight: headerHeight,
        searchHeight: searchHeight,
        actionsHeight: actionsHeight,
        doneButtonHeight: doneButtonHeight,
        dividerHeight: dividerHeight,
        menuPadding: menuPadding,
        menuExtras: menuExtras
      };
    },

    setSize: function () {
      this.findLis();
      this.liHeight();

      if (this.options.header) this.$menu.css('padding-top', 0);
      if (this.options.size === false) return;

      var that = this,
          $menu = this.$menu,
          $menuInner = this.$menuInner,
          $window = $(window),
          selectHeight = this.$newElement[0].offsetHeight,
          selectWidth = this.$newElement[0].offsetWidth,
          liHeight = this.sizeInfo['liHeight'],
          headerHeight = this.sizeInfo['headerHeight'],
          searchHeight = this.sizeInfo['searchHeight'],
          actionsHeight = this.sizeInfo['actionsHeight'],
          doneButtonHeight = this.sizeInfo['doneButtonHeight'],
          divHeight = this.sizeInfo['dividerHeight'],
          menuPadding = this.sizeInfo['menuPadding'],
          menuExtras = this.sizeInfo['menuExtras'],
          notDisabled = this.options.hideDisabled ? '.disabled' : '',
          menuHeight,
          menuWidth,
          getHeight,
          getWidth,
          selectOffsetTop,
          selectOffsetBot,
          selectOffsetLeft,
          selectOffsetRight,
          getPos = function() {
            var pos = that.$newElement.offset(),
                $container = $(that.options.container),
                containerPos;

            if (that.options.container && !$container.is('body')) {
              containerPos = $container.offset();
              containerPos.top += parseInt($container.css('borderTopWidth'));
              containerPos.left += parseInt($container.css('borderLeftWidth'));
            } else {
              containerPos = { top: 0, left: 0 };
            }

            var winPad = that.options.windowPadding;
            selectOffsetTop = pos.top - containerPos.top - $window.scrollTop();
            selectOffsetBot = $window.height() - selectOffsetTop - selectHeight - containerPos.top - winPad[2];
            selectOffsetLeft = pos.left - containerPos.left - $window.scrollLeft();
            selectOffsetRight = $window.width() - selectOffsetLeft - selectWidth - containerPos.left - winPad[1];
            selectOffsetTop -= winPad[0];
            selectOffsetLeft -= winPad[3];
          };

      getPos();

      if (this.options.size === 'auto') {
        var getSize = function () {
          var minHeight,
              hasClass = function (className, include) {
                return function (element) {
                    if (include) {
                        return (element.classList ? element.classList.contains(className) : $(element).hasClass(className));
                    } else {
                        return !(element.classList ? element.classList.contains(className) : $(element).hasClass(className));
                    }
                };
              },
              lis = that.$menuInner[0].getElementsByTagName('li'),
              lisVisible = Array.prototype.filter ? Array.prototype.filter.call(lis, hasClass('hidden', false)) : that.$lis.not('.hidden'),
              optGroup = Array.prototype.filter ? Array.prototype.filter.call(lisVisible, hasClass('dropdown-header', true)) : lisVisible.filter('.dropdown-header');

          getPos();
          menuHeight = selectOffsetBot - menuExtras.vert;
          menuWidth = selectOffsetRight - menuExtras.horiz;

          if (that.options.container) {
            if (!$menu.data('height')) $menu.data('height', $menu.height());
            getHeight = $menu.data('height');

            if (!$menu.data('width')) $menu.data('width', $menu.width());
            getWidth = $menu.data('width');
          } else {
            getHeight = $menu.height();
            getWidth = $menu.width();
          }

          if (that.options.dropupAuto) {
            that.$newElement.toggleClass('dropup', selectOffsetTop > selectOffsetBot && (menuHeight - menuExtras.vert) < getHeight);
          }

          if (that.$newElement.hasClass('dropup')) {
            menuHeight = selectOffsetTop - menuExtras.vert;
          }

          if (that.options.dropdownAlignRight === 'auto') {
            $menu.toggleClass('dropdown-menu-right', selectOffsetLeft > selectOffsetRight && (menuWidth - menuExtras.horiz) < (getWidth - selectWidth));
          }

          if ((lisVisible.length + optGroup.length) > 3) {
            minHeight = liHeight * 3 + menuExtras.vert - 2;
          } else {
            minHeight = 0;
          }

          $menu.css({
            'max-height': menuHeight + 'px',
            'overflow': 'hidden',
            'min-height': minHeight + headerHeight + searchHeight + actionsHeight + doneButtonHeight + 'px'
          });
          $menuInner.css({
            'max-height': menuHeight - headerHeight - searchHeight - actionsHeight - doneButtonHeight - menuPadding.vert + 'px',
            'overflow-y': 'auto',
            'min-height': Math.max(minHeight - menuPadding.vert, 0) + 'px'
          });
        };
        getSize();
        this.$searchbox.off('input.getSize propertychange.getSize').on('input.getSize propertychange.getSize', getSize);
        $window.off('resize.getSize scroll.getSize').on('resize.getSize scroll.getSize', getSize);
      } else if (this.options.size && this.options.size != 'auto' && this.$lis.not(notDisabled).length > this.options.size) {
        var optIndex = this.$lis.not('.divider').not(notDisabled).children().slice(0, this.options.size).last().parent().index(),
            divLength = this.$lis.slice(0, optIndex + 1).filter('.divider').length;
        menuHeight = liHeight * this.options.size + divLength * divHeight + menuPadding.vert;

        if (that.options.container) {
          if (!$menu.data('height')) $menu.data('height', $menu.height());
          getHeight = $menu.data('height');
        } else {
          getHeight = $menu.height();
        }

        if (that.options.dropupAuto) {
          //noinspection JSUnusedAssignment
          this.$newElement.toggleClass('dropup', selectOffsetTop > selectOffsetBot && (menuHeight - menuExtras.vert) < getHeight);
        }
        $menu.css({
          'max-height': menuHeight + headerHeight + searchHeight + actionsHeight + doneButtonHeight + 'px',
          'overflow': 'hidden',
          'min-height': ''
        });
        $menuInner.css({
          'max-height': menuHeight - menuPadding.vert + 'px',
          'overflow-y': 'auto',
          'min-height': ''
        });
      }
    },

    setWidth: function () {
      if (this.options.width === 'auto') {
        this.$menu.css('min-width', '0');

        // Get correct width if element is hidden
        var $selectClone = this.$menu.parent().clone().appendTo('body'),
            $selectClone2 = this.options.container ? this.$newElement.clone().appendTo('body') : $selectClone,
            ulWidth = $selectClone.children('.dropdown-menu').outerWidth(),
            btnWidth = $selectClone2.css('width', 'auto').children('button').outerWidth();

        $selectClone.remove();
        $selectClone2.remove();

        // Set width to whatever's larger, button title or longest option
        this.$newElement.css('width', Math.max(ulWidth, btnWidth) + 'px');
      } else if (this.options.width === 'fit') {
        // Remove inline min-width so width can be changed from 'auto'
        this.$menu.css('min-width', '');
        this.$newElement.css('width', '').addClass('fit-width');
      } else if (this.options.width) {
        // Remove inline min-width so width can be changed from 'auto'
        this.$menu.css('min-width', '');
        this.$newElement.css('width', this.options.width);
      } else {
        // Remove inline min-width/width so width can be changed
        this.$menu.css('min-width', '');
        this.$newElement.css('width', '');
      }
      // Remove fit-width class if width is changed programmatically
      if (this.$newElement.hasClass('fit-width') && this.options.width !== 'fit') {
        this.$newElement.removeClass('fit-width');
      }
    },

    selectPosition: function () {
      this.$bsContainer = $('<div class="bs-container" />');

      var that = this,
          $container = $(this.options.container),
          pos,
          containerPos,
          actualHeight,
          getPlacement = function ($element) {
            that.$bsContainer.addClass($element.attr('class').replace(/form-control|fit-width/gi, '')).toggleClass('dropup', $element.hasClass('dropup'));
            pos = $element.offset();

            if (!$container.is('body')) {
              containerPos = $container.offset();
              containerPos.top += parseInt($container.css('borderTopWidth')) - $container.scrollTop();
              containerPos.left += parseInt($container.css('borderLeftWidth')) - $container.scrollLeft();
            } else {
              containerPos = { top: 0, left: 0 };
            }

            actualHeight = $element.hasClass('dropup') ? 0 : $element[0].offsetHeight;

            that.$bsContainer.css({
              'top': pos.top - containerPos.top + actualHeight,
              'left': pos.left - containerPos.left,
              'width': $element[0].offsetWidth
            });
          };

      this.$button.on('click', function () {
        var $this = $(this);

        if (that.isDisabled()) {
          return;
        }

        getPlacement(that.$newElement);

        that.$bsContainer
          .appendTo(that.options.container)
          .toggleClass('open', !$this.hasClass('open'))
          .append(that.$menu);
      });

      $(window).on('resize scroll', function () {
        getPlacement(that.$newElement);
      });

      this.$element.on('hide.bs.select', function () {
        that.$menu.data('height', that.$menu.height());
        that.$bsContainer.detach();
      });
    },

    /**
     * @param {number} index - the index of the option that is being changed
     * @param {boolean} selected - true if the option is being selected, false if being deselected
     * @param {JQuery} $lis - the 'li' element that is being modified
     */
    setSelected: function (index, selected, $lis) {
      if (!$lis) {
        this.togglePlaceholder(); // check if setSelected is being called by changing the value of the select
        $lis = this.findLis().eq(this.liObj[index]);
      }

      $lis.toggleClass('selected', selected).find('a').attr('aria-selected', selected);
    },

    /**
     * @param {number} index - the index of the option that is being disabled
     * @param {boolean} disabled - true if the option is being disabled, false if being enabled
     * @param {JQuery} $lis - the 'li' element that is being modified
     */
    setDisabled: function (index, disabled, $lis) {
      if (!$lis) {
        $lis = this.findLis().eq(this.liObj[index]);
      }

      if (disabled) {
        $lis.addClass('disabled').children('a').attr('href', '#').attr('tabindex', -1).attr('aria-disabled', true);
      } else {
        $lis.removeClass('disabled').children('a').removeAttr('href').attr('tabindex', 0).attr('aria-disabled', false);
      }
    },

    isDisabled: function () {
      return this.$element[0].disabled;
    },

    checkDisabled: function () {
      var that = this;

      if (this.isDisabled()) {
        this.$newElement.addClass('disabled');
        this.$button.addClass('disabled').attr('tabindex', -1).attr('aria-disabled', true);
      } else {
        if (this.$button.hasClass('disabled')) {
          this.$newElement.removeClass('disabled');
          this.$button.removeClass('disabled').attr('aria-disabled', false);
        }

        if (this.$button.attr('tabindex') == -1 && !this.$element.data('tabindex')) {
          this.$button.removeAttr('tabindex');
        }
      }

      this.$button.click(function () {
        return !that.isDisabled();
      });
    },

    togglePlaceholder: function () {
      var value = this.$element.val();
      this.$button.toggleClass('bs-placeholder', value === null || value === '' || (value.constructor === Array && value.length === 0));
    },

    tabIndex: function () {
      if (this.$element.data('tabindex') !== this.$element.attr('tabindex') && 
        (this.$element.attr('tabindex') !== -98 && this.$element.attr('tabindex') !== '-98')) {
        this.$element.data('tabindex', this.$element.attr('tabindex'));
        this.$button.attr('tabindex', this.$element.data('tabindex'));
      }

      this.$element.attr('tabindex', -98);
    },

    clickListener: function () {
      var that = this,
          $document = $(document);

      $document.data('spaceSelect', false);

      this.$button.on('keyup', function (e) {
        if (/(32)/.test(e.keyCode.toString(10)) && $document.data('spaceSelect')) {
            e.preventDefault();
            $document.data('spaceSelect', false);
        }
      });

      this.$button.on('click', function () {
        that.setSize();
      });

      this.$element.on('shown.bs.select', function () {
        if (!that.options.liveSearch && !that.multiple) {
          that.$menuInner.find('.selected a').focus();
        } else if (!that.multiple) {
          var selectedIndex = that.liObj[that.$element[0].selectedIndex];

          if (typeof selectedIndex !== 'number' || that.options.size === false) return;

          // scroll to selected option
          var offset = that.$lis.eq(selectedIndex)[0].offsetTop - that.$menuInner[0].offsetTop;
          offset = offset - that.$menuInner[0].offsetHeight/2 + that.sizeInfo.liHeight/2;
          that.$menuInner[0].scrollTop = offset;
        }
      });

      this.$menuInner.on('click', 'li a', function (e) {
        var $this = $(this),
            clickedIndex = $this.parent().data('originalIndex'),
            prevValue = that.$element.val(),
            prevIndex = that.$element.prop('selectedIndex'),
            triggerChange = true;

        // Don't close on multi choice menu
        if (that.multiple && that.options.maxOptions !== 1) {
          e.stopPropagation();
        }

        e.preventDefault();

        //Don't run if we have been disabled
        if (!that.isDisabled() && !$this.parent().hasClass('disabled')) {
          var $options = that.$element.find('option'),
              $option = $options.eq(clickedIndex),
              state = $option.prop('selected'),
              $optgroup = $option.parent('optgroup'),
              maxOptions = that.options.maxOptions,
              maxOptionsGrp = $optgroup.data('maxOptions') || false;

          if (!that.multiple) { // Deselect all others if not multi select box
            $options.prop('selected', false);
            $option.prop('selected', true);
            that.$menuInner.find('.selected').removeClass('selected').find('a').attr('aria-selected', false);
            that.setSelected(clickedIndex, true);
          } else { // Toggle the one we have chosen if we are multi select.
            $option.prop('selected', !state);
            that.setSelected(clickedIndex, !state);
            $this.blur();

            if (maxOptions !== false || maxOptionsGrp !== false) {
              var maxReached = maxOptions < $options.filter(':selected').length,
                  maxReachedGrp = maxOptionsGrp < $optgroup.find('option:selected').length;

              if ((maxOptions && maxReached) || (maxOptionsGrp && maxReachedGrp)) {
                if (maxOptions && maxOptions == 1) {
                  $options.prop('selected', false);
                  $option.prop('selected', true);
                  that.$menuInner.find('.selected').removeClass('selected');
                  that.setSelected(clickedIndex, true);
                } else if (maxOptionsGrp && maxOptionsGrp == 1) {
                  $optgroup.find('option:selected').prop('selected', false);
                  $option.prop('selected', true);
                  var optgroupID = $this.parent().data('optgroup');
                  that.$menuInner.find('[data-optgroup="' + optgroupID + '"]').removeClass('selected');
                  that.setSelected(clickedIndex, true);
                } else {
                  var maxOptionsText = typeof that.options.maxOptionsText === 'string' ? [that.options.maxOptionsText, that.options.maxOptionsText] : that.options.maxOptionsText,
                      maxOptionsArr = typeof maxOptionsText === 'function' ? maxOptionsText(maxOptions, maxOptionsGrp) : maxOptionsText,
                      maxTxt = maxOptionsArr[0].replace('{n}', maxOptions),
                      maxTxtGrp = maxOptionsArr[1].replace('{n}', maxOptionsGrp),
                      $notify = $('<div class="notify"></div>');
                  // If {var} is set in array, replace it
                  /** @deprecated */
                  if (maxOptionsArr[2]) {
                    maxTxt = maxTxt.replace('{var}', maxOptionsArr[2][maxOptions > 1 ? 0 : 1]);
                    maxTxtGrp = maxTxtGrp.replace('{var}', maxOptionsArr[2][maxOptionsGrp > 1 ? 0 : 1]);
                  }

                  $option.prop('selected', false);

                  that.$menu.append($notify);

                  if (maxOptions && maxReached) {
                    $notify.append($('<div>' + maxTxt + '</div>'));
                    triggerChange = false;
                    that.$element.trigger('maxReached.bs.select');
                  }

                  if (maxOptionsGrp && maxReachedGrp) {
                    $notify.append($('<div>' + maxTxtGrp + '</div>'));
                    triggerChange = false;
                    that.$element.trigger('maxReachedGrp.bs.select');
                  }

                  setTimeout(function () {
                    that.setSelected(clickedIndex, false);
                  }, 10);

                  $notify.delay(750).fadeOut(300, function () {
                    $(this).remove();
                  });
                }
              }
            }
          }

          if (!that.multiple || (that.multiple && that.options.maxOptions === 1)) {
            that.$button.focus();
          } else if (that.options.liveSearch) {
            that.$searchbox.focus();
          }

          // Trigger select 'change'
          if (triggerChange) {
            if ((prevValue != that.$element.val() && that.multiple) || (prevIndex != that.$element.prop('selectedIndex') && !that.multiple)) {
              // $option.prop('selected') is current option state (selected/unselected). state is previous option state.
              changed_arguments = [clickedIndex, $option.prop('selected'), state];
              that.$element
                .triggerNative('change');
            }
          }
        }
      });

      this.$menu.on('click', 'li.disabled a, .popover-title, .popover-title :not(.close)', function (e) {
        if (e.currentTarget == this) {
          e.preventDefault();
          e.stopPropagation();
          if (that.options.liveSearch && !$(e.target).hasClass('close')) {
            that.$searchbox.focus();
          } else {
            that.$button.focus();
          }
        }
      });

      this.$menuInner.on('click', '.divider, .dropdown-header', function (e) {
        e.preventDefault();
        e.stopPropagation();
        if (that.options.liveSearch) {
          that.$searchbox.focus();
        } else {
          that.$button.focus();
        }
      });

      this.$menu.on('click', '.popover-title .close', function () {
        that.$button.click();
      });

      this.$searchbox.on('click', function (e) {
        e.stopPropagation();
      });

      this.$menu.on('click', '.actions-btn', function (e) {
        if (that.options.liveSearch) {
          that.$searchbox.focus();
        } else {
          that.$button.focus();
        }

        e.preventDefault();
        e.stopPropagation();

        if ($(this).hasClass('bs-select-all')) {
          that.selectAll();
        } else {
          that.deselectAll();
        }
      });

      this.$element.change(function () {
        that.render(false);
        that.$element.trigger('changed.bs.select', changed_arguments);
        changed_arguments = null;
      });
    },

    liveSearchListener: function () {
      var that = this,
          $no_results = $('<li class="no-results"></li>');

      this.$button.on('click.dropdown.data-api', function () {
        that.$menuInner.find('.active').removeClass('active');
        if (!!that.$searchbox.val()) {
          that.$searchbox.val('');
          that.$lis.not('.is-hidden').removeClass('hidden');
          if (!!$no_results.parent().length) $no_results.remove();
        }
        if (!that.multiple) that.$menuInner.find('.selected').addClass('active');
        setTimeout(function () {
          that.$searchbox.focus();
        }, 10);
      });

      this.$searchbox.on('click.dropdown.data-api focus.dropdown.data-api touchend.dropdown.data-api', function (e) {
        e.stopPropagation();
      });

      this.$searchbox.on('input propertychange', function () {
        that.$lis.not('.is-hidden').removeClass('hidden');
        that.$lis.filter('.active').removeClass('active');
        $no_results.remove();

        if (that.$searchbox.val()) {
          var $searchBase = that.$lis.not('.is-hidden, .divider, .dropdown-header'),
              $hideItems;
          if (that.options.liveSearchNormalize) {
            $hideItems = $searchBase.not(':a' + that._searchStyle() + '("' + normalizeToBase(that.$searchbox.val()) + '")');
          } else {
            $hideItems = $searchBase.not(':' + that._searchStyle() + '("' + that.$searchbox.val() + '")');
          }

          if ($hideItems.length === $searchBase.length) {
            $no_results.html(that.options.noneResultsText.replace('{0}', '"' + htmlEscape(that.$searchbox.val()) + '"'));
            that.$menuInner.append($no_results);
            that.$lis.addClass('hidden');
          } else {
            $hideItems.addClass('hidden');

            var $lisVisible = that.$lis.not('.hidden'),
                $foundDiv;

            // hide divider if first or last visible, or if followed by another divider
            $lisVisible.each(function (index) {
              var $this = $(this);

              if ($this.hasClass('divider')) {
                if ($foundDiv === undefined) {
                  $this.addClass('hidden');
                } else {
                  if ($foundDiv) $foundDiv.addClass('hidden');
                  $foundDiv = $this;
                }
              } else if ($this.hasClass('dropdown-header') && $lisVisible.eq(index + 1).data('optgroup') !== $this.data('optgroup')) {
                $this.addClass('hidden');
              } else {
                $foundDiv = null;
              }
            });
            if ($foundDiv) $foundDiv.addClass('hidden');

            $searchBase.not('.hidden').first().addClass('active');
            that.$menuInner.scrollTop(0);
          }
        }
      });
    },

    _searchStyle: function () {
      var styles = {
        begins: 'ibegins',
        startsWith: 'ibegins'
      };

      return styles[this.options.liveSearchStyle] || 'icontains';
    },

    val: function (value) {
      if (typeof value !== 'undefined') {
        this.$element.val(value);
        this.render();

        return this.$element;
      } else {
        return this.$element.val();
      }
    },

    changeAll: function (status) {
      if (!this.multiple) return;
      if (typeof status === 'undefined') status = true;

      this.findLis();

      var $options = this.$element.find('option'),
          $lisVisible = this.$lis.not('.divider, .dropdown-header, .disabled, .hidden'),
          lisVisLen = $lisVisible.length,
          selectedOptions = [];
          
      if (status) {
        if ($lisVisible.filter('.selected').length === $lisVisible.length) return;
      } else {
        if ($lisVisible.filter('.selected').length === 0) return;
      }
          
      $lisVisible.toggleClass('selected', status);

      for (var i = 0; i < lisVisLen; i++) {
        var origIndex = $lisVisible[i].getAttribute('data-original-index');
        selectedOptions[selectedOptions.length] = $options.eq(origIndex)[0];
      }

      $(selectedOptions).prop('selected', status);

      this.render(false);

      this.togglePlaceholder();

      this.$element
        .triggerNative('change');
    },

    selectAll: function () {
      return this.changeAll(true);
    },

    deselectAll: function () {
      return this.changeAll(false);
    },

    toggle: function (e) {
      e = e || window.event;

      if (e) e.stopPropagation();

      this.$button.trigger('click');
    },

    keydown: function (e) {
      var $this = $(this),
          $parent = $this.is('input') ? $this.parent().parent() : $this.parent(),
          $items,
          that = $parent.data('this'),
          index,
          prevIndex,
          isActive,
          selector = ':not(.disabled, .hidden, .dropdown-header, .divider)',
          keyCodeMap = {
            32: ' ',
            48: '0',
            49: '1',
            50: '2',
            51: '3',
            52: '4',
            53: '5',
            54: '6',
            55: '7',
            56: '8',
            57: '9',
            59: ';',
            65: 'a',
            66: 'b',
            67: 'c',
            68: 'd',
            69: 'e',
            70: 'f',
            71: 'g',
            72: 'h',
            73: 'i',
            74: 'j',
            75: 'k',
            76: 'l',
            77: 'm',
            78: 'n',
            79: 'o',
            80: 'p',
            81: 'q',
            82: 'r',
            83: 's',
            84: 't',
            85: 'u',
            86: 'v',
            87: 'w',
            88: 'x',
            89: 'y',
            90: 'z',
            96: '0',
            97: '1',
            98: '2',
            99: '3',
            100: '4',
            101: '5',
            102: '6',
            103: '7',
            104: '8',
            105: '9'
          };


      isActive = that.$newElement.hasClass('open');

      if (!isActive && (e.keyCode >= 48 && e.keyCode <= 57 || e.keyCode >= 96 && e.keyCode <= 105 || e.keyCode >= 65 && e.keyCode <= 90)) {
        if (!that.options.container) {
          that.setSize();
          that.$menu.parent().addClass('open');
          isActive = true;
        } else {
          that.$button.trigger('click');
        }
        that.$searchbox.focus();
        return;
      }

      if (that.options.liveSearch) {
        if (/(^9$|27)/.test(e.keyCode.toString(10)) && isActive) {
          e.preventDefault();
          e.stopPropagation();
          that.$menuInner.click();
          that.$button.focus();
        }
      }

      if (/(38|40)/.test(e.keyCode.toString(10))) {
        $items = that.$lis.filter(selector);
        if (!$items.length) return;

        if (!that.options.liveSearch) {
          index = $items.index($items.find('a').filter(':focus').parent());
	    } else {
          index = $items.index($items.filter('.active'));
        }

        prevIndex = that.$menuInner.data('prevIndex');

        if (e.keyCode == 38) {
          if ((that.options.liveSearch || index == prevIndex) && index != -1) index--;
          if (index < 0) index += $items.length;
        } else if (e.keyCode == 40) {
          if (that.options.liveSearch || index == prevIndex) index++;
          index = index % $items.length;
        }

        that.$menuInner.data('prevIndex', index);

        if (!that.options.liveSearch) {
          $items.eq(index).children('a').focus();
        } else {
          e.preventDefault();
          if (!$this.hasClass('dropdown-toggle')) {
            $items.removeClass('active').eq(index).addClass('active').children('a').focus();
            $this.focus();
          }
        }

      } else if (!$this.is('input')) {
        var keyIndex = [],
            count,
            prevKey;

        $items = that.$lis.filter(selector);
        $items.each(function (i) {
          if ($.trim($(this).children('a').text().toLowerCase()).substring(0, 1) == keyCodeMap[e.keyCode]) {
            keyIndex.push(i);
          }
        });

        count = $(document).data('keycount');
        count++;
        $(document).data('keycount', count);

        prevKey = $.trim($(':focus').text().toLowerCase()).substring(0, 1);

        if (prevKey != keyCodeMap[e.keyCode]) {
          count = 1;
          $(document).data('keycount', count);
        } else if (count >= keyIndex.length) {
          $(document).data('keycount', 0);
          if (count > keyIndex.length) count = 1;
        }

        $items.eq(keyIndex[count - 1]).children('a').focus();
      }

      // Select focused option if "Enter", "Spacebar" or "Tab" (when selectOnTab is true) are pressed inside the menu.
      if ((/(13|32)/.test(e.keyCode.toString(10)) || (/(^9$)/.test(e.keyCode.toString(10)) && that.options.selectOnTab)) && isActive) {
        if (!/(32)/.test(e.keyCode.toString(10))) e.preventDefault();
        if (!that.options.liveSearch) {
          var elem = $(':focus');
          elem.click();
          // Bring back focus for multiselects
          elem.focus();
          // Prevent screen from scrolling if the user hit the spacebar
          e.preventDefault();
          // Fixes spacebar selection of dropdown items in FF & IE
          $(document).data('spaceSelect', true);
        } else if (!/(32)/.test(e.keyCode.toString(10))) {
          that.$menuInner.find('.active a').click();
          $this.focus();
        }
        $(document).data('keycount', 0);
      }

      if ((/(^9$|27)/.test(e.keyCode.toString(10)) && isActive && (that.multiple || that.options.liveSearch)) || (/(27)/.test(e.keyCode.toString(10)) && !isActive)) {
        that.$menu.parent().removeClass('open');
        if (that.options.container) that.$newElement.removeClass('open');
        that.$button.focus();
      }
    },

    mobile: function () {
      this.$element.addClass('mobile-device');
    },

    refresh: function () {
      this.$lis = null;
      this.liObj = {};
      this.reloadLi();
      this.render();
      this.checkDisabled();
      this.liHeight(true);
      this.setStyle();
      this.setWidth();
      if (this.$lis) this.$searchbox.trigger('propertychange');

      this.$element.trigger('refreshed.bs.select');
    },

    hide: function () {
      this.$newElement.hide();
    },

    show: function () {
      this.$newElement.show();
    },

    remove: function () {
      this.$newElement.remove();
      this.$element.remove();
    },

    destroy: function () {
      this.$newElement.before(this.$element).remove();

      if (this.$bsContainer) {
        this.$bsContainer.remove();
      } else {
        this.$menu.remove();
      }

      this.$element
        .off('.bs.select')
        .removeData('selectpicker')
        .removeClass('bs-select-hidden selectpicker');
    }
  };

  // SELECTPICKER PLUGIN DEFINITION
  // ==============================
  function Plugin(option) {
    // get the args of the outer function..
    var args = arguments;
    // The arguments of the function are explicitly re-defined from the argument list, because the shift causes them
    // to get lost/corrupted in android 2.3 and IE9 #715 #775
    var _option = option;

    [].shift.apply(args);

    var value;
    var chain = this.each(function () {
      var $this = $(this);
      if ($this.is('select')) {
        var data = $this.data('selectpicker'),
            options = typeof _option == 'object' && _option;

        if (!data) {
          var config = $.extend({}, Selectpicker.DEFAULTS, $.fn.selectpicker.defaults || {}, $this.data(), options);
          config.template = $.extend({}, Selectpicker.DEFAULTS.template, ($.fn.selectpicker.defaults ? $.fn.selectpicker.defaults.template : {}), $this.data().template, options.template);
          $this.data('selectpicker', (data = new Selectpicker(this, config)));
        } else if (options) {
          for (var i in options) {
            if (options.hasOwnProperty(i)) {
              data.options[i] = options[i];
            }
          }
        }

        if (typeof _option == 'string') {
          if (data[_option] instanceof Function) {
            value = data[_option].apply(data, args);
          } else {
            value = data.options[_option];
          }
        }
      }
    });

    if (typeof value !== 'undefined') {
      //noinspection JSUnusedAssignment
      return value;
    } else {
      return chain;
    }
  }

  var old = $.fn.selectpicker;
  $.fn.selectpicker = Plugin;
  $.fn.selectpicker.Constructor = Selectpicker;

  // SELECTPICKER NO CONFLICT
  // ========================
  $.fn.selectpicker.noConflict = function () {
    $.fn.selectpicker = old;
    return this;
  };

  $(document)
      .data('keycount', 0)
      .on('keydown.bs.select', '.bootstrap-select [data-toggle=dropdown], .bootstrap-select [role="listbox"], .bs-searchbox input', Selectpicker.prototype.keydown)
      .on('focusin.modal', '.bootstrap-select [data-toggle=dropdown], .bootstrap-select [role="listbox"], .bs-searchbox input', function (e) {
        e.stopPropagation();
      });

  // SELECTPICKER DATA-API
  // =====================
  $(window).on('load.bs.select.data-api', function () {
    $('.selectpicker').each(function () {
      var $selectpicker = $(this);
      Plugin.call($selectpicker, $selectpicker.data());
    })
  });
})(jQuery);


}));

/* ===================================================
 *  jquery-sortable.js v0.9.13
 *  http://johnny.github.com/jquery-sortable/
 * ===================================================
 *  Copyright (c) 2012 Jonas von Andrian
 *  All rights reserved.
 *
 *  Redistribution and use in source and binary forms, with or without
 *  modification, are permitted provided that the following conditions are met:
 *  * Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 *  * Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 *  * The name of the author may not be used to endorse or promote products
 *    derived from this software without specific prior written permission.
 *
 *  THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 *  ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 *  WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 *  DISCLAIMED. IN NO EVENT SHALL <COPYRIGHT HOLDER> BE LIABLE FOR ANY
 *  DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 *  (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 *  LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 *  ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 *  (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 *  SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 * ========================================================== */


!function ( $, window, pluginName, undefined){
  var containerDefaults = {
    // If true, items can be dragged from this container
    drag: true,
    // If true, items can be droped onto this container
    drop: true,
    // Exclude items from being draggable, if the
    // selector matches the item
    exclude: "",
    // If true, search for nested containers within an item.If you nest containers,
    // either the original selector with which you call the plugin must only match the top containers,
    // or you need to specify a group (see the bootstrap nav example)
    nested: true,
    // If true, the items are assumed to be arranged vertically
    vertical: true
  }, // end container defaults
  groupDefaults = {
    // This is executed after the placeholder has been moved.
    // $closestItemOrContainer contains the closest item, the placeholder
    // has been put at or the closest empty Container, the placeholder has
    // been appended to.
    afterMove: function ($placeholder, container, $closestItemOrContainer) {
    },
    // The exact css path between the container and its items, e.g. "> tbody"
    containerPath: "",
    // The css selector of the containers
    containerSelector: "ol, ul",
    // Distance the mouse has to travel to start dragging
    distance: 0,
    // Time in milliseconds after mousedown until dragging should start.
    // This option can be used to prevent unwanted drags when clicking on an element.
    delay: 0,
    // The css selector of the drag handle
    handle: "",
    // The exact css path between the item and its subcontainers.
    // It should only match the immediate items of a container.
    // No item of a subcontainer should be matched. E.g. for ol>div>li the itemPath is "> div"
    itemPath: "",
    // The css selector of the items
    itemSelector: "li",
    // The class given to "body" while an item is being dragged
    bodyClass: "dragging",
    // The class giving to an item while being dragged
    draggedClass: "dragged",
    // Check if the dragged item may be inside the container.
    // Use with care, since the search for a valid container entails a depth first search
    // and may be quite expensive.
    isValidTarget: function ($item, container) {
      return true
    },
    // Executed before onDrop if placeholder is detached.
    // This happens if pullPlaceholder is set to false and the drop occurs outside a container.
    onCancel: function ($item, container, _super, event) {
    },
    // Executed at the beginning of a mouse move event.
    // The Placeholder has not been moved yet.
    onDrag: function ($item, position, _super, event) {
      $item.css(position)
    },
    // Called after the drag has been started,
    // that is the mouse button is being held down and
    // the mouse is moving.
    // The container is the closest initialized container.
    // Therefore it might not be the container, that actually contains the item.
    onDragStart: function ($item, container, _super, event) {
      $item.css({
        height: $item.outerHeight(),
        width: $item.outerWidth()
      })
      $item.addClass(container.group.options.draggedClass)
      $("body").addClass(container.group.options.bodyClass)
    },
    // Called when the mouse button is being released
    onDrop: function ($item, container, _super, event) {
      $item.removeClass(container.group.options.draggedClass).removeAttr("style")
      $("body").removeClass(container.group.options.bodyClass)
    },
    // Called on mousedown. If falsy value is returned, the dragging will not start.
    // Ignore if element clicked is input, select or textarea
    onMousedown: function ($item, _super, event) {
      if (!event.target.nodeName.match(/^(input|select|textarea)$/i)) {
        event.preventDefault()
        return true
      }
    },
    // The class of the placeholder (must match placeholder option markup)
    placeholderClass: "placeholder",
    // Template for the placeholder. Can be any valid jQuery input
    // e.g. a string, a DOM element.
    // The placeholder must have the class "placeholder"
    placeholder: '<li class="placeholder"></li>',
    // If true, the position of the placeholder is calculated on every mousemove.
    // If false, it is only calculated when the mouse is above a container.
    pullPlaceholder: true,
    // Specifies serialization of the container group.
    // The pair $parent/$children is either container/items or item/subcontainers.
    serialize: function ($parent, $children, parentIsContainer) {
      var result = $.extend({}, $parent.data())

      if(parentIsContainer)
        return [$children]
      else if ($children[0]){
        result.children = $children
      }

      delete result.subContainers
      delete result.sortable

      return result
    },
    // Set tolerance while dragging. Positive values decrease sensitivity,
    // negative values increase it.
    tolerance: 0
  }, // end group defaults
  containerGroups = {},
  groupCounter = 0,
  emptyBox = {
    left: 0,
    top: 0,
    bottom: 0,
    right:0
  },
  eventNames = {
    start: "touchstart.sortable mousedown.sortable",
    drop: "touchend.sortable touchcancel.sortable mouseup.sortable",
    drag: "touchmove.sortable mousemove.sortable",
    scroll: "scroll.sortable"
  },
  subContainerKey = "subContainers"

  /*
   * a is Array [left, right, top, bottom]
   * b is array [left, top]
   */
  function d(a,b) {
    var x = Math.max(0, a[0] - b[0], b[0] - a[1]),
    y = Math.max(0, a[2] - b[1], b[1] - a[3])
    return x+y;
  }

  function setDimensions(array, dimensions, tolerance, useOffset) {
    var i = array.length,
    offsetMethod = useOffset ? "offset" : "position"
    tolerance = tolerance || 0

    while(i--){
      var el = array[i].el ? array[i].el : $(array[i]),
      // use fitting method
      pos = el[offsetMethod]()
      pos.left += parseInt(el.css('margin-left'), 10)
      pos.top += parseInt(el.css('margin-top'),10)
      dimensions[i] = [
        pos.left - tolerance,
        pos.left + el.outerWidth() + tolerance,
        pos.top - tolerance,
        pos.top + el.outerHeight() + tolerance
      ]
    }
  }

  function getRelativePosition(pointer, element) {

    var offset = element.offset()
    return {
      left: pointer.left - offset.left,
      top: pointer.top - offset.top
    }
  }

  function sortByDistanceDesc(dimensions, pointer, lastPointer) {
    pointer = [pointer.left, pointer.top]
    lastPointer = lastPointer && [lastPointer.left, lastPointer.top]

    var dim,
    i = dimensions.length,
    distances = []

    while(i--){
      dim = dimensions[i]
      distances[i] = [i,d(dim,pointer), lastPointer && d(dim, lastPointer)]
    }
    distances = distances.sort(function  (a,b) {
      return b[1] - a[1] || b[2] - a[2] || b[0] - a[0]
    })

    // last entry is the closest
    return distances
  }

  function ContainerGroup(options) {
    this.options = $.extend({}, groupDefaults, options)
    this.containers = []

    if(!this.options.rootGroup){
      this.scrollProxy = $.proxy(this.scroll, this)
      this.dragProxy = $.proxy(this.drag, this)
      this.dropProxy = $.proxy(this.drop, this)
      this.placeholder = $(this.options.placeholder)

      if(!options.isValidTarget)
        this.options.isValidTarget = undefined
    }
  }

  ContainerGroup.get = function  (options) {
    if(!containerGroups[options.group]) {
      if(options.group === undefined)
        options.group = groupCounter ++

      containerGroups[options.group] = new ContainerGroup(options)
    }

    return containerGroups[options.group]
  }

  ContainerGroup.prototype = {
    dragInit: function  (e, itemContainer) {
      this.$document = $(itemContainer.el[0].ownerDocument)

      // get item to drag
      var closestItem = $(e.target).closest(this.options.itemSelector);
      // using the length of this item, prevents the plugin from being started if there is no handle being clicked on.
      // this may also be helpful in instantiating multidrag.
      if (closestItem.length) {
        this.item = closestItem;
        this.itemContainer = itemContainer;
        if (this.item.is(this.options.exclude) || !this.options.onMousedown(this.item, groupDefaults.onMousedown, e)) {
            return;
        }
        this.setPointer(e);
        this.toggleListeners('on');
        this.setupDelayTimer();
        this.dragInitDone = true;
      }
    },
    drag: function  (e) {
      if(!this.dragging){
        if(!this.distanceMet(e) || !this.delayMet)
          return

        this.options.onDragStart(this.item, this.itemContainer, groupDefaults.onDragStart, e)
        this.item.before(this.placeholder)
        this.dragging = true
      }

      this.setPointer(e)
      // place item under the cursor
      this.options.onDrag(this.item,
                          getRelativePosition(this.pointer, this.item.offsetParent()),
                          groupDefaults.onDrag,
                          e)

      var p = this.getPointer(e),
      box = this.sameResultBox,
      t = this.options.tolerance

      if(!box || box.top - t > p.top || box.bottom + t < p.top || box.left - t > p.left || box.right + t < p.left)
        if(!this.searchValidTarget()){
          this.placeholder.detach()
          this.lastAppendedItem = undefined
        }
    },
    drop: function  (e) {
      this.toggleListeners('off')

      this.dragInitDone = false

      if(this.dragging){
        // processing Drop, check if placeholder is detached
        if(this.placeholder.closest("html")[0]){
          this.placeholder.before(this.item).detach()
        } else {
          this.options.onCancel(this.item, this.itemContainer, groupDefaults.onCancel, e)
        }
        this.options.onDrop(this.item, this.getContainer(this.item), groupDefaults.onDrop, e)

        // cleanup
        this.clearDimensions()
        this.clearOffsetParent()
        this.lastAppendedItem = this.sameResultBox = undefined
        this.dragging = false
      }
    },
    searchValidTarget: function  (pointer, lastPointer) {
      if(!pointer){
        pointer = this.relativePointer || this.pointer
        lastPointer = this.lastRelativePointer || this.lastPointer
      }

      var distances = sortByDistanceDesc(this.getContainerDimensions(),
                                         pointer,
                                         lastPointer),
      i = distances.length

      while(i--){
        var index = distances[i][0],
        distance = distances[i][1]

        if(!distance || this.options.pullPlaceholder){
          var container = this.containers[index]
          if(!container.disabled){
            if(!this.$getOffsetParent()){
              var offsetParent = container.getItemOffsetParent()
              pointer = getRelativePosition(pointer, offsetParent)
              lastPointer = getRelativePosition(lastPointer, offsetParent)
            }
            if(container.searchValidTarget(pointer, lastPointer))
              return true
          }
        }
      }
      if(this.sameResultBox)
        this.sameResultBox = undefined
    },
    movePlaceholder: function  (container, item, method, sameResultBox) {
      var lastAppendedItem = this.lastAppendedItem
      if(!sameResultBox && lastAppendedItem && lastAppendedItem[0] === item[0])
        return;

      item[method](this.placeholder)
      this.lastAppendedItem = item
      this.sameResultBox = sameResultBox
      this.options.afterMove(this.placeholder, container, item)
    },
    getContainerDimensions: function  () {
      if(!this.containerDimensions)
        setDimensions(this.containers, this.containerDimensions = [], this.options.tolerance, !this.$getOffsetParent())
      return this.containerDimensions
    },
    getContainer: function  (element) {
      return element.closest(this.options.containerSelector).data(pluginName)
    },
    $getOffsetParent: function  () {
      if(this.offsetParent === undefined){
        var i = this.containers.length - 1,
        offsetParent = this.containers[i].getItemOffsetParent()

        if(!this.options.rootGroup){
          while(i--){
            if(offsetParent[0] != this.containers[i].getItemOffsetParent()[0]){
              // If every container has the same offset parent,
              // use position() which is relative to this parent,
              // otherwise use offset()
              // compare #setDimensions
              offsetParent = false
              break;
            }
          }
        }

        this.offsetParent = offsetParent
      }
      return this.offsetParent
    },
    setPointer: function (e) {
      var pointer = this.getPointer(e)

      if(this.$getOffsetParent()){
        var relativePointer = getRelativePosition(pointer, this.$getOffsetParent())
        this.lastRelativePointer = this.relativePointer
        this.relativePointer = relativePointer
      }

      this.lastPointer = this.pointer
      this.pointer = pointer
    },
    distanceMet: function (e) {
      var currentPointer = this.getPointer(e)
      return (Math.max(
        Math.abs(this.pointer.left - currentPointer.left),
        Math.abs(this.pointer.top - currentPointer.top)
      ) >= this.options.distance)
    },
    getPointer: function(e) {
      var o = e.originalEvent || e.originalEvent.touches && e.originalEvent.touches[0]
      return {
        left: e.pageX || o.pageX,
        top: e.pageY || o.pageY
      }
    },
    setupDelayTimer: function () {
      var that = this
      this.delayMet = !this.options.delay

      // init delay timer if needed
      if (!this.delayMet) {
        clearTimeout(this._mouseDelayTimer);
        this._mouseDelayTimer = setTimeout(function() {
          that.delayMet = true
        }, this.options.delay)
      }
    },
    scroll: function  (e) {
      this.clearDimensions()
      this.clearOffsetParent() // TODO is this needed?
    },
    toggleListeners: function (method) {
      var that = this,
      events = ['drag','drop','scroll']

      $.each(events,function  (i,event) {
        that.$document[method](eventNames[event], that[event + 'Proxy'])
      })
    },
    clearOffsetParent: function () {
      this.offsetParent = undefined
    },
    // Recursively clear container and item dimensions
    clearDimensions: function  () {
      this.traverse(function(object){
        object._clearDimensions()
      })
    },
    traverse: function(callback) {
      callback(this)
      var i = this.containers.length
      while(i--){
        this.containers[i].traverse(callback)
      }
    },
    _clearDimensions: function(){
      this.containerDimensions = undefined
    },
    _destroy: function () {
      containerGroups[this.options.group] = undefined
    }
  }

  function Container(element, options) {
    this.el = element
    this.options = $.extend( {}, containerDefaults, options)

    this.group = ContainerGroup.get(this.options)
    this.rootGroup = this.options.rootGroup || this.group
    this.handle = this.rootGroup.options.handle || this.rootGroup.options.itemSelector

    var itemPath = this.rootGroup.options.itemPath
    this.target = itemPath ? this.el.find(itemPath) : this.el

    this.target.on(eventNames.start, this.handle, $.proxy(this.dragInit, this))

    if(this.options.drop)
      this.group.containers.push(this)
  }

  Container.prototype = {
    dragInit: function  (e) {
      var rootGroup = this.rootGroup

      if( !this.disabled &&
          !rootGroup.dragInitDone &&
          this.options.drag &&
          this.isValidDrag(e)) {
        rootGroup.dragInit(e, this)
      }
    },
    isValidDrag: function(e) {
      return e.which == 1 ||
        e.type == "touchstart" && e.originalEvent.touches.length == 1
    },
    searchValidTarget: function  (pointer, lastPointer) {
      var distances = sortByDistanceDesc(this.getItemDimensions(),
                                         pointer,
                                         lastPointer),
      i = distances.length,
      rootGroup = this.rootGroup,
      validTarget = !rootGroup.options.isValidTarget ||
        rootGroup.options.isValidTarget(rootGroup.item, this)

      if(!i && validTarget){
        rootGroup.movePlaceholder(this, this.target, "append")
        return true
      } else
        while(i--){
          var index = distances[i][0],
          distance = distances[i][1]
          if(!distance && this.hasChildGroup(index)){
            var found = this.getContainerGroup(index).searchValidTarget(pointer, lastPointer)
            if(found)
              return true
          }
          else if(validTarget){
            this.movePlaceholder(index, pointer)
            return true
          }
        }
    },
    movePlaceholder: function  (index, pointer) {
      var item = $(this.items[index]),
      dim = this.itemDimensions[index],
      method = "after",
      width = item.outerWidth(),
      height = item.outerHeight(),
      offset = item.offset(),
      sameResultBox = {
        left: offset.left,
        right: offset.left + width,
        top: offset.top,
        bottom: offset.top + height
      }
      if(this.options.vertical){
        var yCenter = (dim[2] + dim[3]) / 2,
        inUpperHalf = pointer.top <= yCenter
        if(inUpperHalf){
          method = "before"
          sameResultBox.bottom -= height / 2
        } else
          sameResultBox.top += height / 2
      } else {
        var xCenter = (dim[0] + dim[1]) / 2,
        inLeftHalf = pointer.left <= xCenter
        if(inLeftHalf){
          method = "before"
          sameResultBox.right -= width / 2
        } else
          sameResultBox.left += width / 2
      }
      if(this.hasChildGroup(index))
        sameResultBox = emptyBox
      this.rootGroup.movePlaceholder(this, item, method, sameResultBox)
    },
    getItemDimensions: function  () {
      if(!this.itemDimensions){
        this.items = this.$getChildren(this.el, "item").filter(
          ":not(." + this.group.options.placeholderClass + ", ." + this.group.options.draggedClass + ")"
        ).get()
        setDimensions(this.items, this.itemDimensions = [], this.options.tolerance)
      }
      return this.itemDimensions
    },
    getItemOffsetParent: function  () {
      var offsetParent,
      el = this.el
      // Since el might be empty we have to check el itself and
      // can not do something like el.children().first().offsetParent()
      if(el.css("position") === "relative" || el.css("position") === "absolute"  || el.css("position") === "fixed")
        offsetParent = el
      else
        offsetParent = el.offsetParent()
      return offsetParent
    },
    hasChildGroup: function (index) {
      return this.options.nested && this.getContainerGroup(index)
    },
    getContainerGroup: function  (index) {
      var childGroup = $.data(this.items[index], subContainerKey)
      if( childGroup === undefined){
        var childContainers = this.$getChildren(this.items[index], "container")
        childGroup = false

        if(childContainers[0]){
          var options = $.extend({}, this.options, {
            rootGroup: this.rootGroup,
            group: groupCounter ++
          })
          childGroup = childContainers[pluginName](options).data(pluginName).group
        }
        $.data(this.items[index], subContainerKey, childGroup)
      }
      return childGroup
    },
    $getChildren: function (parent, type) {
      var options = this.rootGroup.options,
      path = options[type + "Path"],
      selector = options[type + "Selector"]

      parent = $(parent)
      if(path)
        parent = parent.find(path)

      return parent.children(selector)
    },
    _serialize: function (parent, isContainer) {
      var that = this,
      childType = isContainer ? "item" : "container",

      children = this.$getChildren(parent, childType).not(this.options.exclude).map(function () {
        return that._serialize($(this), !isContainer)
      }).get()

      return this.rootGroup.options.serialize(parent, children, isContainer)
    },
    traverse: function(callback) {
      $.each(this.items || [], function(item){
        var group = $.data(this, subContainerKey)
        if(group)
          group.traverse(callback)
      });

      callback(this)
    },
    _clearDimensions: function  () {
      this.itemDimensions = undefined
    },
    _destroy: function() {
      var that = this;

      this.target.off(eventNames.start, this.handle);
      this.el.removeData(pluginName)

      if(this.options.drop)
        this.group.containers = $.grep(this.group.containers, function(val){
          return val != that
        })

      $.each(this.items || [], function(){
        $.removeData(this, subContainerKey)
      })
    }
  }

  var API = {
    enable: function() {
      this.traverse(function(object){
        object.disabled = false
      })
    },
    disable: function (){
      this.traverse(function(object){
        object.disabled = true
      })
    },
    serialize: function () {
      return this._serialize(this.el, true)
    },
    refresh: function() {
      this.traverse(function(object){
        object._clearDimensions()
      })
    },
    destroy: function () {
      this.traverse(function(object){
        object._destroy();
      })
    }
  }

  $.extend(Container.prototype, API)

  /**
   * jQuery API
   *
   * Parameters are
   *   either options on init
   *   or a method name followed by arguments to pass to the method
   */
  $.fn[pluginName] = function(methodOrOptions) {
    var args = Array.prototype.slice.call(arguments, 1)

    return this.map(function(){
      var $t = $(this),
      object = $t.data(pluginName)

      if(object && API[methodOrOptions])
        return API[methodOrOptions].apply(object, args) || this
      else if(!object && (methodOrOptions === undefined ||
                          typeof methodOrOptions === "object"))
        $t.data(pluginName, new Container($t, methodOrOptions))

      return this
    });
  };

}(jQuery, window, 'sortable');
function reloadPage(url) {
    'use strict';
    window.location.href = url;
}

function convert2id(idString, expectedArrayLength) {

    var idArray = idString.split('_');

    if (idArray.length === expectedArrayLength) {
        return idArray[1];
    }

    return 0;

}

//
// Settings
PNotify.prototype.options.styling = 'bootstrap3';
PNotify.prototype.options.styling = 'fontawesome';
var stack_bottomright = {'dir1': 'up', 'dir2': 'left', 'push': 'top'};

function showMultipleNotifies(data_array, status) {
    'use strict';

    if (typeof data_array === 'undefined') {
        return;
    }

    $.each(data_array, function (key, val) {
        showNotify(val, '', status);
    });

}

function showNotify(var_title, var_text, alert_type) {
    'use strict';

    new PNotify({
        title : var_title,
        text : var_text,
        addclass : 'stack-bottomright',
        stack : stack_bottomright,
        type : alert_type
    });

}

function showErrorNofity() {
    'use strict';

    var errors = $('.errorNotification').length;
    if (errors > 0) {

        $('.errorNotification').each(function (index, value) {
            showNotify($(this).val(), '', 'error');
        });

    }

}

function showSuccessNofity() {
    'use strict';

    var errors = $('.successNotification').length;
    if (errors > 0) {

        $('.successNotification').each(function (index, value) {
            showNotify($(this).val(), '', 'success');
        });

    }

}

$(document).ready(function () {
    'use strict';
    showErrorNofity();
    showSuccessNofity();
});

function updateNotifyStatus(notify_id) {
    'use strict';

    $.post(
        'ajax.php?site=notify',
        {
            action : 'seen',
            notify_id : notify_id
        },
        function (data, status) {}
    );

}

function checkbox(cup_id) {
    'use strict';
    var checkbox = document.getElementById('checkin_box').checked;
    if (checkbox === true) {
        $('#enter_cup_container').html('<a class="list-group-item alert-info center" href="index.php?site=cup&amp;action=checkin&amp;id=' + cup_id + '">Team Check-In</a>');
    } else {
        $('#enter_cup_container').html('<span class="list-group-item alert-info center">Team Check-In</span>');
    }
    return true;
}

function showSelect(value) {
    'use strict';
    window.location.href = value + '#content';
    return true;
}

var timerHandle;

function countdown(timeleft, div_id) {
    'use strict';

    var timeleft_local,
        days,
        hours,
        minutes,
        seconds;

    timeleft_local = timeleft;

    days = Math.floor(timeleft_local / (24 * 3600));
    timeleft_local = timeleft_local % (24 * 3600);
    hours = Math.floor(timeleft_local / (60 * 60));
    timeleft_local = timeleft_local % (60 * 60);
    minutes = Math.floor(timeleft_local / 60);
    timeleft_local = timeleft_local % 60;
    seconds = timeleft_local % 60;

    if (days < 0) {
        days = 0;
    }

    if (hours < 0) {
        hours = 0;
    }

    if (minutes < 0) {
        minutes = 0;
    }

    if (seconds < 0) {
        seconds = 0;
    }

    $('#' + div_id + 'Days').html(days);
    $('#' + div_id + 'Hours').html(hours);
    $('#' + div_id + 'Minutes').html(minutes);
    $('#' + div_id + 'Seconds').html(seconds);

    timerHandle = setTimeout('countdown(' + --timeleft + ', \'' + div_id + '\')', 1000);

}

$(function () {
    'use strict';

    $('[data-toggle="tooltip"]').tooltip();

});

function isUrl(url) {
    'use strict';
    return /^(https?|s?ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(url);
}

function isEmail(email) {
    'use strict';
    var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    return regex.test(email);
}