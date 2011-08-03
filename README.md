Виджет для экспорта комментариев из Google Plus+ в свой блог
============================================================

![preview](http://gplus.kulikovd.ru/gplus-preview.jpg)

Пример
------

http://gplus.kulikovd.ru/demo1.html

http://gplus.kulikovd.ru/demo.html — внизу странички после комментариев из дискуса


Использование
-------------

В самый низ, перед закрывающимся тегом body вставляете следующий скрипт.
Заменяете соответственно 104578309919492528255 — на id своего профайла из G+, а url=http://new.radio-t.com/2011/07/249_31.html — на урл странички где будут отображаться комментарии и на которую вы сослались в G+.

Виджет находит среди ваших постов тот, который содержит ссылку на страничку, указанную в url=,
и вытягивает из него комменты.

По умолчанию в параметр url подставляется адрес текущей странички url=' + window.location.href


	<script type='text/javascript'>
	    (function() {
	        var gplusapi = document.createElement('script');
	        gplusapi.type = 'text/javascript';
	        gplusapi.async = true;
	        gplusapi.src = 'http://gplus.kulikovd.ru/pingback?profile=104578309919492528255&url=' + window.location.href;
	        document.getElementsByTagName('script')[0].parentNode.appendChild(gplusapi);
	    })();
	</script>
	
Блок с комментариями вставляется в элемент &lt;div id="gplus-pingback"&gt;&lt;/div&gt;

Размещаете в html-коде вашей странички блок 

	<div id="gplus-pingback"></div>

Именно в него загрузятся комментарии. Таким образом можно поместить этот блок в любое место сайта.


! Кеширование
----------

Список комментариев кешируется и обновляется не чаще чем раз в 2 минуты


+
---------

Сделано по мотивам этого поста Umputun'а https://plus.google.com/104578309919492528255/posts/GrRV3p8BYiZ

Blogger
_______

В гугловском блогере сложно вставить тег div#gplus-pingback в нужно место. Поэтому можно добавлять его динамически.
Для этого можно немного модифицировать код подключения виджета:

    <script type='text/javascript'>
        (function() {
        
            var newNode = document.createElement('div')
            newNode.id = 'gplus-pingback';
            var block = document.getElementById('comments');
            block.parentNode.insertBefore(newNode, block.nextSibling);
        
            var gplusapi = document.createElement('script');
            gplusapi.type = 'text/javascript';
            gplusapi.async = true;
            gplusapi.src = 'http://gplus.kulikovd.ru/pingback?profile=104578309919492528255&url=' + window.location.href;
            document.getElementsByTagName('script')[0].parentNode.appendChild(gplusapi);
	
        })();
    </script> 
