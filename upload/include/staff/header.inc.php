<?php
$title = ($ost && ($title=$ost->getPageTitle()))
    ? $title : ('osTicket :: '.__('Staff Control Panel'));

if (!isset($_SERVER['HTTP_X_PJAX'])) { ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html<?php
if (($lang = Internationalization::getCurrentLanguage())
        && ($info = Internationalization::getLanguageInfo($lang))
        && (@$info['direction'] == 'rtl'))
    echo ' dir="rtl" class="rtl"';
if ($lang) {
    echo ' lang="' . Internationalization::rfc1766($lang) . '"';
}
?>>
<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta http-equiv="x-pjax-version" content="<?php echo GIT_VERSION; ?>">
    <meta http-equiv="cache-control" content="max-age=0" />
    <meta http-equiv="cache-control" content="no-cache" />
    <meta http-equiv="expires" content="0" />
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
    <meta http-equiv="pragma" content="no-cache" />
    <title><?php echo Format::htmlchars($title); ?></title>
    <!--[if IE]>
    <style type="text/css">
        .tip_shadow { display:block !important; }
    </style>
    <![endif]-->
    <script type="text/javascript" src="<?php echo ROOT_PATH; ?>js/jquery-1.11.2.min.js?901e5ea"></script>
    <script type="text/javascript" src="<?php echo ROOT_PATH; ?>js/bootstrap.js"></script>
    <script type="text/javascript" src="<?php echo ROOT_PATH; ?>js/bootstrap-notify.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.4.0/Chart.min.js"></script>
    <script type="application/javascript" src="https://ajax.googleapis.com/ajax/libs/angularjs/1.6.3/angular.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.2.23/angular-sanitize.min.js"></script>
    <script type="application/javascript" src="<?php echo ROOT_PATH; ?>js/angular-local_fr-fr.js"></script>

    <!-- Angular Material requires Angular.js Libraries -->
    <script src="http://ajax.googleapis.com/ajax/libs/angularjs/1.6.3/angular-animate.min.js"></script>
    <script src="http://ajax.googleapis.com/ajax/libs/angularjs/1.6.3/angular-aria.min.js"></script>
    <script src="http://ajax.googleapis.com/ajax/libs/angularjs/1.6.3/angular-messages.min.js"></script>

    <!-- Angular Material Library -->
    <script src="http://ajax.googleapis.com/ajax/libs/angular_material/1.1.0/angular-material.min.js"></script>

    <link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/angular_material/1.1.0/angular-material.min.css">
    <link rel="stylesheet" href="<?php echo ROOT_PATH ?>css/bootstrap.css" media="all"/>
    <link rel="stylesheet" href="<?php echo ROOT_PATH ?>css/thread.css?901e5ea" media="all"/>
    <link rel="stylesheet" href="<?php echo ROOT_PATH ?>scp/css/scp.css?901e5ea" media="all"/>
    <link rel="stylesheet" href="<?php echo ROOT_PATH; ?>css/redactor.css?901e5ea" media="screen"/>
    <link rel="stylesheet" href="<?php echo ROOT_PATH ?>scp/css/typeahead.css?901e5ea" media="screen"/>
    <link type="text/css" href="<?php echo ROOT_PATH; ?>css/ui-lightness/jquery-ui-1.10.3.custom.min.css?901e5ea"
         rel="stylesheet" media="screen" />
     <link type="text/css" rel="stylesheet" href="<?php echo ROOT_PATH; ?>css/font-awesome.min.css?901e5ea"/>
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css">
    <!--[if IE 7]>
    <link rel="stylesheet" href="<?php echo ROOT_PATH; ?>css/font-awesome-ie7.min.css?901e5ea"/>
    <![endif]-->
    <link type="text/css" rel="stylesheet" href="<?php echo ROOT_PATH ?>scp/css/dropdown.css?901e5ea"/>
    <link type="text/css" rel="stylesheet" href="<?php echo ROOT_PATH; ?>css/loadingbar.css?901e5ea"/>
    <link type="text/css" rel="stylesheet" href="<?php echo ROOT_PATH; ?>css/flags.css?901e5ea"/>
    <link type="text/css" rel="stylesheet" href="<?php echo ROOT_PATH; ?>css/select2.min.css?901e5ea"/>
    <link type="text/css" rel="stylesheet" href="<?php echo ROOT_PATH; ?>css/rtl.css?901e5ea"/>
    <link type="text/css" rel="stylesheet" href="<?php echo ROOT_PATH ?>scp/css/translatable.css?901e5ea"/>
    <link href="https://fonts.googleapis.com/css?family=Quicksand:500" rel="stylesheet">

    <?php
    if($ost && ($headers=$ost->getExtraHeaders())) {
        echo "\n\t".implode("\n\t", $headers)."\n";
    }
    ?>
    <script>
        var app = angular.module('myApp',['ngSanitize']);
    </script>

</head>
<body ng-app="myApp">
<div id="container">
    <?php
    if($ost->getError())
        echo sprintf('<div id="error_bar">%s</div>', $ost->getError());
    elseif($ost->getWarning())
        echo sprintf('<div id="warning_bar">%s</div>', $ost->getWarning());
    elseif($ost->getNotice())
        echo sprintf('<div id="notice_bar">%s</div>', $ost->getNotice());
    ?>
    <div id="pjax-container" class="<?php if ($_POST) echo 'no-pjax'; ?>">
<?php } else {
    header('X-PJAX-Version: ' . GIT_VERSION);
    if ($pjax = $ost->getExtraPjax()) { ?>
    <script type="text/javascript">
    <?php foreach (array_filter($pjax) as $s) echo $s.";"; ?>
    </script>
    <?php }
    foreach ($ost->getExtraHeaders() as $h) {
        if (strpos($h, '<script ') !== false)
            echo $h;
    } ?>
    <title><?php echo ($ost && ($title=$ost->getPageTitle()))?$title:'osTicket :: '.__('Staff Control Panel'); ?></title><?php
} # endif X_PJAX ?>
    <!-- id="content"-->
    <div class="container">

      <div style="background-color: #f8f8f8;
                  padding-bottom: 30px;
                  position: absolute;
                  top: 0px;
                  left: 0px;
                  width: 100%;
                  height:100%">

      <div style="width: 230px;
         position: fixed;
         left: 0px;
         height: 100%;">
            <nav class="navbar navbar-default" style="">
              <div class="">
                <!-- Brand and toggle get grouped for better mobile display -->
                <div class="navbar-header">
                  <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                  </button>
              <!--<a class="navbar-brand" href="#">Brand</a>-->
            </div>
                <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">

                <?php
                    $agent = Staff::objects();
                    $agent->filter(array('firstname'=>$thisstaff->getFirstName(),
                                     'lastname'=>$thisstaff->getLastName()));
                    $agent->values('avatar','firstname','lastname');
                    $avatar = $agent[0]['avatar'];
                ?>

                <div class="connected_agent"/>
                    <div class="img">
                        <img class="<?php echo 'pull-left'; ?> avatar" src="../assets/avatar/<?php echo $avatar ?>"></img>
                    </div>
                    <div class="agent_info">
                        <h6 class="agent_name"><?= $agent[0]['firstname'] . ' ' . $agent[0]['lastname'] ?></h6>
                        <div class="online">
                            <div class="online_point"></div>
                            <span class="online_text">Online</span>
                        </div>
                    </div>
                </div>

              <ul class="nav navbar-nav">

                <?php include STAFFINC_DIR . "templates/navigation.tmpl.php"; ?>

              </ul>
            </div><!-- /.navbar-collapse -->
            </div><!-- /.container-fluid -->
            </nav>
       </div>

       <div id="header">
           <a href="<?php echo ROOT_PATH ?>scp/index.php" class="no-pjax" id="logo">
               <span class="valign-helper"></span>
               <img src="<?php echo ROOT_PATH ?>scp/logo.php?<?php echo strtotime($cfg->lastModified('staff_logo_id')); ?>" alt="osTicket &mdash; <?php echo __('Customer Support System'); ?>"/>
           </a>
           <p id="info" class="no-pjax"><?php echo sprintf(__('Welcome, %s.'), '<strong>'.$thisstaff->getFirstName().'</strong>'); ?>
              <?php
               if($thisstaff->isAdmin() && !defined('ADMINPAGE')) { ?>
               <a href="<?php echo ROOT_PATH ?>scp/admin.php" class="no-pjax" title="Panneau d'administration"><span class="glyphicon glyphicon-wrench"></span></a>
               <?php }else{ ?>
               <a href="<?php echo ROOT_PATH ?>scp/index.php" class="no-pjax"><?php echo __('Agent Panel'); ?></a>
               <?php } ?>
               <a href="<?php echo ROOT_PATH ?>scp/profile.php" title="Profile"><span class="glyphicon glyphicon-user"></span></a>
               <a href="<?php echo ROOT_PATH ?>scp/logout.php?auth=<?php echo $ost->getLinkToken(); ?>" title="Déconnexion" class="no-pjax"><span class="glyphicon glyphicon-log-out"></span></a>
           </p>
        </div>

       <div class="col-md-12 pageContainer">
       <!--<div id="header">
            <a href="<?php echo ROOT_PATH ?>scp/index.php" class="no-pjax" id="logo">
                <span class="valign-helper"></span>
                <img src="<?php echo ROOT_PATH ?>scp/logo.php?<?php echo strtotime($cfg->lastModified('staff_logo_id')); ?>" alt="osTicket &mdash; <?php echo __('Customer Support System'); ?>"/>
            </a>
            <p id="info" class="no-pjax"><?php echo sprintf(__('Welcome, %s.'), '<strong>'.$thisstaff->getFirstName().'</strong>'); ?>
               <?php
                if($thisstaff->isAdmin() && !defined('ADMINPAGE')) { ?>
                <span class="pipe">|</span> <a href="<?php echo ROOT_PATH ?>scp/admin.php" class="no-pjax"><?php echo __('Admin Panel'); ?></a>
                <?php }else{ ?>
                <span class="pipe">|</span> <a href="<?php echo ROOT_PATH ?>scp/index.php" class="no-pjax"><?php echo __('Agent Panel'); ?></a>
                <?php } ?>
                <span class="pipe">|</span> <a href="<?php echo ROOT_PATH ?>scp/profile.php"><?php echo __('Profile'); ?></a>
                <span class="pipe">|</span> <a href="<?php echo ROOT_PATH ?>scp/logout.php?auth=<?php echo $ost->getLinkToken(); ?>" class="no-pjax"><?php echo __('Log Out'); ?></a>
            </p>
        </div>-->

        <ul id="sub_nav">
            <div class="sub">
    <?php include STAFFINC_DIR . "templates/sub-navigation.tmpl.php"; ?>
            </div>
        </ul>

        <?php if($errors['err']) { ?>
            <div id="msg_error"><?php echo $errors['err']; ?></div>
        <?php }elseif($msg) { ?>
            <div id="msg_notice"><?php echo $msg; ?></div>
        <?php }elseif($warn) { ?>
            <div id="msg_warning"><?php echo $warn; ?></div>
        <?php }
        foreach (Messages::getMessages() as $M) { ?>
            <div class="<?php echo strtolower($M->getLevel()); ?>-banner"><?php
                echo (string) $M; ?></div>
<?php   } ?>
