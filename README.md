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
Заменяете соответственно 106622629615873511071 — на id своего профайла из G+, 
а url=http://new.radio-t.com/2011/07/249_31.html — на урл странички где будут 
отображаться комментарии и на которую вы сослались в G+.

Виджет находит среди ваших постов тот, который содержит ссылку на страничку, указанную в url=,
и вытягивает из него комменты.

По умолчанию в параметр url подставляется адрес текущей странички url=' + window.location.href

    <script type='text/javascript'>//<![CDATA[
        (function() {
            var _gp = document.createElement('script');
            _gp.type = 'text/javascript'; _gp.async = true;
            _gp.src = 'http://gplus.kulikovd.ru/pingback?profile=106622629615873511071&url=' + window.location.href;
            var _s = document.getElementsByTagName('script')[0]; _s.parentNode.insertBefore(_gp, _s.nextSibling);
        })();
    //]]></script>
    
По умолчанию виджет ищет на страничке блок с комментариями &lt;div id="comments"&gt; (есть в WordPress и Blogger) и вставляется сразу после него.
    
Но можно и явно указать куда он будет вставлен. Для этого нужно поместить в html-код вашей странички блок:

    <div id="gplus-pingback"></div>

И комментарии загрузятся в него. Таким образом можно поместить виджет в любое место сайта.



! Кеширование
----------

Список комментариев кешируется и обновляется не чаще чем раз в 2 минуты



+
---------

Сделано по мотивам этого поста Umputun'а https://plus.google.com/104578309919492528255/posts/GrRV3p8BYiZ
