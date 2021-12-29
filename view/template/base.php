<html>
    <head>
        <title>
            Local Organizer
        </title>
    </head>

    <link href="<?php echo str_replace("/local_organizer", "", $root); ?>/js/bootstrap.css" rel="stylesheet" />
    </script>

<style>
body 
{
    padding : 10px;
}
#main_results_table 
{
    margin: 2%;
}
.edit 
{
    width: 20px;
}
.delete 
{
    width: 20px;
}
.hidden 
{
    display: none;
}
form.object_edit 
{
    border: 1px solid #444444;
    padding: 20px;
}
form.object_edit input 
{
    position: absolute; 
    left: 20%;
}
form.object_edit select 
{
    position: absolute; 
    left: 20%;
}
form.object_edit .field 
{
    font-weight: bold;
    position: absolute;
}
form.object_edit .input_unit
{
    margin-bottom: 10px;
}
form.object_edit .enter 
{
    position: relative;
}
.add_new 
{
    margin-top: 10px;
    margin-right: 20px;
    font-size: 32pt;
    float: right;
}
.add_new a 
{
    outline: none;
    text-decoration: none;
}
</style>

    <body>
<?php
    if ($module != "" && $module != "modules") 
    {
        echo "<h4><a href=\"".$root."\">Back to index</a></h4>";
    }
    else
    {
        echo "<h4>Local organizer main page</h4>";
    }
    if (isset($extra) && sizeof($extra) > 0)
    {
        echo "<h5><a href=\"".$root."/".$module."/view\">Clear filters</a></h5>";
    }
?>
        <div class="" id="main_results_table">
        </div>
<?php
    if ($action == "view") 
    {
        echo "<div class=\"add_new\"><a href=\"". $root."/".$module."/view/id/0\">➕</a></div>";
    }
?>

    <script src="<?php echo str_replace("/local_organizer", "", $root); ?>/js/jquery-3.6.0.min.js"></script>
    <script src="<?php echo str_replace("/local_organizer", "", $root); ?>/js/bootstrap.min.js"></script>
    <script>
        function enable_edits() 
        {
            $('#main_results_table .edit').click(function () 
            {
                var idItem = $(this).nextAll('.id').text();
                document.location = '<?php echo $root."/".$module; ?>/view/id/'+idItem;
            });
        }
        function enable_select() 
        {
            $('#main_results_table tr').dblclick(function () 
            {
                var idItem = $(this).children('.id').text();
                document.location = '<?php echo $root."/".$module; ?>/view/id/'+idItem;
            });
        }
        function enable_deletes() 
        {
            $('#main_results_table .delete').click(function () 
            {
                var idItem = $(this).next('.id').text();
                document.location = '<?php echo $root."/".$module; ?>/delete/id/'+idItem;
            });
        }
    </script>
    </body>
</html>