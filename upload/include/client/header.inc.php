<?php
$title=($cfg && is_object($cfg) && $cfg->getTitle())
    ? $cfg->getTitle() : 'osTicket :: '.__('Support Ticket System');
$signin_url = ROOT_PATH . "login.php"
    . ($thisclient ? "?e=".urlencode($thisclient->getEmail()) : "");
$signout_url = ROOT_PATH . "logout.php?auth=".$ost->getLinkToken();

header("Content-Type: text/html; charset=UTF-8");
if (($lang = Internationalization::getCurrentLanguage())) {
    $langs = array_unique(array($lang, $cfg->getPrimaryLanguage()));
    $langs = Internationalization::rfc1766($langs);
    header("Content-Language: ".implode(', ', $langs));
}
?>
<!DOCTYPE html>
<html<?php
if ($lang
        && ($info = Internationalization::getLanguageInfo($lang))
        && (@$info['direction'] == 'rtl'))
    echo ' dir="rtl" class="rtl"';
if ($lang) {
    echo ' lang="' . $lang . '"';
}
?>>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title><?php echo Format::htmlchars($title); ?></title>
    <meta name="description" content="customer support platform">
    <meta name="keywords" content="osTicket, Customer support system, support ticket system">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="<?php echo ROOT_PATH; ?>css/osticket.css?901e5ea" media="screen"/>
    <link rel="stylesheet" href="<?php echo ASSETS_PATH; ?>css/theme.css?901e5ea" media="screen"/>
    <link rel="stylesheet" href="<?php echo ASSETS_PATH; ?>css/print.css?901e5ea" media="print"/>
    <link rel="stylesheet" href="<?php echo ROOT_PATH; ?>scp/css/typeahead.css?901e5ea"
         media="screen" />
    <link type="text/css" href="<?php echo ROOT_PATH; ?>css/ui-lightness/jquery-ui-1.10.3.custom.min.css?901e5ea"
        rel="stylesheet" media="screen" />
    <link rel="stylesheet" href="<?php echo ROOT_PATH; ?>css/thread.css?901e5ea" media="screen"/>
    <link rel="stylesheet" href="<?php echo ROOT_PATH; ?>css/redactor.css?901e5ea" media="screen"/>
    <link type="text/css" rel="stylesheet" href="<?php echo ROOT_PATH; ?>css/font-awesome.min.css?901e5ea"/>
    <link type="text/css" rel="stylesheet" href="<?php echo ROOT_PATH; ?>css/flags.css?901e5ea"/>
    <link type="text/css" rel="stylesheet" href="<?php echo ROOT_PATH; ?>css/rtl.css?901e5ea"/>
    <link type="text/css" rel="stylesheet" href="<?php echo ROOT_PATH; ?>css/select2.min.css?901e5ea"/>
    <link type="text/css" rel="stylesheet" href="<?php echo ROOT_PATH; ?>css/bootstrap.css"/>
    <link href="https://fonts.googleapis.com/css?family=PT+Sans:700" rel="stylesheet">
    <script type="text/javascript" src="<?php echo ROOT_PATH; ?>js/jquery-1.11.2.min.js?901e5ea"></script>
    <script type="text/javascript" src="<?php echo ROOT_PATH; ?>js/jquery-ui-1.10.3.custom.min.js?901e5ea"></script>
    <script src="<?php echo ROOT_PATH; ?>js/osticket.js?901e5ea"></script>
    <script type="text/javascript" src="<?php echo ROOT_PATH; ?>js/filedrop.field.js?901e5ea"></script>
    <script src="<?php echo ROOT_PATH; ?>scp/js/bootstrap-typeahead.js?901e5ea"></script>
    <script type="text/javascript" src="<?php echo ROOT_PATH; ?>js/redactor.min.js?901e5ea"></script>
    <script type="text/javascript" src="<?php echo ROOT_PATH; ?>js/redactor-plugins.js?901e5ea"></script>
    <script type="text/javascript" src="<?php echo ROOT_PATH; ?>js/redactor-osticket.js?901e5ea"></script>
    <script type="text/javascript" src="<?php echo ROOT_PATH; ?>js/select2.min.js?901e5ea"></script>
    <script type="text/javascript" src="<?php echo ROOT_PATH; ?>js/fabric.min.js?901e5ea"></script>
    <script type="text/javascript" src="<?php echo ROOT_PATH; ?>js/bootstrap.js"></script>

    <?php
    if($ost && ($headers=$ost->getExtraHeaders())) {
        echo "\n\t".implode("\n\t", $headers)."\n";
    }

    // Offer alternate links for search engines
    // @see https://support.google.com/webmasters/answer/189077?hl=en
    if (($all_langs = Internationalization::getConfiguredSystemLanguages())
        && (count($all_langs) > 1)
    ) {
        $langs = Internationalization::rfc1766(array_keys($all_langs));
        $qs = array();
        parse_str($_SERVER['QUERY_STRING'], $qs);
        foreach ($langs as $L) {
            $qs['lang'] = $L; ?>
        <link rel="alternate" href="//<?php echo $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>?<?php
            echo http_build_query($qs); ?>" hreflang="<?php echo $L; ?>" />
<?php
        } ?>
        <link rel="alternate" href="//<?php echo $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>"
            hreflang="x-default" />
<?php
    }
    ?>

    <?php
    require_once("./scp/Request/Tickets.php");
    ?>
