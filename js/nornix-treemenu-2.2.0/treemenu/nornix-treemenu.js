/*
Nornix TreeMenu JavaScript with common routines  <http://treemenu.nornix.com/>
Version: 2.2.0 (2008-03-11) (common routines 0.5 (2008-03-11))
Build time: 2008-03-11 19:02 UTC
*/
if(!Nornix)
{
    var Nornix={
        events:{},cookies:{},css:{},dom:{},util:{}
    }
}
if(document.addEventListener){
    Nornix.events.add=function(D,C,B,A){D.addEventListener(C,B,A)};
    Nornix.events.remove=function(D,C,B,A){D.removeEventListener(C,B,A)}
}
else
{
    if(document.attachEvent){
        Nornix.events.add=function(C,B,A){
            C["e"+B+A]=A;
            C[B+A]=function(){
                var D=window.event;
                D.target=window.event.srcElement;
                C["e"+B+A](D)};
            C.attachEvent("on"+B,C[B+A])};
        Nornix.events.remove=function(C,B,A){
            C.detachEvent("on"+B,C[B+A]);
            C[B+A]=null;
            C["e"+B+A]=null}
    }
    else
    {
        Nornix.events.add=Function;
        Nornix.events.remove=Function
    }
}
Nornix.events.cancel=function(A,B){
    A.returnValue=false;
    if(A.preventDefault){
        A.preventDefault()
    }
    if(B){
        A.cancelBubble=true;
        if(A.stopPropagation){
            A.stopPropagation()
        }
    }
};
Nornix.events.delayedInit=function(E,D,A){
    var B;
    if(A===undefined){
        A=10
    }
    var C=window.setInterval(function(){
        if(B=document.getElementById(E)){
            window.clearInterval(C);D(B)
        }
    },A)
};
Nornix.cookies.create=function(C,D,E){
    var A;
    if(E){
        var B=new Date();
        B.setTime(B.getTime()+(E*24*60*60*1000));
        A="; expires="+B.toGMTString()
    }
    else{
        A=""
    }
    document.cookie=C+"="+D+A+"; path=/"
};
Nornix.cookies.read=function(B){
    var D=B+"=";
    var A=document.cookie.split(";"),C=0,E;
    while(E=A[C++]){
        E=Nornix.util.trim(E);
        if(E.indexOf(D)===0){
            return E.substring(D.length,E.length)
        }
    }return""
};
Nornix.cookies.erase=function(A){
    createCookie(A,"",-1)
};
Nornix.css.swap=function(D,E,F){
    if(!D){
        return
    }
    if(!D.className||D.className.length===0){
        D.className=F?F:"";
        return
    }
    var A=D.className.split(" "),C=0,B;
    while(B=A[C++]){
        if(B===E){
            if(F){
                A[C-1]=F;D.className=A.join(" ");
                return
            }else{
                delete A[C-1];
                D.className=A.join(" ");
                return
            }
        }else{
            if(B===F){
                D.className=A.join(" ");
                return
            }
        }
    }
    if(F){
        A[A.length]=F
    }
    D.className=A.join(" ")
};
Nornix.css.add=function(B,A){
    return Nornix.css.swap(B,null,A)
};
Nornix.css.remove=function(B,A){
    return Nornix.css.swap
}();
Nornix.css.contains=function(E,D){
    if(!E||!E.className){
        return false
    }
    var A=E.className.split(" "),C=0,B;
    while(B=A[C++]){
        if(B===D){
            return true
        }
    }
    return false
};
Nornix.css.getPos=function(A){
    var B={x:A.offsetLeft||0,y:A.offsetTop||0};
    while(A=A.offsetParent){
        B.x+=A.offsetLeft||0;B.y+=A.offsetTop||0
    }
    return B
};
Nornix.css.getProperty=function(B,A){
    if(window.getComputedStyle){
        return function(D,C){
            return window.getComputedStyle(D,"").getPropertyValue(C)
        }
    }return function(D,C){
        return D.currentStyle?D.currentStyle[Nornix.css.prop2Js(C)]:null
    }
}();
Nornix.css.prop2Js=function(E){
    var A=E.split("-");
    if(A.length>1){
        var D=A[0],C=1,B;
        while(B=A[C++]){
            D+=B.charAt(0).toUpperCase()+B.substr(1)
        }
        return D
    }
    return E
};
Nornix.dom.live2copy=function(D){
    var C=[],A=0,B;
    while(B=D[A++]){
        C[C.length]=B
    }
    return C
};
Nornix.dom.getTextContent=function(A){
    if(typeof A.textContent!="undefined"){
        return A.textContent
    }else{
        return A.innerText
    }
};
Nornix.dom.imagePreload=function(D,C){
    var B=0,A;
    while(A=D[B++]){
        (new Image()).src=C+A
    }
};
Nornix.dom.findChildOfType=function(B,F,C,A){
    var E;
    if(!A){
        var D=0;
        while(E=B.childNodes[D++]){
            if(Nornix.dom.eqNodeName(E,F)){
                return C(E)
            }
        }
    }else{
        var D=B.childNodes.length-1;
        while(E=B.childNodes[D--]){
            if(Nornix.dom.eqNodeName(E,F)){
                return C(E)
            }
        }
    }
};
Nornix.dom.eqNodeName=function(A,B){
    if(A&&A.nodeName&&A.nodeName.toLowerCase()===B){
        return true
    }
    return false
};
Nornix.util.trim=function(A){
    return A.replace(/^\s*|\s*$/g,"")
};
Nornix.util.isIe=document.all&&window.opera===undefined;
Nornix.TreeMenu=function(E,R){
    var Q=this;
    var N=E?E:"menu";
    var b="tree"+N;
    var P=Nornix.cookies.read(b);
    var A=(R===false)?false:true;
    var V=window.location+"#";
    var I=null;
    var B=null;
    Q.openPattern=/(^| )open( |$)/;
    this.openAll=W;
    this.closeAll=L;
    this.start=function(){
        Nornix.events.delayedInit(N,Z);
        if(Q.config.preloadImages&&!Nornix.cookies.read("preImg")){
            Nornix.dom.imagePreload(Q.config.preloadImages,Q.config.imagePath);
            Nornix.cookies.create("preImg","x")
        }
    };
    function Z(e){
        if(!document.getElementById||!document.createElement){
            return
        }
        Q.menu=e;
        Q.menuElements=Nornix.dom.live2copy(Q.menu.getElementsByTagName("li"));
        if(Q.config.openCloseAll){T()}
        M();
        Y();
        d()}
    function d(){
        i();
        function i(){
            if(Q.config.menuLinkElement){
                Nornix.events.add(document.getElementById(Q.config.menuLinkElement),"click",e);
                if(Nornix.util.isIe){
                    Nornix.events.add(document.getElementById(Q.config.menuLinkElement),"focus",h)
                }
            }
            if(A){
                S(Q.menu);
                A=false
            }
            Nornix.events.add(Q.menu,"click",g,true);
            Nornix.events.add(Q.menu,"keydown",f,true);
            Nornix.events.add(window,"unload",c)
        }
        function e(j){
            H(Q.menu);
            Nornix.events.cancel(j);
            return false
        }
        function h(j){
            if(j.altKey){
                H(Q.menu)
            }
        }
        function g(l){
            var j=l.target;
            var prub = j.nodeName;
            switch(j.className){
                case"closeTree":
                    L();
                    return false;
                case"openTree":
                    W();
                    return false
           }
           var k=j.parentNode;
           if(!k){
               return true
           }
           if(Nornix.dom.eqNodeName(j,"span")){
               U(k);
               return
           }
           if(!J(j)){
               G(j)
           }
           if(a(k)){
               if(Q.hooks.dynamicFolderLinks&&!Q.hooks.dynamicFolderLinks(j)){
                   Nornix.events.cancel(l);
                   return false
               }
               if(J(j)){
                   U(k);
                   Nornix.events.cancel(l);
                   return false
               }
           }else{
               if(Q.hooks.dynamicDocumentLinks&&!Q.hooks.dynamicDocumentLinks(j)){
                   Nornix.events.cancel(l);
                   return false
               }
           }
           return true
       }
       function f(s){
           var m,u,q,r,n,v,t,k=s.target,j=k.parentNode,l;
           switch(true){
               case Nornix.css.contains(k,"root"):
                   m=true;
                   break;
               case Nornix.css.contains(k,"closeTree"):
                   r=true;
                   t=true;
                   break;
               case Nornix.css.contains(k,"openTree"):
                   n=true;
                   t=true;
                   break;
               case Nornix.css.contains(j,"folder"):
                   u=true;
                   v=true;
                   break;
               case Nornix.css.contains(j,"document"):
                   q=true;
                   v=true;
                   break;
               default:
                   return true
           }
           var w=s.keyCode!==null?s.keyCode:s.which;
           if(w===56){
               W()
           }else{
               if(w===57){
                   L(k);
                   if(!k.offsetParent){
                       Nornix.dom.findChildOfType(Q.menu,"a",function(o){K(o)})
                   }
               }
               else{
                   if(u&&(w==32||(w==13&&J(k)))){
                       U(j)
                   }
                   else{
                       if(m&&(w===40||((w===39)&&!K(k.nextSibling)))&&(l=j.lastChild.firstChild)){
                           H(l)
                       }else{
                           if(v&&(w===40)){
                               if(q||!O(j)){
                                   if(l=j.nextSibling){
                                       H(l)
                                   }
                                   else{
                                       if(u&&(l=k.nextSibling.firstChild)){
                                           U(j);
                                           H(l)
                                       }
                                       else{
                                           l=j;
                                           while(l&&l!==Q.menu){
                                               l=l.parentNode.parentNode;
                                               if(l.nextSibling){
                                                   H(l.nextSibling);
                                                   break
                                               }
                                           }
                                       }
                                   }
                               }else{
                                   if(l=j.nextSibling){
                                       H(l)
                                   }else{
                                       if(l=k.nextSibling.firstChild){
                                           H(l)
                                       }
                                   }
                               }
                           }else{
                               if(v&&(w===38)){
                                   if(l=j.previousSibling){
                                       H(l)
                                   }else{
                                       if(l=j.parentNode.parentNode){
                                           H(l)
                                       }
                                   }
                               }else{
                                   if(v&&(w===39)){
                                       if(q&&(l=j.nextSibling)){
                                           H(l)
                                       }else{
                                           if(k.nextSibling&&(l=k.nextSibling.firstChild)){
                                               if(O(j)){
                                                   H(l)
                                               }else{
                                                   U(j);
                                                   H(l)
                                               }
                                           }
                                       }
                                   }else{
                                       if(v&&(w===37)){
                                           if(l=j.parentNode.parentNode){
                                               H(l)
                                           }if(u&&O(j)){
                                               U(j)
                                           }
                                       }else{
                                           if(q&&(w===32)){

                                           }else{
                                               if(w===27&&Q.config.contentElement){
                                                   window.location.hash=Q.config.contentElement
                                               }else{
                                                   if(t&&(w===37||w===38)){
                                                       K(k.previousSibling)
                                                   }else{
                                                       if(r&&(w===39||w===40)){
                                                           K(k.nextSibling)
                                                       }else{
                                                           if(n&&(w===39||w===40)){
                                                               H(k.nextSibling.firstChild)
                                                           }else{
                                                               return true
                                                           }
                                                       }
                                                   }
                                               }
                                           }
                                       }
                                   }
                               }
                           }
                       }
                   }
               }
           }
           Nornix.events.cancel(s);
           return false
       }
   }
   function M(){
       function f(i){
           if(Q.config.dynamicClasses){
               B=i.href;
               i.removeAttribute("href")
           }
           if(!i.href){
               I=i;
               i.href="javascript:;";
               C(i)
           }
       }
       var e=[];
       var l,r,o,s,j;
       var p=Q.menuElements;
       var k=document.createElement("span");
       var q;
       k.title=Q.texts.openFolderTitle;
       var h=0;
       if(Q.config.dynamicClasses){
           var n=window.location.href;
           Nornix.dom.findChildOfType(Q.menu,"a",function(i){
               Nornix.css.swap(i,null,"root");
               if(i.href===n){
                   f(i)
               }
           });
           l=0;
           while(r=p[l++]){
               o=r.firstChild;s=a(r);
               var m;
               if(s){
                   m="folder closed";
                   e[e.length]=r;
                   q=k.cloneNode(false);
                   r.insertBefore(q,o);
                   j=P.charAt(h++);
                   if(j&&j==="-"){
                       m="folder open";
                       r.firstChild.title=Q.texts.closeFolderTitle
                   }
               }else{
                   m="document"
               }
               if(A){
                   Nornix.dom.findChildOfType(r.parentNode,"li",function(i){
                       if(r===i){m+=" last"
                       }
                   },true)
               }else{
                   if(r===r.parentNode.lastChild){
                       m+=" last"
                   }
               }
               if(o.href==n){
                   f(o);
                   if(s){
                       m="folder open";
                       r.firstChild.title=Q.texts.closeFolderTitle
                   }
                   var g=r.parentNode.parentNode;
                   while(g&&g!=Q.menu){
                       F(g);
                       g=g.parentNode.parentNode
                   }
               }
               r.className=m
           }
       }else{
           l=0;
           while(r=p[l++]){
               o=r.firstChild;
               s=a(r);
               if(J(o)){
                   f(o)
               }
               if(s){
                   e[e.length]=r;
                   q=k.cloneNode(false);
                   r.insertBefore(q,o);
                   j=P.charAt(h++);
                   if(j&&j==="-"){
                       F(r)
                   }
               }
           }
           l=0;
           while(o=Q.menu.childNodes[l++]){
               if(Nornix.dom.eqNodeName(o,"a")){
                   f(o);
                   break
               }
           }
       }
       Q.menuFolders=e
   }
   function G(e){
       if(I){
           X(I)
       }
       C(e);
       I=e
   }
   function C(e){
       Nornix.css.swap(e,null,"current");
       if(Q.config.markCurrentItem){
           e.insertBefore(document.createElement("span"),e.firstChild)
       }
   }
   function X(e){
       if(B){
           I.href=B;
           B=null
       }
       Nornix.css.swap(I,"current",null);
       if(Q.config.markCurrentItem){
           I.removeChild(I.firstChild)
       }
   }
   function c(){
       var g="",f=0,e;
       var h=Q.menuFolders;
       while(e=h[f++]){
           if(O(e)){g+="-"}else{g+="+"}
       }
       Nornix.cookies.create(b,g)
   }
   function J(e){
       if(e.href&&(e.href==V||e.href==="javascript:;")){
           return true
       }return !e.href
   }
   function Y(){}
   if(Nornix.util.isIe){
       Y=function(){
           Q.menu.style.position="absolute";
           Q.menu.style.position="relative";
           try{
               window.event.srcElement.focus()
           }
           catch(e){}
       }
   }else{
       Y=function(){}
   }
   function S(k){
       var g=0,h;
       while(h=k.childNodes[g]){
           switch(h.nodeType){
               case 1:
                   var e=0,f;
                   while(f=h.childNodes[e]){
                       switch(f.nodeType){
                           case 1:
                               S(f);
                               break;
                           case 3:
                               if(!/\S/.test(f.nodeValue)){
                                   h.removeChild(f);continue
                               }
                               break;
                           case 8:
                               h.removeChild(f);
                               continue
                       }
                       e++
                   }
                   break;
               case 3:
                   if(!/\S/.test(h.nodeValue)){
                       k.removeChild(h);
                       continue
                   }
                   break;
               case 8:
                   k.removeChild(h);
                   continue
           }
           g++
       }
   }
   function a(e){
       if(A){
           return Nornix.dom.findChildOfType(e,"ul",function(f){
               return true
           })
       }else{
           return e.childNodes.length>1
       }
   }
   function U(e){
       if(!O(e)){F(e)}else{D(e)} Y()
   }
   function F(e){
       Nornix.css.swap(e,"closed","open");
       e.firstChild.title=Q.texts.closeFolderTitle
   }
   function D(e){
       Nornix.css.swap(e,"open","closed");
       e.firstChild.title=Q.texts.openFolderTitle
   }
   function L(){
       var e=0,f=Q.menuFolders;
       while(li=f[e++]){D(li)}Y()}
   function W(){
       var e=0,f=Q.menuFolders;
       while(li=f[e++]){F(li)}Y()}
   function H(e){
       if(!e||!e.firstChild){
           return false
       }
       var f=e.firstChild;
       if(K(f)){
           return true
       }
       if(!f.nextSibling||e===Q.menu){
           return false
       }
       f=f.nextSibling;
       return K(f)
   }
   function K(e){
       if(Nornix.dom.eqNodeName(e,"a")){
           e.focus();
           return true
       }
       return false
   }
   function O(e){
       return e.className.search(Q.openPattern)!==-1
   }
   function T(){
       var e=document.createElement("a");
       e.href="javascript:;";
       var f=e.cloneNode(false);
       f.className="closeTree";
       f.title=Q.texts.closeTreeTitle;
       Q.menu.insertBefore(f,Q.menu.firstChild.nextSibling);
       f=e.cloneNode(false);
       f.className="openTree";
       f.title=Q.texts.openTreeTitle;
       Q.menu.insertBefore(f,Q.menu.firstChild.nextSibling.nextSibling)
   }
};
Nornix.TreeMenu.prototype.config={
   dynamicClasses:true,openCloseAll:true,markCurrentItem:false,contentElement:false,menuLinkElement:false,preloadImages:["home-icon.png","close-icon.png","open-icon.png","plus-node.png","minus-node.png","folder-closed-icon.png","doc-node-icon.png","folder-open-icon.png","treemenu-line.png","treemenu-current.png"],imagePath:"/style/nornix-"
};
Nornix.TreeMenu.prototype.hooks={
   dynamicDocumentLinks:false,dynamicFolderLinks:false
};
Nornix.TreeMenu.prototype.texts={
   closeTreeTitle:"cerrar todas las carpetas",openTreeTitle:"abrir todas las carpetas",closeFolderTitle:"cerrar carpeta",openFolderTitle:"abrir carpeta"
};

var treemenu = new Nornix.TreeMenu();
treemenu.start();
