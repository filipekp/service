<!DOCTYPE html>
<html lang="cs" dir="ltr">
  <head>
    <meta charset=utf-8 />
    <style>
      body {
        font-family: Arial, Tahoma, Verdana;
        font-size: 12px;
        margin: 0;
        padding: 0;
      }
    </style>
    <script src="http://www.dysportal.cz/admin/resources/js/plugins/mathjax/MathJax.js?config=TeX-AMS_HTML-full"></script>
    <script>
      MathJax.Hub.Config({
        tex2jax: {inlineMath: [['$', '$'],['\\(', '\\)']]}
      });
      var args = top.tinymce.activeEditor.windowManager.getParams();
    </script>
  </head>
  <body style="padding: 20px;">
  </body>
  <script>document.body.innerHTML = args.text;</script>
</html>