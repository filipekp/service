/*************************************************************
 *
 *  MathJax/config/local/local.js
 *  
 *  Include changes and configuration local to your installation
 *  in this file.  For example, common macros can be defined here
 *  (see below).  To use this file, add "local/local.js" to the
 *  config array in MathJax.js or your MathJax.Hub.Config() call.
 *
 *  ---------------------------------------------------------------------
 *  
 *  Copyright (c) 2009 Design Science, Inc.
 * 
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 * 
 *      http://www.apache.org/licenses/LICENSE-2.0
 * 
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */
var url = window.top.tinymce.activeEditor.plugins.tinymath.url;

MathJax.Hub.Register.StartupHook("TeX Jax Ready",function () {
  var TEX = MathJax.InputJax.TeX;

  // place macros here.  E.g.:
      TEX.Macro("vect","{\\mathchoice{\\overrightarrow{\\displaystyle\\mathstrut#1\\,\\,}}{\\overrightarrow{\\textstyle\\mathstrut#1\\,\\,}}{\\overrightarrow{\\scriptstyle\\mathstrut#1\\,\\,}}{\\overrightarrow{\\scriptscriptstyle\\mathstrut#1\\,\\,}}}",1);
      TEX.Macro("rouge","{\\color{red}{#1}}",1);
      TEX.Macro("bleu","{\\color{blue}{#1}}",1);
      TEX.Macro("vert","{\\color{green}{#1}}",1);
      TEX.Macro("Oij","{\\left(\\text{O},~\\vect{\\imath},~\\vect{\\jmath}\\right)}");
      TEX.Macro("Oijk","{\\left(\\text{O},~\\vect{\\imath},~\\vect{\\jmath},~\\vect{k}\\right)}");
      TEX.Macro("Ouv","{\\left(\\text{O},~\\vect{u},~\\vect{v}\\right)}");
      TEX.Macro("impeq","{\\,\\,\\stackrel{#1}{\\Rightarrow}\\,\\,}",1);
      TEX.Macro("eq","{\\,\\,\\stackrel{#1}{\\iff}\\,\\,}",1);
      TEX.Macro("e","{\\text{e}^{#1}}",1);
      TEX.Macro("i","{\\text{i}}");
      TEX.Macro("coordv","{\\begin{pmatrix}#1\\cr #2\\end{pmatrix}}",2);
      TEX.Macro("coordp","{\\left(#1 ~; ~ #2\\right)}",2);
      TEX.Macro("res","{\\bbox[yellow,4pt]{#1}}",1);
      TEX.Macro("ipp","{\\left\\{\\begin{array}{l}#1\\cr #2\\end{array}\\right.\\quad\\text{et}\\quad\\left\\{\\begin{array}{l}#3\\cr #4\\end{array}\\right.}",4);

      TEX.Macro("calA","{\\mathscr{A}}");
      TEX.Macro("calB","{\\mathscr{B}}");
      TEX.Macro("calC","{\\mathscr{C}}");
      TEX.Macro("calD","{\\mathscr{D}}");
      TEX.Macro("calE","{\\mathscr{E}}");
      TEX.Macro("calF","{\\mathscr{F}}");
      TEX.Macro("calG","{\\mathscr{G}}");
      TEX.Macro("calH","{\\mathscr{H}}");
      TEX.Macro("calI","{\\mathscr{I}}");
      TEX.Macro("calJ","{\\mathscr{J}}");
      TEX.Macro("calK","{\\mathscr{K}}");
      TEX.Macro("calL","{\\mathscr{L}}");
      TEX.Macro("calM","{\\mathscr{M}}");
      TEX.Macro("calN","{\\mathscr{N}}");
      TEX.Macro("calO","{\\mathscr{O}}");
      TEX.Macro("calP","{\\mathscr{P}}");
      TEX.Macro("calQ","{\\mathscr{Q}}");
      TEX.Macro("calR","{\\mathscr{R}}");
      TEX.Macro("calS","{\\mathscr{S}}");
      TEX.Macro("calT","{\\mathscr{T}}");
      TEX.Macro("calU","{\\mathscr{U}}");
      TEX.Macro("calV","{\\mathscr{V}}");
      TEX.Macro("calW","{\\mathscr{W}}");
      TEX.Macro("calX","{\\mathscr{X}}");
      TEX.Macro("calY","{\\mathscr{Y}}");
      TEX.Macro("calZ","{\\mathscr{Z}}");

      TEX.Macro("C","{\\mathbb{C}}");
      TEX.Macro("R","{\\mathbb{R}}");
      TEX.Macro("Q","{\\mathbb{Q}}");
      TEX.Macro("D","{\\mathbb{D}}");
      TEX.Macro("Z","{\\mathbb{Z}}");
      TEX.Macro("N","{\\mathbb{N}}");
  //   TEX.Macro("op","\\mathop{\\rm #1}",1); // a macro with 1 parameter
  
});

MathJax.Ajax.loadComplete(url+"/js/macro.js");
