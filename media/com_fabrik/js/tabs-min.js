var Tabs=new Class({initialize:function(c,b,a){this.editable=a;this.iconGen=new IconGenerator({scale:0.5});this.el=document.id(c);this.tabs=$H({});this.build(b)},build:function(c){Fabrik.fireEvent("fabrik.history.off",this);if(this.editable){var b=new Element("a",{href:"#",events:{click:function(a){this.addWindow(a)}.bind(this)}});art=this.iconGen.create(icon.plus,{fill:{color:["#40B53E","#378F36"]}});art.inject(b);this.el.adopt(new Element("li",{"class":"add",events:{click:function(a){this.addWindow(a)}.bind(this)}}).adopt([new Element("span").set("text","add"),b]))}c.each(function(a){this.add(a)}.bind(this));this.setActive(c[0]);var d=function(){Fabrik.fireEvent("fabrik.history.on",this)};d.delay(500)},remove:function(c){var d;if(typeOf(c)==="event"){d=c.target.getParent("li").getElement("span").get("text").trim();c.stop()}else{d=c}if(confirm("Delete tab?")){if(this.tabs.getLength()<=1){alert("you can not remove all tabs");return}var b=this.tabs[d];Fabrik.fireEvent("fabrik.tab.remove",[this,b]);this.tabs.erase(d);b.destroy();var a=this.tabs.getKeys()[0];this.setActive(this.tabs[a])}},addWindow:function(b){var d=new Element("form");d.adopt(new Element("input",{name:"label",events:{keydown:function(c){if(c.key==="enter"){c.stop()}}}}),new Element("br"),new Element("input",{"class":"button",type:"button",events:{click:function(f){var c=f.target.getParent().getElement("input[name=label]").get("value");if(c===""){alert("please supply a tab label");return false}this.add(c);Fabrik.Windows[this.windowopts.id].close()}.bind(this)},value:"add"}));this.windowopts={id:"addTab",type:"modal",title:"Add",content:d,width:200,height:200,minimizable:false,collapsible:true};var a=Fabrik.getWindow(this.windowopts)},add:function(d){var b=new Element("li",{events:{click:function(a){this.setActive(b)}.bind(this),mouseover:function(a){Fabrik.fireEvent("fabrik.tab.hover",[d])}}});b.adopt(new Element("span").set("text",d+" "));var c=new Element("a",{href:"#",events:{click:function(a){this.remove(a)}.bind(this)}});if(this.editable){art=this.iconGen.create(icon.cross);art.inject(c);b.adopt(c)}b.store("ref",d);if(this.editable){var e=this.el.getElement("li.add");b.inject(e,"before")}else{b.inject(this.el,"inside")}this.setActive(b);this.tabs[d]=b;Fabrik.fireEvent("fabrik.history.add",[this,this.remove,d,this.add,d]);Fabrik.fireEvent("fabrik.tab.add",[this,d])},setActive:function(b){var c=typeOf(b)==="string"?b:b.retrieve("ref");var d=b;Fabrik.fireEvent("fabrik.tab.click",c);this.tabs.each(function(a){a.removeClass("active");a.addClass("inactive");if(a.retrieve("ref")===c){d=a}});d.addClass("active");d.removeClass("inactive")},reorder:function(){}});