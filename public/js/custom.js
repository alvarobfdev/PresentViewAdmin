/**
 * Created by alvarobanofos on 31/1/16.
 */

$(function () {
    $('body').on('click', '.cb_select_all', function () {
        var selectAllEle = $(this);
        var id = $(this).attr('data-table-id');
        if(id) {
            $("#"+id+" input[data-selectable]").each(function(){
                $(this).prop('checked', selectAllEle.prop("checked"));
            });
        }
    });
});