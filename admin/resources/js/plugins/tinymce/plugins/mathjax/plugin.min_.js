/**
 * editor_plugin_src.js
 *
 * Copyright 2009, Etienne Florent (contributing)
 * Released under LGPL License.
 *
 * License: http://tinymce.moxiecode.com/license
 * Contributing: http://tinymce.moxiecode.com/contributing
 */

//@TODO gérer le passage en mode plein écran lorsqu'une formule est en cours d'édition.

(function() {
    //Les différentes classes utilisées pour le rendu et autre ...
    var cmj = "mcemathjax",  cmji = "mcemathinline", cmjd = "mcemathdisplaystyle", cmjv ="mcemathv", cmje = "mcemathedit",
    each = tinymce.each, Event = tinymce.dom.Event,// MathJax = null, queue = null;
    delim = []; delim["{"] = "}"; delim["("]=")"; delim["["]="]",
    
    texCommands = ["above","abovewithdelims","acute","aleph","alpha","amalg","And","angle","approx","approxeq","arccos","arcsin","arctan","arg","array","Arrowvert","arrowvert","ast","asymp","atop","atopwithdelims",
    "backepsilon","backprime","backsim","backsimeq","backslash","backslash","bar","barwedge","Bbb","Bbbk","because","begin","beta","beth","between","bf","Big","big","bigcap","bigcirc","bigcup","Bigg","bigg","Biggl","biggl","Biggm","biggm","Biggr","biggr","Bigl","bigl","Bigm","bigm","bigodot","bigoplus","bigotimes","Bigr","bigr","bigsqcup","bigstar","bigtriangledown","bigtriangleup","biguplus","bigvee","bigwedge","binom","blacklozenge","blacksquare","blacktriangle","blacktriangledown","blacktriangleleft","blacktriangleright","bmod","boldsymbol","bot","bowtie","Box","boxdot","boxed","boxminus","boxplus","boxtimes","brace","bracevert","brack","breve","buildrel","bullet","Bumpeq","bumpeq",
    "cal","cap","Cap","cases","cdot","cdotp","cdots","centerdot","cfrac","check","checkmark","chi","choose","circ","circeq","circlearrowleft","circlearrowright","circledast","circledcirc","circleddash","circledR","circledS","class","clubsuit","colon","color","complement","cong","coprod","cos","cosh","cot","coth","cr","csc","cssId","cup","Cup","curlyeqprec","curlyeqsucc","curlyvee","curlywedge","curvearrowleft","curvearrowright",
    "dagger","daleth","dashleftarrow","dashrightarrow","dashv","dbinom","ddagger","ddddot","dddot","ddot","ddots","DeclareMathOperator","def","deg","Delta","delta","det","dfrac","diagdown","diagup","diamond","Diamond","diamondsuit","digamma","dim","displaylines","displaystyle","div","divideontimes","dot","doteq","Doteq","doteqdot","dotplus","dots","dotsb","dotsc","dotsi","dotsm","dotso","doublebarwedge","doublecap","doublecup","Downarrow","downarrow","downdownarrows","downharpoonleft","downharpoonright",
    "ell","emptyset","end","enspace","epsilon","eqalign","eqalignno","eqcirc","eqsim","eqslantgtr","eqslantless","equiv","eta","eth","exists","exp",
    "fallingdotseq","fbox","Finv","flat","forall","frac","frac","frak","frown",
    "Game","Gamma","gamma","gcd","ge","genfrac","geq","geqq","geqslant","gets","gg","ggg","gggtr","gimel","gnapprox","gneq","gneqq","gnsim","grave","gt","gt","gtrapprox","gtrdot","gtreqless","gtreqqless","gtrless","gtrsim","gvertneqq",
    "hat","hbar","hbox","hdashline","heartsuit","hline","hom","hookleftarrow","hookrightarrow","hphantom","href","hskip","hslash","hspace","Huge","huge",
    "idotsint","iff","iiiint","iiint","iint","Im","imath","impliedby","implies","in","inf","infty","injlim","int","intercal","intop","iota","it",
    "jmath","Join",
    "kappa","ker","kern",
    "Lambda","lambda","land","langle","LARGE","Large","large","LaTeX","lbrace","lbrack","lceil","ldotp","ldots","le","leadsto","left","Leftarrow","leftarrow","leftarrowtail","leftharpoondown","leftharpoonup","leftleftarrows","Leftrightarrow","leftrightarrow","leftrightarrows","leftrightharpoons","leftrightsquigarrow","leftroot","leftthreetimes","leq","leqalignno","leqq","leqslant","lessapprox","lessdot","lesseqgtr","lesseqqgtr","lessgtr","lesssim","lfloor","lg","lgroup","lhd","lim","liminf","limits","limsup","ll","llap","llcorner","Lleftarrow","lll","llless","lmoustache","ln","lnapprox","lneq","lneqq","lnot","lnsim","log","Longleftarrow","longleftarrow","Longleftrightarrow","longleftrightarrow","longmapsto","Longrightarrow","longrightarrow","looparrowleft","looparrowright","lor","lower","lozenge","lrcorner","Lsh","lt","lt","ltimes","lVert","lvert","lvertneqq",
    "maltese","mapsto","mathbb","mathbf","mathbin","mathcal","mathchoice","mathclose","mathfrak","mathinner","mathit","mathop","mathopen","mathord","mathpunct","mathrel","mathring","mathrm","mathscr","mathsf","mathstrut","mathtt","matrix","max","mbox","measuredangle","mho","mid","min","mit","mkern","mod","models","moveleft","moveright","mp","mskip","mspace","mu","multimap",
    "nabla","natural","ncong","ne","nearrow","neg","negmedspace","negthickspace","negthinspace","neq","newcommand","newenvironment","newline","nexists","ngeq","ngeqq","ngeqslant","ngtr","ni","nLeftarrow","nleftarrow","nLeftrightarrow","nleftrightarrow","nleq","nleqq","nleqslant","nless","nmid","nobreakspace","nolimits","normalsize","not","notag","notag","notin","nparallel","nprec","npreceq","nRightarrow","nrightarrow","nshortmid","nshortparallel","nsim","nsubseteq","nsubseteqq","nsucc","nsucceq","nsupseteq","nsupseteqq","ntriangleleft","ntrianglelefteq","ntriangleright","ntrianglerighteq","nu","nVDash","nVdash","nvDash","nvdash","nwarrow",
    "odot","oint","oldstyle","Omega","omega","omicron","ominus","operatorname","oplus","oslash","otimes","over","overbrace","overleftarrow","overleftrightarrow","overline","overrightarrow","overset","overwithdelims","owns",
    "parallel","partial","perp","phantom","Phi","phi","Pi","pi","pitchfork","pm","pmatrix","pmb","pmod","pod","Pr","prec","precapprox","preccurlyeq","preceq","precnapprox","precneqq","precnsim","precsim","prime","prod","projlim","propto","Psi","psi",
    "qquad","quad",
    "raise","rangle","rbrace","rbrack","rceil","Re","renewcommand","require","restriction","rfloor","rgroup","rhd","rho","right","Rightarrow","rightarrow","rightarrowtail","rightharpoondown","rightharpoonup","rightleftarrows","rightleftharpoons","rightleftharpoons","rightrightarrows","rightsquigarrow","rightthreetimes","risingdotseq","rlap","rm","rmoustache","root","Rrightarrow","Rsh","rtimes","Rule","rVert","rvert",
    "S","scr","scriptscriptstyle","scriptsize","scriptstyle","searrow","sec","setminus","sf","sharp","shortmid","shortparallel","shoveleft","shoveright","sideset","Sigma","sigma","sim","simeq","sin","sinh","skew","small","smallfrown","smallint","smallsetminus","smallsmile","smash","smile","Space","space","spadesuit","sphericalangle","sqcap","sqcup","sqrt","sqsubset","sqsubseteq","sqsupset","sqsupseteq","square","stackrel","star","strut","style","subset","Subset","subseteq","subseteqq","subsetneq","subsetneqq","substack","succ","succapprox","succcurlyeq","succeq","succnapprox","succneqq","succnsim","succsim","sum","sup","supset","Supset","supseteq","supseteqq","supsetneq","supsetneqq","surd","swarrow",
    "tag","tag","tan","tanh","tau","tbinom","TeX","text","textbf","textit","textrm","textstyle","tfrac","therefore","Theta","theta","thickapprox","thicksim","thinspace","tilde","times","tiny","Tiny","to","top","triangle","triangledown","triangleleft","trianglelefteq","triangleq","triangleright","trianglerighteq","tt","twoheadleftarrow","twoheadrightarrow",
    "ulcorner","underbrace","underleftarrow","underleftrightarrow","underline","underrightarrow","underset","unicode","unlhd","unrhd","Uparrow","uparrow","Updownarrow","updownarrow","upharpoonleft","upharpoonright","uplus","uproot","Upsilon","upsilon","upuparrows","urcorner",
    "varDelta","varepsilon","varGamma","varinjlim","varkappa","varLambda","varliminf","varlimsup","varnothing","varOmega","varphi","varPhi","varpi","varPi","varprojlim","varpropto","varPsi","varrho","varsigma","varSigma","varsubsetneq","varsubsetneqq","varsupsetneq","varsupsetneqq","vartheta","varTheta","vartriangle","vartriangleleft","vartriangleright","varUpsilon","varXi","vcenter","vdash","Vdash","vDash","vdots","vec","vee","veebar","verb","Vert","vert","vphantom","Vvdash",
    "wedge","widehat","widetilde","wp","wr",
    "Xi","xi","xleftarrow","xrightarrow",
    "yen",
    "zeta"];
    // Load plugin specific language pack
    //tinymce.PluginManager.requireLangPack('mathjax');

    tinymce.create('tinymce.plugins.MathjaxPlugin', {
        
        init : function(ed, url) {
            var t = this;
            t.url = url; t.win = null; t.editor = ed; t.dom = null; t.doc = null; 
            t.MathJax = null; t.queue = null; t.JAX = new Array(1000); t.mathID = 0; t.spanID = 0; t.onMathjaxReady = new tinymce.util.Dispatcher(t);
            t.mathViewHandler = null; t.mathBox = null;//div de pré-visualisation des formules en cours d'édition
            t.completion = {//est utile popur la complétion de commande via ctrl+space
                ideb: -1, //indice de début pour la commande en cours dans texCommands
                iend: 1, //fin de la selection courante
                dec: 0,//lorsqu'on fait ctrl+space plusieurs fois de suite, indique la nouvelle commande à afficher
                nc: 2 //Pour gérer les 2 nodeChange produit par le raccourci ctrl+space
            };
            t.fullscreenInit = false; t.parent = null;//sert à gérer les aller-retour en mode plein écran
            t.actif = false; t.cleanPath = false;

            ed.onPreInit.add(function(ed){               
                t.dom = ed.dom; t.win = ed.getWin(); t.doc = ed.getDoc(); 
                t.mathBox = t.dom.create("div",{
                    id:cmj+"box", 
                    'class':cmj+"box", 
                    contentEditable:"false",
                    style:"display:none;"
                },"\\({}\\)");
                
                //Si l'éditeur actuel est celui du mode plein écran (fullscreen)
                if(ed.id.indexOf('fullscreen') != -1) {
                    t.fullscreenInit = true;
                    //parent est une référence vers l'instance du plugin mathjax de l'éditeur parent.
                    t.parent = tinymce.get(ed.settings.fullscreen_editor_id).plugins.tinymath;
                    t.mathBox = t.parent.mathBox;
                }

                t._mathjaxInit(url);
                t._testSchemaSettings();
                
                //sert à inserer une formule
                ed.formatter.register('math',{
                	inline:"span",
                	attributes: {'class':cmj+' '+cmji+' '+cmje}
                });
            });
            
            // Lorsque le document est chargé dans l'éditeur 
            //ed.onLoadContent.add(function(ed,o){console.log(o)});
            //Avant que le document ne soit sérialisé ; il est encore temps d'agir sur celui-ci : ed.onPreProcess.add(function(ed,o){});
            
            // après la sérialisation du document ... 
            // o.content la chaine obtenue ; o.save vaut true si le formulaire qui contient l'éditeur est soumis au serveur.
            // o.source_view vaut true si l'utilisateur a appuyé sur le bouton "html".
            ed.onPostProcess.add(function(ed,o){
                if(o.save || o.source_view) o.content = t._cleanMath(o.content);//sert à éliminer le contenu spécifique produit par MathJax.
            });
            
            //Avant que l'écriture dans le corp du document 
            //o.initial : si le document est chargé pour la première fois ; o.source_view : si les sources sont mises à jour dans la fenêtre html
            ed.onBeforeSetContent.add(function(ed,o){
                o.format = "raw";//empêche le formatage de tinymce
                if(o.initial || o.source_view) o.content = t._marqueMath(o.content);
                //le code source du document est modifié, on se prépare à «recompiler» les formules
                if(o.source_view && t.actif) o.typeset = true;
            });
            
            ed.onSetContent.add(function(ed,o){
                if(o.typeset) {
                    t._nettoyer();
                    t.actif = false;
                } 
                if(!t.parent && t.mathID > 0){
                    t._nettoyer();
                    t.actif = true;
                    t._globalMathTypeset(t.mathID,t.spanID);
                    t.mathID = null;
                    t.spanID = null;
                }
                if(!t.parent && t.mathID == -1){
                    t._nettoyer();
                    t.actif = false;
                    t.mathID = null;
                }
            });
            
            ed.onBeforeGetContent.add(function(ed,o){
                //console.log("BeforeGetContent",o.content);
                if(!o.source_view) o.format = "raw";
                //console.log("onBeforeGetContent :",ed, o);
                if(o.source_view) {
                    t._cleanMessage();
                }
            });
                        
            //Lorsqu'on revient du mode plein écran, on met à jour l'ID du parent
            ed.onBeforeExecCommand.add(function(ed, cmd){
                if(cmd == "mceFullScreen" && t.parent) {
                    if(!t.actif && t.parent.actif){
                        t.parent.mathID = -1;   
                    } else if(t.actif){
                        t.parent.mathID = t.MathJax.ElementJax.ID;
                        t.parent.spanID = t.MathJax.OutputJax["HTML-CSS"].ID;
                    } 
                }
            });
            
            ed.onNodeChange.add(function(ed, cm, n, c) {//cm->controlManager, n == ed.selection.getNode(), c-> collapsed ?
                //console.log("NodeChange :", arguments,"\nUndoManager",ed.undoManager);
                var dom = t.dom, cmp = t.completion; 
                
                if(!(t.MathJax && t.actif)) return;
                
                if (t.cleanPath){
                	t.cleanPath = !t.cleanPath;
                	return;
                }
                
                //on profite du nodeChange initial au retour du mode fullscreen pour recalculer les maths
                if(t.fullscreenInit && ed.id.indexOf("fullscreen") == -1){
                    t.actif = false;
                    t.fullscreenInit = false;
                    t._globalMathTypeset();
                    return;
                }
                
                if(cmp.nc) {//pour "annuler" les 2(?) nodeChange qui suivent un ctrl+espace (commande de complétion) ... la touche ctrl déclenche un nodeChange
                    cmp.nc--;
                    return;
                } else if (cmp.ideb != -1)   {//réinitialisation de l'objet «completion»
                    cmp.ideb = -1;
                    cmp.iend = 1;
                    cmp.dec = 0;
                }
                
                var math = t._ismath(n);
                
                //on désactive les controls lorsqu'on est dans une formule (la commande undo n'est pas désactivée !)
                //@TODO : trouver un moyen de désactiver la commande undo lors de la saisie d'une formule
                if(math) each(cm.controls,function(v,k){v.setDisabled(1);});
                //pas de formule à traduire ; hors maths ou dans une formule en cours d'édition ... => rien à faire !
                if(!math && !t.mathedit) return;
                //nous entrons dans une formule interprétée ...
                else if(math && dom.hasClass(math,cmjv)) t.view2edit(math);
                //on sort juste d'une formule en cours d'édition
                else if(!math && t.mathedit) t._removeMathHandler();
            });

            ed.addShortcut('ctrl+m','toggleMathJax',function(){                
                if(!t.actif) {
                    t.actif = true;
                    t._globalMathTypeset();
                } else {
                    t._desactiver(t);
                }
            });
            
            //gestion de la complétion de commande.
            ed.onKeyDown.add(function(ed,ev){
                if(!t.MathJax || !t.actif) return;
                if (ev.ctrlKey && ev.which == 32) {//Ctrl+espace
                    var sel = ed.selection, n = sel.getNode(), math = null;
                    if((math = t._ismath(n)) && ed.dom.hasClass(math,cmje)){
                        var texn = math.firstChild, cmp = t.completion;
                        if(!sel.isCollapsed()) return;
                        var rng = sel.getRng();
                        var tex = texn.data.substring(0,rng.startOffset);
                        if(cmp.ideb == -1){//ctrl+espace n'a pas été tapé 2 fois de suite ou rien n'avait été trouvé lors de la précédente recherche
                            var com = /\\(\w+)$/i.exec(tex);
                            if(com && com[1]) com = com[1];
                            else return;
                            var reg = new RegExp("^"+com,"i"), ideb = -1, iend = 1, ok = true;
                            for (var i = 0; i < texCommands.length; i++) {
                                if(!ok && reg.test(texCommands[i])) iend++;
                                if( ok && reg.test(texCommands[i])) {
                                    ideb = i;
                                    ok = false;
                                }
                            }
                            if(ideb == -1) return;
                            cmp.ideb = ideb;
                            cmp.iend = iend = ideb + iend;
                        }
                        tex = tex.replace(/\\(\w+)$/,"\\"+texCommands[cmp.ideb+cmp.dec]);
                        texn.data = tex + texn.data.substr(rng.startOffset);
                        cmp.dec = (cmp.ideb + cmp.dec == cmp.iend -1 ? 0 : cmp.dec+1);
                        cmp.nc = 2;
                        rng.setStart(texn,tex.length);
                        rng.setEnd(texn,tex.length);
                        sel.setRng(rng);
                    }
                }
                else if(!(ev.metaKey || ev.ctrlKey || ev.altKey || ev.shiftKey) && t.completion.ideb != -1) {//réinitialisation de l'objet «completion»
                    t.completion.ideb = -1;
                    t.completion.iend = 1;
                    t.completion.dec = 0;
                } 
            });
            
            ed.onKeyPress.add(function(ed,ev){
                if(!t.actif) return;
                var dom = t.dom, sel = ed.selection, n = sel.getNode(), math = t._ismath(n), texn = null, tex = "", rng = null;
                var key = String.fromCharCode(ev.charCode);
                if (key == '$' && !ev.ctrlKey) {
                    Event.cancel(ev);//empêche l'insertion ordinaire de '$'
                    t.insertMath(math);
                } else if(math && (key == '{' || key == '(' || key =='[')){
                    //if(tinymce.isGecko && ev.charCode == 0) return;//la touche "flèche vers le bas" a un keyCode de 40 qui donne key == "(" !; son charCode est nul.
                    Event.cancel(ev);//alert('ok');
                    texn = n.firstChild;
                    rng = sel.getRng(); 
                    var start = rng.startOffset, end = rng.endOffset;
                    texn.data = texn.data.substr(0,start)+key+""+delim[key]+texn.data.substring(end);
                    rng.setStart(texn,start+1);
                    rng.setEnd(texn, end+1);
                    sel.setRng(rng);
                } else if(math && dom.hasClass(math,cmje) && (ev.charCode >= 65 && ev.charCode <= 90 || ev.charCode >=97 && ev.charCode <=122)){
                	//afin de visualiser la partie du tableau TexCommands adéquate.
                    texn = n.firstChild; 
                    if(!sel.isCollapsed()) return;
                    rng = sel.getRng();
                    tex = texn.data.substring(0,rng.startOffset)+key;
                    var com = /\\(\w+)$/i.exec(tex);
                    if(com && com[1]) com = com[1];
                    else return;
                    var reg = new RegExp("^"+com,"i"), ideb = -1, iend = 1, ok = true;
                    for (var i = 0; i < texCommands.length; i++) {
                        if(!ok && reg.test(texCommands[i])) iend++;
                        if( ok && reg.test(texCommands[i])) {
                            ideb = i;
                            ok = false;
                        };
                    }
                    if(ideb == -1) return;
                    iend = ideb + iend;
                //console.log(texCommands.slice(ideb,iend));
                }
            });
            
            t.onMathjaxReady.add(function(){
                t.win.setTimeout(function(){
                    t._cleanMessage();
                },2000);
            });
        },

        /**
         * Creates control instances based in the incomming name. This method is normally not
         * needed since the addButton method of the tinymce.Editor class is a more easy way of adding buttons
         * but you sometimes need to create more complex controls like listboxes, split buttons etc then this
         * method can be used to create those.
         *
         * @param {String} n Name of the control to create.
         * @param {tinymce.ControlManager} cm Control manager to use inorder to create new control.
         * @return {tinymce.ui.Control} New control instance or null if no control was created.
         */
        createControl : function(n, cm) {//exemple : plugin advlist
            return null;
        },
        
        insertMath: function(math){
        	var t = this, ed = t.editor, dom = t.dom;
        	each(ed.controlManager.controls,function(v,k){ v.setDisabled(1);});
        	//bof, est-ce bien utile ?
            if(math) {//si on est déjà dans une formule, on change de mode : mode en ligne -> mode block ou le contraire.
                if(dom.hasClass(math,cmjv)) return;
                dom.hasClass(math,cmji)?
                math.setAttribute("class",cmj+" "+cmjd+" "+cmje):
                math.setAttribute("class",cmj+" "+cmji+" "+cmje);
                return;
            }
            
            //On insère une formule en édition 
            ed.formatter.apply('math');
            var entity = ed.selection.getStart();                    
            //rendre la formule en cours de traitement ...
            if(t.MathJax) t._mathPreview(entity);

            t.mathedit = entity;
            ed.undoManager.clear();
        },
        
        view2edit: function(math){
        	var t = this, ed = t.editor, doc = t.doc, dom = t.dom, tab, TEX, form;
        	if(t.mathedit && t.mathedit !== math){
                t._removeMathHandler();
            }
            //Pour la formule courante
            tab = dom.select(".MathJax",math);//console.log(tab);
            if(tab.length != 0) tab[tab.length-1].setAttribute("style","display:none;");
            TEX = math.querySelector('script').text;
            form = doc.createTextNode(dom.hasClass(math,cmjd) ? TEX.substring(14,TEX.length -1): TEX);//on enlève \displaystyle{...}
            math.insertBefore(form,math.firstChild);
            
            dom.removeClass(math,cmjv);
            dom.addClass(math,cmje);
            t.mathedit = math;
            
            //gestion du curseur
            t._selection(math);
            //pb : le path en haut en bas est trop long (pb de mise à jour)
            t.cleanPath = true;//ne marche pas !
            ed.nodeChanged();
            
            //rendre la formule en cours de traitement ...
            t._mathPreview(math);
        },
        
        _globalMathTypeset: function(mathID, spanID){
            var t = this, ed = t.editor, dom = t.dom, maths = [], i, m;
            
            function updateJAX(tab){
            	console.log('updateJAX');
            	var script = null;
                for(var k = 0 ;k < tab.length; k++){
                	script = tab[k].querySelector('script');
                    t.JAX[script.id.substring(16)-1] = script.MathJax;
                    script.text = t._norm(script.text);
                    if(k==0) {
                        t.mathBox.removeAttribute("style");
                        dom.remove(t.mathBox);
                    }
                }
                if(mathID && spanID){
                    t.queue.Push(function(){
                        t.MathJax.ElementJax.ID = mathID;
                        t.MathJax.OutputJax["HTML-CSS"].ID = spanID;
                    });
                }
                ed.setProgressState(false);
                t.win.setTimeout(function(){
                    t._cleanMessage();
                },1000);
            }
            
            ed.setProgressState(true);
            maths = dom.select("."+cmj);
            
            if(t.MathJax.ElementJax) t.MathJax.ElementJax.ID = 0;
            if(t.MathJax.OutputJax["HTML-CSS"]) t.MathJax.OutputJax["HTML-CSS"].ID = 0;
            
            if(!t.mathBox){
                t.mathBox = t.dom.create("div",{
                    id:cmj+"box", 
                    'class':cmj+"box", 
                    contentEditable:"false",
                    style:"display:none;"
                },"\\({}\\)");
            }
            
            ed.getBody().insertBefore(t.mathBox,ed.getBody().firstChild);
            maths.splice(0,0,t.mathBox);
                       
            //le curseur est sur une formule, on le déplace sur le noeud suivant.
            m = t._ismath(ed.selection.getNode());
            if(m && m.nextSibling)  {
                ed.selection.select(m.nextSibling);
                ed.selection.isCollapsed() || ed.selection.collapse(true);
            }
            
            for (i = 0; i < maths.length; i++) {
                dom.addClass(maths[i],cmjv);
                dom.hasClass(maths[i],cmje) && dom.removeClass(maths[i],cmje);
            }
            t.queue.Push(function(){
                t.MathJax.Hub.Typeset(ed.getBody(),function(){
                    updateJAX(maths);
                });
            });
            
            t.win.setTimeout(function(){
                t._cleanMessage();
            },1000);
        },
        
        _mathTypeset : function(math){
            var t = this, ed = t.editor, dom = t.dom, jax = null, TEX, script=math.querySelector('script');
            
            TEX = math.firstChild.data;
            TEX = t._norm(TEX);
//            if(tinymce.isWebKit) TEX = TEX.substring(1,TEX.length-1); //on enlève les $$.(bug wekkit)
            //on gère le curseur si on entre directement dans une autre formule ?
            var m = t._ismath(ed.selection.getNode());
            if(m && m.nextSibling)  {
                ed.selection.select(m.nextSibling);
                ed.selection.isCollapsed() || ed.selection.collapse(true);
            }
            
            dom.addClass(math,cmjv);
            dom.hasClass(math,cmje) && dom.removeClass(math,cmje);
            
            
            if(!script){//premier rendu de math
                math.firstChild.data = dom.hasClass(math,cmji) ? "\\("+TEX+"\\)" : "\\(\\displaystyle{"+TEX+"}\\)";
                t.queue.Push(function(){
                    t.MathJax.Hub.Typeset(math);
                },function(){
                	script = math.querySelector('script');
                    t.JAX[script.id.substring(16)-1] = script.MathJax;
                });
            } else {//mise à jour
                dom.remove(math.firstChild);
                if(!script.MathJax) script.MathJax = t.JAX[script.id.substring(16)-1];
                jax = t.JAX[script.id.substring(16)-1].elementJax;//console.log(jax);
                t.queue.Push(function(){
                    dom.hasClass(math,cmjd) ? jax.Text("\\displaystyle{"+TEX+"}") : jax.Text(TEX);
                },function(){
                    var tab = dom.select(".MathJax",math);//console.log(tab);
                    if(tab.length != 0) tab[tab.length-1].setAttribute("style","");
                    t.JAX[script.id.substring(16)-1] = script.MathJax;
                });
            }
        },

        _ismath : function(node){
            var dom = this.dom;
            return dom.getParent(node,"."+cmj);
        },
        
        //Cette méthode sert à marquer les formules (qui ne le seraient pas ...).
        //Elle est appelée à deux reprises : 1. lorsque l'éditeur est chargé 
        //2. Lorsque l'utilisateur met à jour le code html via l'éditeur de code
        _marqueMath : function(content){
            var text = this._cleanMath(content);
            //on nettoie les résidus avant toute chose ...
            var newtext = "";
            //le motif qui suit détecte les formules qui sont marquées ou non
            var match, lastIndex = 0, motif = /\\\((.*?)\\\)/gi;// /(<(span)[^>]*?mcemathjax[^>]*?>)?\\\((.*?)\\\)(<\/\2>)?/gi//motif = /\\\((.*?)\\\)/gi, 
            while((match = motif.exec(text)) != null){
                newtext += text.substring(lastIndex,match.index);
                lastIndex = motif.lastIndex;
                newtext += '<span class="'+cmj+' '+cmji+'">\\('+match[1]+'\\)</span>';
            //                newtext += (match[2] == "span") ? 
            //                match[0] : //la formule est déja marquée ; on ne modifie rien
            //                '<span class="'+cmj+' '+cmji+'">\\('+match[3]+'\\)</span>';
            }
            newtext += text.substring(lastIndex,text.length);
            text = newtext;
            newtext = "";
            motif = /\\\[(.*?)\\\]/gi;// /(<(span)[^>]*?mcemathjax[^>]*?>)?\\\[(.*?)\\\](<\/\2>)?/gi;// /\\\[(.*?)\\\]/gi;
            lastIndex = 0;
            while((match = motif.exec(text)) != null){
                newtext += text.substring(lastIndex,match.index);
                lastIndex = motif.lastIndex;
                newtext += '<span class="'+cmj+' '+cmjd+'">\\('+match[1]+'\\)</span>';
            //                newtext += (match[2] == "span") ? 
            //                match[0] : //la formule est déja marquée ; on ne modifie rien
            //                '<span class="'+cmj+' '+cmjd+'">\\('+match[3]+'\\)</span>';
            }
            newtext += text.substring(lastIndex,text.length);
            text = newtext;
            newtext = "";
            motif = /(\${1,2})([^\$]{1,})\1/gi; // /(<(span)[^>]*?mcemathjax[^>]*?>)?(\${1,2})([^\$]{1,})\3(<\/\2>)?/gi;
            lastIndex = 0;
            while((match = motif.exec(text)) != null){
                newtext += text.substring(lastIndex,match.index);
                lastIndex = motif.lastIndex;
                newtext += '<span class="'+cmj+' '+(match[1]=='$' ? cmji : cmjd)+'">\\('+match[2]+'\\)</span>';
            }
            newtext += text.substring(lastIndex,text.length);
            return newtext;
        },

        //sert à nettoyer le contenu de l'éditeur des traces laissées par le plugin.
        _cleanMath : function(content){
            function rep(re, str) {
                content = content.replace(re, str);
            };
            //les deux lignes qui suivent servent à éliminer le html relatif aux formules en cours après que tinymce ait sérializé le document en mode "html"
            rep(/<span[^>]*?mcemathinline mcemathv[^>]*?>.*?\n.*?\n(.*?)\n.*?\n<\/span>/g,"\\($1\\)");
            rep(/<span[^>]*?mcemathdisplaystyle mcemathv[^>]*?>.*?\n.*?\n(.*?)\n.*?\n<\/span>/g,"\\[$1\\]");
            //pour éliminer le html après une sérialisation en mode "raw"
            rep(/<span[^>]*?mcemathinline mcemathv[^>]*?>.*?<script[^>]*?>(.*?)<\/script><\/span>/g,"\\($1\\)");
            rep(/<span[^>]*?mcemathdisplaystyle mcemathv[^>]*?>.*?<script[^>]*?>(.*?)<\/script><\/span>/g,"\\[$1\\]");
            //on élimine les marqueurs de formules
            rep(/<span[^>]*?mcemathjax mcemathinline[^>]*?>\\\((.*?)\\\)<\/span>/g,"\\($1\\)");
            rep(/<span[^>]*?mcemathjax mcemathdisplaystyle[^>]*?>\\\((.*?)\\\)<\/span>/g,"\\[$1\\]");
            rep(/<span[^>]*?mcemathjax mcemathinline[^>]*?>\$(.*?)\$<\/span>/g,"\\($1\\)");
            rep(/<span[^>]*?mcemathjax mcemathdisplaystyle[^>]*?>\$(.*?)\$<\/span>/g,"\\[$1\\]");
            return content;
        },
        
        //MathJax pollue le document (body) avec trois div (deux au début, la dernière tout à la fin)
        _cleanMessage : function(){
            var dom = this.dom;
            var el1 = dom.get("MathJax_Hidden");
            if(el1) el1 = el1.parentNode;
            var el2 = dom.get("MathJax_Message");
            var el3 = dom.select("body > div[style]:last-child")[0];
            if(el1) dom.remove(el1);
            if(el2) dom.remove(el2);
            if(el3) dom.remove(el3);
        },
        
        _selection: function(node){
            var t = this, ed = t.editor, dom = t.dom, child = node.firstChild, sel = ed.selection;
            if(!child) {//une nouvelle formule vient d'être insérée.
                dom.add(node,t.doc.createTextNode(""));
                child = node.firstChild;
//                console.log(child.nodeType);
            }
            //sel.setNode(node);
            var rng = ed.selection.getRng();
            //rng.selectNodeContents(node);
            //rng.collapse(true);//de quel côté entre-t-on ?
            rng.setStart(child,0);
            rng.setEnd(child,0);
            sel.setRng(rng);
//            console.log(sel);
        },
        
        //est appelée lorsqu'on sort d'une formule.
        _removeMathHandler: function(){
            var t = this, dom = t.dom, cm = t.editor.controlManager;
            t._mathTypeset(t.mathedit);//on commence par appliquer mathJax sur cette formule.
            t.mathedit = null;
            t.mathBox = dom.remove(cmj+"box");
            t.doc.removeEventListener("keyup",t.mathViewHandler,false);//on supprime l'écouteur définit dans _mathPreview.
            each(cm.controls,function(v,k){//réactivation des controles de l'éditeur.
                v.setDisabled(0);
            });
            t.mathViewHandler = null;
        },
        
        //gère la prévisulation des formules
        _mathPreview: function(math){
            var t = this, ed = t.editor, dom = t.dom, box = t.mathBox, TEX ="", script = box.querySelector('script');
            
            dom.add(ed.getBody(),box);
            var dynmath = null;
            if(!script.MathJax) script.MathJax = t.JAX[0];
            dynmath = t.JAX[0].elementJax;
            //sert à initialiser le rendu ...
            t.queue.Push(function(){
                TEX = math.firstChild ? math.firstChild.data : "";
//                if(tinymce.isWebKit) TEX = TEX.substring(1,TEX.length-1);
            },function(){
                dynmath.Text("\\displaystyle{"+TEX+"}");
            });
            //lorsqu'on est dans une formule, la prévisualisation est mis à jour grâce à cet écouteur
            t.mathViewHandler = function(ev){
                if (ed.selection.getStart() === math) {
//                    if(tinymce.isWebKit){//on essaie de gérer webkit au mieux ...
//                        var rng = ed.selection.getRng();
//                        var tex = rng.startContainer;
//                        if(tex.data.charAt(0) != '$') tex.data = "$" + tex.data;
//                        if(tex.data.charAt(tex.length-1) != '$') tex.data += "$";
//                        if(ev.keyCode == 39 && rng.startOffset == tex.length){//-> pour sortir de la formule courante
//                            rng.setStart(math.nextSibling,1);
//                            rng.setEnd(math.nextSibling,1);
//                            ed.selection.setRng(rng);
//                            return;
//                        }
//                    }
                    TEX = math.firstChild ? math.firstChild.data : "";
                    //if(tinymce.isWebKit) TEX = TEX.substring(1,TEX.length-1);
                    t.queue.Push(function(){
                        dynmath.Text("\\displaystyle{"+TEX+"}");
                    });
                }
            };
            //ajout de l'écouteur défini plus tôt ; on l'enlève dès que l'utilisateur sort d'une formule (cf _removeMathHandler).
            t.doc.addEventListener("keyup",t.mathViewHandler,false);
        },

        config : 'MathJax.Hub.Config({' +
        'tex2jax: {inlineMath: [ ["$","$"], ["\\\\(","\\\\)"] ], displayMath: [ ["$$","$$"], ["\\\\[","\\\\]"] ], processEscapes: true, processEnvironments: true},'+
        '"HTML-CSS" : {showMathMenu : false},'+
        'skipStartupTypeset: true,'+
        //'extensions: ["tex2jax.js","toMathML.js","TeX/noErrors.js","TeX/noUndefined.js","TeX/AMSmath.js","TeX/AMSsymbols.js"],' +
        //'jax: ["input/TeX","output/HTML-CSS"],' +
        'showProcessingMessages: false,'+
        'messageStyle: "none",'+
        //'processEnvironments: true'+
        //'menuSettings: { zoom: "Double-Click" },'+
        '});\n'+
        'MathJax.Hub.Startup.onload();',
    
        //lors d'un setContent ou d'un getContent avec o.format = "html", tinymce pollue les formules affichées qui ne sont alors plus éditables.
        //1ère solution : forcer le format o.format = "raw" lors de ces actions.
        //2ème solution (serait plus propre ...) : agir sur le schéma.
        _testSchemaSettings : function(){
            var ed = this.editor, sr = ed.schema.getElementRule('span');
            sr.removeEmpty = false;
            sr.attributes["aria-role"] = {};//impossible d'empêcher cet attribut d'être éliminer.
            sr.attributes["role"] = {};
            sr.attributesOrder["17"] = "aria-role";
            sr.attributesOrder["18"] = "role";
            //ed.schema.setValidElements('span[aria-readonly|role]')
            ed.schema.addValidElements('nobr');
            //@TODO : tinymce commente les scripts <![CDATA[...]]!>, il faudrait empêcher cela (pas fastoche)
            ed.parser.addNodeFilter('script',function(nodes,name){
                var i = nodes.length, node;
                while(i--){
                    node = nodes[i];
                    if(node.attr('id').indexOf("MathJax") != -1){
                        node.attr('type','text/math');
                    //console.log(node);
                    }
                }
            });
        },
        
        _nettoyer : function(){
            var t = this;
            for(var i = 0; i < t.JAX.length; i++) {
                if(t.JAX[i] !== undefined) t.JAX[i] = undefined;
                else break;
            }
            t.mathBox = null;
        },
        
        //insère un espace autour des inégalités strictes 
        _norm : function(str){
            var text = str;
            text = text.replace(/(<|>)(?=[^\s])/gi,"$1 ");
            text = text.replace(/(?=[^\s])(<|>)/gi," $1");
            return text;
        },
        
        _desactiver : function(){//et si on est sur une formule ?
            var t = this, html;
            if(t.mathedit){
                t._removeMathHandler(t.mathedit);
            }
            t.queue.Push(function(){
                t._nettoyer();
                html = t.editor.getContent();
                html = t._marqueMath(html);
                t.editor.setContent(html);
                t.actif = false;
            });
            
        },
        
        _mathjaxInit : function(url){
            var t = this, dom = t.dom, ed = t.editor, doc = t.doc, win = t.win;
            
            //On charge la feuille de style qui gère le rendu des formules en cours d'édition
            ed.dom.loadCSS(url+'/css/mathjax.css');
            
            //On insère mathjax dans le head du document à éditer.
            
            //infos de configuration pour MathJax
            var script1 = dom.create('script',{
                type:"text/x-mathjax-config"
            },t.config);
            //Chargement du script principal via le CDN (et si jamais le CDN de MathJax était indisponible ?)
            var script2 = dom.create('script',{
                type:"text/javascript",
                src:"http://cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-MML-AM_HTMLorMML,"+url+"/js/macro"
            });
            var head = doc.getElementsByTagName("head")[0];
            head.appendChild(script1);
            head.appendChild(script2);
            
            //MathJax peut mettre un certain temps avant d'être prêt ...
            var timeId = win.setInterval(function(){
                if(win.MathJax && win.MathJax.Hub.queue) {
                    t.MathJax = win.MathJax;
                    //pour la synchronisation : mettre une (ou des) fonction de rappel f dans la queue -> queue.Push(function(){f()},...).
                    t.queue = t.MathJax.Hub.queue;
                    
                    //lorsqu'on est en mode plein écran
                    if(t.parent && t.parent.actif) {
                        //sert à mettre à jour le compteur ID de MathJax.ElementJax ; 
                        //à ne faire qu'après s'être occupé de la boite de prévisualisation (ainsi son ID sera 1)
                        t.actif = true;
                        t.mathBox = null;
                        t._globalMathTypeset(t.parent.MathJax.ElementJax.ID,t.parent.MathJax.OutputJax["HTML-CSS"].ID);
                    }
                    t.onMathjaxReady.dispatch();
                    
                    //l'initialisation est achevée, inutile de la répéter ...
                    win.clearInterval(timeId);
                }
            },1);
        },
        
        getInfo : function() {
            return {
                longname : 'MathJax plugin',
                author : 'Etienne Florent',
                authorurl : 'http://moodle.albert-thomas.org',
                //infourl : 'http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/example',
                version : "1.0"
            };
        }
    });

    // Register plugin
    tinymce.PluginManager.add('tinymath', tinymce.plugins.MathjaxPlugin);
})();