</head>
<body>
   <div class="connected">
            <p>
             <?php
                if ($thisclient && is_object($thisclient) && $thisclient->isValid()
                    && !$thisclient->isGuest()) {
                 echo Format::htmlchars($thisclient->getName()).'&nbsp;|';
                 ?>
                <a href="<?php echo ROOT_PATH; ?>profile.php"><?php echo __('Profile'); ?></a> |
                <a href="<?php echo ROOT_PATH; ?>tickets.php"><?php echo sprintf(__('Tickets <b>(%d)</b>'), TicketsInfos::getInstance()->numberOfOpenTicketsForUser($thisclient->getId())); ?></a> -
                <a href="<?php echo $signout_url; ?>"><?php echo __('Sign Out'); ?></a>
            <?php
            } elseif($nav) {
                if ($cfg->getClientRegistrationMode() == 'public') { ?>
                    <?php echo __('Guest User'); ?> | <?php
                }
                if ($thisclient && $thisclient->isValid() && $thisclient->isGuest()) { ?>
                    <a href="<?php echo $signout_url; ?>"><?php echo __('Sign Out'); ?></a><?php
                }
                elseif ($cfg->getClientRegistrationMode() != 'disabled') { ?>
                    <a href="<?php echo $signin_url; ?>"><?php echo __('Sign In'); ?></a>
            <?php
                            }
                        } ?>
            <?php
            if (($all_langs = Internationalization::getConfiguredSystemLanguages())
                && (count($all_langs) > 1)
            ) {
                $qs = array();
                parse_str($_SERVER['QUERY_STRING'], $qs);
                foreach ($all_langs as $code=>$info) {
                    list($lang, $locale) = explode('_', $code);
                    $qs['lang'] = $code;
            ?>
                    <a class="flag flag-<?php echo strtolower($locale ?: $info['flag'] ?: $lang); ?>"
                        href="?<?php echo http_build_query($qs);
                        ?>" title="<?php echo Internationalization::getLanguageDescription($code); ?>">&nbsp;</a>
            <?php }
            } ?>
            </p>
            </div>
            <div id="indexTrianglify">
            <div id="background">
            <div id="logo_menu">
            <a class="pull-left" id="logo" href="/osTicket/upload/index.php" title="HELPDESK">
                <span class="valign-helper"></span>
                <img src="/osTicket/upload/logo.php" border="0" alt="VienneDoc Ticket">
            </a>
                    <?php
        if($nav){ ?>
        <ul id="nav" class="flush-left">
            <?php
            if($nav && ($navs=$nav->getNavLinks()) && is_array($navs)){
                foreach($navs as $name =>$nav) {
                    echo sprintf('<li><a class="%s %s" href="%s">%s</a></li>%s',$nav['active']?'active':'',$name,(ROOT_PATH.$nav['href']),$nav['desc'],"\n");
                }
            } ?>
        </ul>
        <?php
        }else{ ?>
                 <hr>
        <?php
        } ?>
    </div>
    <?php

    echo "<div class='clear'></div>";
    if(strstr(strtolower("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"),"index.php") != false ||
      strtolower("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]") == strtolower("http://$_SERVER[HTTP_HOST]/osTicket/upload/")){
        echo '<div id="presentation">
           <p >Bienvenue sur notre centre de support</p>
           <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam faucibus dolor at neque iaculis ultricies. Nulla egestas ex ac pharetra iaculis. Phasellus nec massa nec est faucibus auctor. Suspendisse nec erat est. Etiam turpis lacus, scelerisque ac nulla a, porta vehicula mi. Aenean finibus a neque ac condimentum. Nullam placerat nunc sit amet ante cursus facilisis. Integer nec egestas purus, et imperdiet massa. Nullam eu accumsan mauris, id luctus ex. Maecenas feugiat libero sed enim placerat lobortis. Cras eget condimentum massa.</p>
       </div>';

    echo "</div></div>";
       /* break;*/
    } else if(strstr(strtolower("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"),"login.php") != false){
        echo '<div id="presentationConnexion">
           <p>Connexion</p>';
        echo "</div></div>";
    } else if(strstr(strtolower("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"),"view.php") != false){
        echo '<div id="presentationConnexion">
           <p>Vérifier le statut d\'un ticket</p>';
        echo "</div></div>";
    } else if(strstr(strtolower("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"),"open.php") != false ||
             strstr(strtolower("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"),"tickets.php") != false){
        if (strstr(strtolower("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"),"tickets.php?id=") != false){
            echo '<div id="presentationConnexion">';
            echo '<p>Ticket '. $ticket->getNumber() .'</p>';
            echo "</div></div>";
        } else if(strstr(strtolower("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"),"tickets.php") != false){
            echo '<div id="presentationConnexion">
               <p>Listes des tickets</p>';
            echo "</div></div>";
        } else if(strstr(strtolower("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"),"open.php?") != false){
            echo '<div id="presentationConnexion">
                <p>Nouveau ticket</p>';
            echo "</div></div></div>";
        } else {
            echo '<div id="presentationConnexion">
               <p>Connexion</p>';
            echo "</div></div></div>";
        }
    }
    else if(strstr(strtolower("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"),"profile.php") != false){
            echo '<div id="presentationConnexion">
               <p>Gérer vos informations de profil</p>';
            echo "</div></div>";
    }
    else if(strstr(strtolower("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"),"account.php?do=create") != false ||
           $_POST['do'] == 'create'){
            echo '<div id="presentationConnexion">
               <p>Enregistrement d\'un compte</p>';
            echo "</div></div>";
    }
       ?>
                </div>
       <div id='container'>
        <!--<div id="header">

        </div>-->
        <div class="clear"></div>

         <?php if($errors['err']) { ?>
            <div id="msg_error"><?php echo $errors['err']; ?></div>
         <?php }elseif($msg) { ?>
            <div id="msg_notice"><?php echo $msg; ?></div>
         <?php }elseif($warn) { ?>
            <div id="msg_warning"><?php echo $warn; ?></div>
         <?php } ?>
