<!-- html template page -->
<?php
// header + title
echo '<!DOCTYPE html><html lang="en"><head>
        <title>WiserCut Frequently Updated Web-based Tool Adviser</title>

        <meta name="robots" content="index,follow">
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <meta name="author" content="WiserCut">
        <meta name="viewport" content="user-scalable=1, width=device-width,initial-scale=1, maximum-scale=1.0, user-scalable=0">
        <meta name="rating" content="general">
        <meta name="audience" content="all">
        <meta name="description" content="WiserCut provides up-to-date information on cutting tools for metal-working manufacturers.">
        <meta name="keywords" content="web-platform,manufacturing,machine-tools,software-applications,cutting-tools">
        <meta name="distribution" content="Global">
        <meta name="generator" content="WiserCut">
        <meta name="page-topic" content="software applications">
        <meta name="resource-type" content="document">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="http-equiv" content="en" http-equiv="content-language">
        <meta name="copyright" content="WiserCut">
        <meta property="og:image" content="Content/images/logo.png">
        <!--<meta property="og:url" content="http://www.wisercut.com/">-->
        <meta property="og:site_name" content="WiserCut">
        <meta property="og:locale" content="he_IL">
        <meta property="og:type" content="website">
        <meta property="og:description" content="Wisercut platform provides you the most innovative technical solutions, from headquarters’ experts of leading tool manufacturers, to your private account.">
        <meta property="og:title" content="WiserCut Frequently Updated Web-based Tool Adviser">
        <!--<base href="http://www.wisercut.com/">-->
        <link rel="shortcut icon" href="fav.ico">
        <!--<link rel="canonical" href="http://www.wisercut.com/">-->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        <link rel="stylesheet" href="Content/css/bootstrap4/bootstrap.min.css">
        <link rel="stylesheet" href="Content/css/libs/sweetalert2.min.css">
        <link rel="stylesheet" href="Content/css/libs/hover-min.css">
        <link rel="stylesheet" href="Content/css/libs/colors.min.css">
        <link rel="stylesheet" href="Content/css/libs/selectize.default.css">
        <link rel="stylesheet" href="Content/css/libs/selectize.legacy.css">
        <link rel="stylesheet" href="Content/css/libs/selectize.css">
        <link rel="stylesheet" href="Scripts/libs/bootstrap-table-master/dist/bootstrap-table.min.css">
        <link rel="stylesheet" href="Scripts/libs/bootstrap-table-master/dist/extensions/sticky-header/bootstrap-table-sticky-header.min.css">
        <link rel="stylesheet" href="Scripts/libs/bootstrap-table-master/dist/extensions/filter-control/bootstrap-table-filter-control.css">
        <link rel="stylesheet" href="Content/css/libs/bootstrap4-toggle.min.css">
        <link rel="stylesheet" href="Content/css/main.css">

        <script src="Scripts/libs/jquery-3.4.1.js"></script>
        <script src="Scripts/libs/popper.min.js" ></script>
        <script src="Scripts/libs/selectize.min.js" ></script>
        <script src="Scripts/libs/bootstrap.min.js" ></script>
        <script src="Scripts/libs/bootstrap-table-master/dist/bootstrap-table.js"></script>
        <script src="Scripts/libs/bootstrap-table-master/dist/extensions/mobile/bootstrap-table-mobile.min.js" ></script>
        <script src="Scripts/libs/bootstrap-table-master/dist/extensions/sticky-header/bootstrap-table-sticky-header.min.js" ></script>
        <script src="Scripts/libs/bootstrap-table-master/dist/extensions/filter-control/bootstrap-table-filter-control.min.js" ></script>
        <script src="Scripts/libs/bootstrap-table-master/dist/extensions/print/bootstrap-table-print.js" ></script>
        <script src="Scripts/libs/bootstrap-table-master/dist/extensions/export/tableExport.min.js" ></script>
        <script src="Scripts/libs/bootstrap-table-master/dist/extensions/export/jspdf.min.js" ></script>
        <script src="Scripts/libs/bootstrap-table-master/dist/extensions/export/jspdf.plugin.autotable.js" ></script>
        <script src="Scripts/libs/bootstrap-table-master/dist/extensions/export/bootstrap-table-export.js" ></script>
        <script src="Scripts/libs/sweetalert2.all.min.js"></script>
        <script src="Scripts/libs/bootstrap4-toggle.min.js"></script>
        <script src="Scripts/libs/tilt.jquery.min.js"></script>
        <script type="module" src="Scripts/main.js"></script>
	</head><body>';

// Top-menu
if (Helper::IsMobile()) {
    echo $this->_buildMenuMobile();
} else {
    echo $this->_buildMenuDesktop();
}

// Default content
require __DIR__.'/../../Partials/'.$page;

// Footer
require_once __DIR__.'/../../Partials/footer.html';

