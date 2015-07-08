/**
 * Cotonti Module Brs
 *
 * @author Kalnov Alexey    <kalnovalexey@yandex.ru>
 * @copyright Portal30 Studio http://portal30.ru
 */

$(function() {
    var brs = {};
    var cnt = 0;
    $('div.brs-loading').each(function(){
        var id = $(this).attr('id');
        id = parseInt(id.replace('brs_', ''));
        var cat   = $(this).attr('data-category'),
            order = $(this).attr('data-order'),
            client = $(this).attr('data-client');
        if(id > 0){
            brs[id] = {category: cat, order: order, client: client};
            cnt++;
        }
    });

    if(cnt > 0){
        $.post('index.php?e=brs&a=ajxLoad', {brs: brs, x : brsX}, function(data){
            if(data.error != ''){
                alert(data.error)
            }else{
                data.items = data.items || {};
                $.each(data.items, function(index, value) {
                    $('div#brs_'+index).html(value).removeClass('brs-loading');
                });
            }
        }, 'json');
    }
});