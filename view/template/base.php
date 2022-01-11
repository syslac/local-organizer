<html>
    <head>
        <title>
            Local Organizer
        </title>
    </head>

    <link href="<?php echo str_replace("/local_organizer", "", $root); ?>/js/bootstrap.css" rel="stylesheet" />
    <link href="<?php echo $root; ?>/view/template/base.css" rel="stylesheet" />

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
        echo "<div class=\"add_new\"><a href=\"". $root."/".$module.$add_url."\">➕</a></div>";
    }
?>

    <script src="<?php echo str_replace("/local_organizer", "", $root); ?>/js/jquery-3.6.0.min.js"></script>
    <script src="<?php echo str_replace("/local_organizer", "", $root); ?>/js/bootstrap.min.js"></script>
    </body>
</html>