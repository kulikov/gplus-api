Виджет для экспорта комментариев из Google Plus+ в свой блог
============================================================

![preview](http://gplus.kulikovd.ru/gplus-preview.jpg)

Пример
------

http://gplus.kulikovd.ru/demo1.html


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



Changelog
---------

**v0.4**

 * В заголовке выводится количество комментариев
 * Виджет не показывается совсем, если в G+ нет поста со ссылкой на эту страницу
 * Добавил ссылки для добавления комментариев (переходит на G+)

**v0.3**

 * Добавил кнопки сортировки комментариев по дате вверх/вниз и по оценкам комментариев
 * У комментариев теперь выводится их оценка. Пока только просто показывается, но скоро прикручу и кнопочку выставления

**v0.2**

**v0.1**


TODO
-------

 - Добавлять комменты прямо в виджете
 - Оценивать комментарии тут же
 - Производительность: поставить на nginx прокси в мемкеш. Обновлять кеш асинхронно по очереди. Тогда сам виджет будет всегда забирать данные из кеша, а кеш будет пересчитываться в фоне. Если в мемкеше пусто — подсовывать nginx'у статичную js болванку с надписью «Пока комментов нет» и по мере сил парсить комменты и писать их в мемкеш. %) как-то так. Демона, парсящего коменты рестартить раз в 10 минут по крону. А для очереди заданий заюзать или тот-же мемкеш или что-нибудь типа rabbitmq
 - Вот интересная библиотека — возможно она подойдет https://github.com/akalend/amqp-rest 
 - Защита от спама и доса – поковырять встроенные возможности nginx (ngx_accesskey_module). Наверняка там есть что-то для этого.
 - переписать в итоге все на питончике :). А потом можно и на хабре статейку опубликовать.