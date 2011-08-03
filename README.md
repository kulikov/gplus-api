Виджет для добавления коментариев их Google Plus в свой блог
============================================================

Пример
------

	[http://gplus.kulikovd.ru/demo.html]

Использование
-------------

В самый, перед закрывающимся тегом </body> вставляете следующий скрипт
Заменяете соответственно 104578309919492528255 — на id своего профайла из G+
F url=http://new.radio-t.com/2011/07/249_31.html — на урл текущей странички

Виджет находит среди ваших постов тот, который содержит ссылку на старничку url=
и вытягивает из него комменты

	<script type='text/javascript'>
	    (function() {
	        var gplusapi = document.createElement('script');
	        gplusapi.type = 'text/javascript';
	        gplusapi.async = true;
	        gplusapi.src = 'http://gplus.kulikovd.ru/pingback?profile=104578309919492528255&url=http://new.radio-t.com/2011/07/249_31.html';
	        document.getElementsByTagName('script')[0].parentNode.appendChild(gplusapi);
	    })();
	</script>
	
Блок с коментариями вставляется в элемент <div id="gplus-pingback"></div>
Размещяете в html-коде вашей странички блок 

	<div id="gplus-pingback"></div>
	
Именно в него загрузятся комментарии. Таким образом можно поместить этот блок в любое место сайта.