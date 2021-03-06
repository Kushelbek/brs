Banners
=======

Banner rotation module with statistics for Cotonti


Version 2.0


Russian documentation: http://portal30.ru/sozdanie-internet-sajtov/free-scripts/cotonti-banners

Системные требования и ограничения:

Наличие на Вашем сайте установленной библиотеки Cotonti Lib не ниже версии 2.0
Административная часть плагина расчитана на альтернативную тему панели управления cpanel.
В качестве шаблонизатора используется View
 

Описание:

Баннеры разделяются по категориям и в каждую рекламную позицию на сайте можно вывести баннеры из одной или нескольких категорий. Вывод баннеров, ротация, осуществляется по порядку или случайным образом.
Для каждого баннера ведется статистика показов и кликов. Возможно ведение почасовой статистики, но это вызовет дополнительную нагрузку на сервер.

Поддерживаются изображения и флеш. Так же есть возможность установки произвольного кода баннера, например со сторонней рекламной системы.

Для удобства ведения рекламных кампаний, баннеры можно разбивать по клиентам. Это позволяет получать статистику показов и кликов по баннеру для  каждого клиента.

 

Возможности:
- отображение и автоматическая ротация баннеров на вашем сайте
- упорядоченная или случайная ротация баннеров
- поддерживаемые форматы: jpeg, png, gif, bmp и swf
- возможность установки произвольного кода баннера
- неограниченное количество категорий (баннерных позиций)
- неограниченное количество баннеров в категрии и в целом
- статистика показов и кликов
- почасовая статистика показов и кликов
- отчет о показах и кликах с фильтрами по периоду, категориям и клиентам
- привязка баннеров к клиентам

 

Установка:
- Скопировать модуль на сервер в папку modules/brs
- В папке datas cоздать папку brs и установить на нее права на запись
- Установить планин в панели управления
- Создать нужные категории и баннеры
- В нужное место файла шаблона добавить вызов виджета: <?=brs_controller_Widget::banner('category', 2)?> (параметры см. ниже)


Параметры виджета:
Виджет вызывается со следующими параметрами

brs_controller_Widget::banner($cat, $cnt = 1, $tpl = 'brs.banner', $order = 'order', $client = false, $subcats = false)
где:

$cat - категория баннеров или список категорий, разделенный символом ';'

Остальные параметры не обязательные

$cnt - количество выводимых баннеров. При выводе нескольких баннеров подряд, следует использовать этот параметр вместо нескольких вывозовов этой функции, т.к. каждый отдельный вывозов совершает обращения в базе данных
$tpl = tpl - файл
$order - порядок показа. Допустимые значения 'order' - по порядку, 'rand' - случайно
$client - данный параметр пока не используется
$subcats - выводить баннеры из вложенных категорий

Однако, в отличие от шаблонизатора View, встроенный шаблонизатор CoTemplate не умеет работать с объектами (как ни странно). Для использования в tpl-шаблонах пользуйтесь call-back функцией:

banner_widget($cat, $cnt = 1, $tpl = 'banners', $order = 'order', $client = false, $subcats = false)
Она является оберткой для brs_controller_Widget::banner.

 

История изменений:

Версия v2.0.1

Совместимость с Cotonti Lib v2.0.
Версия v2.0.0

Расширение теперь полноценный модуль. Раньше оно было плагином.
Переход на Cotonti Lib. Это дало унификацию API и повышение производительности.
Изменен код расширения и название папки на сервере. ADBlocker'ы не любили название banners :)
     Обратите внимание! Автоматическое обновление плагина (версии 1) до модуля (версии 2) невозможно.

 

GitHub: https://github.com/Alex300/brs
