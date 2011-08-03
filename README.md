Виджет для экспорта комментариев из Google Plus+ в свой блог
============================================================

![preview](http://gplus.kulikovd.ru/gplus-preview.jpg)

Пример
------

http://gplus.kulikovd.ru/demo1.html

http://gplus.kulikovd.ru/demo.html — внизу странички после комментариев из дискуса


Использование
-------------

В самый, перед закрывающимся тегом body вставляете следующий скрипт.
Заменяете соответственно 104578309919492528255 — на id своего профайла из G+, а url=http://new.radio-t.com/2011/07/249_31.html — на урл странички где будут отображаться комментарии и на которую вы сослались в G+.

Виджет находит среди ваших постов тот, который содержит ссылку на старничку, указанную в url=,
и вытягивает из него комменты.

	<script type='text/javascript'>
	    (function() {
	        var gplusapi = document.createElement('script');
	        gplusapi.type = 'text/javascript';
	        gplusapi.async = true;
	        gplusapi.src = 'http://gplus.kulikovd.ru/pingback?profile=104578309919492528255&url=http://new.radio-t.com/2011/07/249_31.html';
	        document.getElementsByTagName('script')[0].parentNode.appendChild(gplusapi);
	    })();
	</script>
	
Блок с комментариями вставляется в элемент <div id="gplus-pingback"></div>
Размещяете в html-коде вашей странички блок 

	<div id="gplus-pingback"></div>

Именно в него загрузятся комментарии. Таким образом можно поместить этот блок в любое место сайта.


! Кеширование
----------

Список комментариев кешируется и обновляется не чаще чем раз в 2 минуты


+
---------

Сделано по мотивам этого поста Umputun'а https://plus.google.com/104578309919492528255/posts/GrRV3p8BYiZ