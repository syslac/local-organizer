<html>
    <head>
        <title>
            Local Organizer
        </title>
    </head>

    <link href="/js/bootstrap.css" rel="stylesheet" />
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

    <script src="/js/jquery-3.6.0.min.js"></script>
    <script src="/js/bootstrap.min.js"></script>
    </body>
</html>