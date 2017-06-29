<script type="text/javascript" src="../js/autosize.js"></script>
<script type="text/javascript" src="./js/atelier.js"></script>


<?php
require('staff.inc.php');
require(INCLUDE_DIR.'class.org.php');
//require_once(INCLUDE_DIR.'class.ticket.php');
//require_once(INCLUDE_DIR.'class.dept.php');
//require_once(INCLUDE_DIR.'class.filter.php');
//require_once(INCLUDE_DIR.'class.canned.php');
//require_once(INCLUDE_DIR.'class.json.php');
//require_once(INCLUDE_DIR.'class.dynamic_forms.php');
//require_once(INCLUDE_DIR.'class.export.php');       // For paper sizes
$nav->setActiveTab('atelier');

$nav->addSubMenu(array('desc'=>'Tickets',
                        'title'=>'Tickets Atelier',
                        'href'=>'atelier.php?tabs=tickets',
                        'iconclass'=>'Ticket no-pjax'));

$nav->addSubMenu(array('desc'=>'Plan',
                        'title'=>'Plan de l\'atelier',
                        'href'=>'atelier.php?tabs=plan',
                        'iconclass'=>'Plan no-pjax'));

$nav->addSubMenu(array('desc'=>'Liste des VD',
                        'title'=>'Liste des VD',
                        'href'=>'atelier.php?tabs=vd',
                        'iconclass'=>'Ticket no-pjax'));

if(isset($_REQUEST['tabs'])){
     if($_REQUEST['tabs'] == 'tickets'){
         $nav->setActiveSubMenu(1);
         $inc = 'atelier_tickets.inc.php';
     } else if($_REQUEST['tabs'] == 'plan') {
         $nav->setActiveSubMenu(2);
         $inc = 'atelier_plan.inc.php';
     } else {
       $nav->setActiveSubMenu(3);
       $inc = 'atelier_vd.inc.php';
     }
} else {
    $nav->setActiveSubMenu(1);
    $inc = 'atelier_tickets.inc.php';
}

require_once(STAFFINC_DIR.'header.inc.php');
require_once(STAFFINC_DIR.$inc);
require_once(STAFFINC_DIR.'footer.inc.php');

?>
