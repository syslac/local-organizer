<script>
function enable_edits() 
{
    $('#main_results_table .edit').click(function () 
    {
        var idItem = $(this).nextAll('.id').text();
        document.location = '<?php echo $root."/".$module.$edit_url; ?>'+idItem;
    });
}
function enable_select() 
{
    $('#main_results_table tr').dblclick(function () 
    {
        var idItem = $(this).children('.id').text();
        document.location = '<?php echo $root."/".$module.$edit_url; ?>'+idItem;
    });
}
function enable_deletes() 
{
    $('#main_results_table .delete').click(function () 
    {
        var idItem = $(this).nextAll('.id').text();
        document.location = '<?php echo $root."/".$module.$delete_url; ?>'+idItem;
    });
}
function compute_dones() 
{
    $('td.done').each(function ()
    {
        var val = $(this).text();
        if (val == '1') 
        {
            $(this).parent().addClass('done');
        }
    });
}
function enable_mtms() 
{
    $('td.mtm').each(function ()
    {
        var mtms = $(this).text().split(',');
        if (mtms.length == 0)
        {
            return;
        }
        $(this).html('');
        mtms.forEach((t) => 
        {
            if (t === '') 
            {
                return;
            }
            var cont = $('<span>')
                .addClass('tag_container')
                .mouseover(function() 
                {
                    $(this).children('.del_tag').css('display', 'inline');
                })
                .mouseout(function() 
                {
                    $(this).children('.del_tag').css('display', 'none');
                })
                .appendTo($(this));
            $('<span>')
                .html('&nbsp;')
                .appendTo($(this));
            $('<a>')
                .prop('href', '<?php echo $root."/".$module.$filter_tag_url; ?>'+t)
                .text(t)
                .appendTo(cont);
            var id_item = $(this).closest('tr').find('td.id').text();
            $('<a>')
                .prop('href', '<?php echo $root."/".$module; ?>/del_mtm/table/lo_<?php echo strtolower($module); ?>_tags/id_<?php echo strtolower($module); ?>/'+id_item+'/tag/'+t)
                .html('❌')
                .addClass('del_tag')
                .css('display', 'none')
                .appendTo(cont);
        })
    });
    $('td.add_tag').each(function ()
    {
        if ($(this).parent('tr').children('td.mtm').length <= 0) 
        {
            $(this).html('');
            return;
        }
        $(this).click(function() {
            $(this).unbind('click');
            $(this).text('');
            var form = $('<form>')
                .attr('action', '<?php echo $root."/".$module.$add_tag_url; ?>')
                .attr('method', 'POST');
            $(this).append(form);
            $(this).find('form').html('<?php echo str_replace(
                array("\n", "\r"), 
                "", 
                $new_tag_form);
                ?>');
            $(this).find('form').append($('<input>')
                .attr('type', 'hidden')
                .attr('name', 'id_<?php echo strtolower($module); ?>')
                .attr('value', $(this).closest('tr').find('td.id').text())
            );
            $(this).find('form').append($('<input>')
                .attr('type', 'hidden')
                .attr('name', 'table')
                .attr('value', 'lo_<?php echo strtolower($module); ?>_tags')
            );
            $(this).find('select').change(function () 
            {
                $(this).closest('form').submit();    
            })
        });
    });
}
</script>