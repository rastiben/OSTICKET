<?php
if(!defined('OSTCLIENTINC') || !is_object($thisclient) || !$thisclient->isValid()) die('Access Denied');

require_once("./scp/Request/Tickets.php");

$settings = &$_SESSION['client:Q'];

// Unpack search, filter, and sort requests
if (isset($_REQUEST['clear']))
    $settings = array();
if (isset($_REQUEST['keywords'])) {
    $settings['keywords'] = $_REQUEST['keywords'];
}
if (isset($_REQUEST['topic_id'])) {
    $settings['topic_id'] = $_REQUEST['topic_id'];
}
if (isset($_REQUEST['status'])) {
    $settings['status'] = $_REQUEST['status'];
}

$org_tickets = $thisclient->canSeeOrgTickets();
if ($settings['keywords']) {
    // Don't show stat counts for searches
    $openTickets = $closedTickets = -1;
}
elseif ($settings['topic_id']) {
    $openTickets = $thisclient->getNumTopicTicketsInState($settings['topic_id'],
        'open', $org_tickets);
    $closedTickets = $thisclient->getNumTopicTicketsInState($settings['topic_id'],
        'closed', $org_tickets);
}
else {
    $openTickets = $thisclient->getNumOpenTickets($org_tickets);
    $closedTickets = $thisclient->getNumClosedTickets($org_tickets);
}

$tickets = Ticket::objects();

$qs = array();
$status=null;

$sortOptions=array('id'=>'number', 'subject'=>'cdata__subject',
                    'status'=>'status__name', 'dept'=>'dept__name','date'=>'created');
$orderWays=array('DESC'=>'-','ASC'=>'');
//Sorting options...
$order_by=$order=null;
$sort=($_REQUEST['sort'] && $sortOptions[strtolower($_REQUEST['sort'])])?strtolower($_REQUEST['sort']):'date';
if($sort && $sortOptions[$sort])
    $order_by =$sortOptions[$sort];

$order_by=$order_by ?: $sortOptions['date'];
if ($_REQUEST['order'] && $orderWays[strtoupper($_REQUEST['order'])])
    $order = $orderWays[strtoupper($_REQUEST['order'])];
else
    $order = $orderWays['DESC'];

$x=$sort.'_sort';
$$x=' class="'.strtolower($_REQUEST['order'] ?: 'desc').'" ';

$basic_filter = Ticket::objects();
if ($settings['topic_id']) {
    $basic_filter = $basic_filter->filter(array('topic_id' => $settings['topic_id']));
}

if ($settings['status'])
    $status = strtolower($settings['status']);
    switch ($status) {
    default:
        $status = 'open';
    case 'open':
    case 'closed':
		$results_type = ($status == 'closed') ? __('Closed Tickets') : __('Open Tickets');
        $basic_filter->filter(array('status__state' => $status));
        break;
}

// Add visibility constraints — use a union query to use multiple indexes,
// use UNION without "ALL" (false as second parameter to union()) to imply
// unique values
$visibility = $basic_filter->copy()
    ->values_flat('ticket_id')
    ->filter(array('user_id' => $thisclient->getId()))
    ->union($basic_filter->copy()
        ->values_flat('ticket_id')
        ->filter(array('thread__collaborators__user_id' => $thisclient->getId()))
    , false);

if ($thisclient->canSeeOrgTickets()) {
    $visibility = $visibility->union(
        $basic_filter->copy()->values_flat('ticket_id')
            ->filter(array('user__org_id' => $thisclient->getOrgId()))
    , false);
}

// Perform basic search
if ($settings['keywords']) {
    $q = trim($settings['keywords']);
    if (is_numeric($q)) {
        $tickets->filter(array('number__startswith'=>$q));
    } elseif (strlen($q) > 2) { //Deep search!
        // Use the search engine to perform the search
        $tickets = $ost->searcher->find($q, $tickets);
    }
}

$tickets->distinct('ticket_id');

TicketForm::ensureDynamicDataView();

$total=$visibility->count();
$page=($_GET['p'] && is_numeric($_GET['p']))?$_GET['p']:1;
$pageNav=new Pagenate($total, $page, PAGE_LIMIT);
$qstr = '&amp;'. Http::build_query($qs);
$qs += array('sort' => $_REQUEST['sort'], 'order' => $_REQUEST['order']);
$pageNav->setURL('tickets.php', $qs);
$tickets->filter(array('ticket_id__in' => $visibility));
$pageNav->paginate($tickets);

$showing =$total ? $pageNav->showing() : "";
if(!$results_type)
{
	$results_type=ucfirst($status).' '.__('Tickets');
}
$showing.=($status)?(' '.$results_type):' '.__('All Tickets');
if($search)
    $showing=__('Search Results').": $showing";

$negorder=$order=='-'?'ASC':'DESC'; //Negate the sorting

$tickets->order_by($order.$order_by);
$tickets->values(
    'ticket_id', 'number', 'created', 'isanswered', 'source', 'status_id',
    'status__state', 'status__name', 'cdata__subject', 'dept_id',
    'dept__name', 'dept__ispublic', 'user__default_email__address'
);

?>
<div id="ticketList">
<div class="search well">
<div class="flush-left">
<form action="tickets.php" method="get" id="ticketSearchForm">
    <input type="hidden" name="a"  value="search">
    <input type="text" name="keywords" size="30" value="<?php echo Format::htmlchars($settings['keywords']); ?>">
    <!--<select name="topic_id" class="nowarn" onchange="javascript: this.form.submit(); ">
        <option value="">&mdash; <?php echo __('All Help Topics');?> &mdash;</option>
        <?php
        foreach (Topic::getHelpTopics(true) as $id=>$name) {
                $count = $thisclient->getNumTopicTickets($id, $org_tickets);
                if ($count == 0)
                    continue;
        ?>
                <option value="<?php echo $id; ?>"
                    <?php if ($settings['topic_id'] == $id) echo 'selected="selected"'; ?>
                    ><?php echo sprintf('%s (%d)', Format::htmlchars($name),
                        $thisclient->getNumTopicTickets($id)); ?></option>
        <?php } ?>
    </select>-->
    <input type="submit" value="<?php echo __('Search');?>">
    <a href="<?php echo Format::htmlchars($_SERVER['REQUEST_URI']); ?>"
        >
    <?php echo __('Rafraîchir'); ?>
    </a>
</form>
</div>

<?php if ($settings['keywords'] || $settings['topic_id'] || $_REQUEST['sort']) { ?>
<div style="margin-top:10px"><strong><a href="?clear" style="color:#777"><i class="icon-remove-circle"></i> <?php echo __('Clear all filters and sort'); ?></a></strong></div>
<?php } ?>

</div>

<div class="tickets">
<!--<h1 style="margin:10px 0">

<!--<div class="pull-right states">
    <small>
<?php if ($openTickets) { ?>
    <i class="icon-file-alt"></i>
    <a class="state <?php if ($status == 'open') echo 'active'; ?>"
        href="?<?php echo Http::build_query(array('a' => 'search', 'status' => 'open')); ?>">
    <?php echo _P('ticket-status', 'Open'); if ($openTickets > 0) echo sprintf(' (%d)', $openTickets); ?>
    </a>
    <?php if ($closedTickets) { ?>
    &nbsp;
    <span style="color:lightgray">|</span>
    <?php }
}
if ($closedTickets) {?>
    &nbsp;
    <i class="icon-file-text"></i>
    <a class="state <?php if ($status == 'closed') echo 'active'; ?>"
        href="?<?php echo Http::build_query(array('a' => 'search', 'status' => 'closed')); ?>">
    <?php echo __('Closed'); if ($closedTickets > 0) echo sprintf(' (%d)', $closedTickets); ?>
    </a>
<?php } ?>
    </small>
</div>
</h1>-->
<table class="table tickets" border="0" cellspacing="0" cellpadding="0" style="word-break: break-word;">
    <caption>
        <span>Tickets : </span>
        <span style="color:#006699;cursor:pointer">Ouverts</span> -
        <span style="cursor:pointer">Fermés</span>
    </caption>
    <thead>
        <tr>
            <th width="140">
                <p><?php echo __('TICKET #');?></p>
            </th>
            <th width="150">
                <p><?php echo __('DATE DE CREATION');?></p>
            </th>
            <th width="280">
                <p><?php echo __('SUJET');?></p>
            </th>
            <th width="280">
                <p><?php echo __('AUTEUR');?></p>
            </th>
        </tr>
    </thead>
    <tbody>
    <?php
        //keywords
        $keywords = $_GET['keywords'];
        $ticketsClose = TicketsInfos::getInstance()->ticket_close_org($thisclient->getId());
        $ticketsOpen = TicketsInfos::getInstance()->ticket_open_org($thisclient->getId());
        $iO = 0;
        foreach($ticketsOpen as $ticket){
            if(empty($keywords) ||
                strpos(strtolower($ticket['number']),strtolower($keywords)) !== FALSE ||
                strpos(strtolower(substr($ticket['created'],0,10)),strtolower($keywords)) !== FALSE ||
                strpos(strtolower($ticket['subject']),strtolower($keywords)) !== FALSE ||
                strpos(strtolower($ticket['name'] . ' ' . $ticket['firsname']),strtolower($keywords)) !== FALSE )
            {
    ?>
    <tr class="open <?php if($iO < 25) echo " active"; ?>" id="<?php echo $ticket['ticket_id']; ?>">
        <td><a class="Icon Ticket" href="./tickets.php?id=<?php echo $ticket['ticket_id'] ?>"><?php echo $ticket['number'] ?></a></td>
        <td><?php echo substr($ticket['created'],0,10) ?></td>
        <td><?php echo $ticket['subject'] ?></td>
        <td><?php echo ucwords($ticket['name'] . ' ' . $ticket['firsname']) ?></td>
    </tr>
    <?php
            $iO++;
            }
        }
        ?>
    <?php
        $iC = 0;
        foreach($ticketsClose as $ticket){
            if(empty($keywords) ||
                strpos(strtolower($ticket['number']),strtolower($keywords)) !== FALSE ||
                strpos(strtolower(substr($ticket['created'],0,10)),strtolower($keywords)) !== FALSE ||
                strpos(strtolower($ticket['subject']),strtolower($keywords)) !== FALSE ||
                strpos(strtolower($ticket['name'] . ' ' . $ticket['firsname']),strtolower($keywords)) !== FALSE )
            {
    ?>
    <tr class="closed" id="<?php echo $ticket['ticket_id']; ?>">
        <td><a class="Icon Ticket" href="./tickets.php?id=<?php echo $ticket['ticket_id'] ?>"><?php echo $ticket['number'] ?></a></td>
        <td><?php echo substr($ticket['created'],0,10) ?></td>
        <td><?php echo $ticket['subject'] ?></td>
        <td><?php echo ucwords($ticket['name'] . ' ' . $ticket['firsname']) ?></td>
    </tr>
    <?php
        $iC++;
            }
        }
        ?>
    </tbody>
</table>
<?php
    $count = $iO;
    echo '<span style="color:#777">Pages : </span>';
    echo '<div class="openPage">';
    for($i = 0; $i<($count/25);$i++){
        echo '<span class="page" style="' . ($i == 0 ? 'color:#006699;' : 'color:#777;') . 'cursor:pointer" >'.($i+1).'</span>' . (($i+1)<($count/25) ? ' - ' : '');
    }
    echo '</div>';
    $count = $iC;
    echo '<div class="closePage" style="display:none;">';
    for($i = 0; $i<($count/25);$i++){
        echo '<span class="page" style="' . ($i == 0 ? 'color:#006699;' : 'color:#777;') . 'cursor:pointer" >'.($i+1).'</span>' . (($i+1)<($count/25) ? ' - ' : '');
    }
    echo '</div>';
?>
</div>
</div>

<script>

    var oPage = 1;
    var cPage = 1;

    $('table caption span').click(function(){
        if($(this).text() != "Tickets : "){
            /*SWITCH OUVERTS-FERMES*/
            var spans = $(this).siblings().not(':first');
            $(this).css('color','#006699');
            $(spans).css('color','##777');
            /*SWITCH PAGE AND TR*/
            if($(this).text() == 'Ouverts'){
                var tr = $('table.tickets tbody tr.open').splice(0,25);
                $('table.tickets tbody tr.closed').removeClass('active');
                $('.openPage').css('display','inline-block');
                $('.closePage').css('display','none');
                oPage = 1;
                $('.openPage span').css('color','#777');
                $('.openPage span:first').css('color','#006699');
            } else {
                var tr = $('table.tickets tbody tr.closed').splice(0,25);
                $('table.tickets tbody tr.open').removeClass('active');
                $('.closePage').css('display','inline-block');
                $('.openPage').css('display','none');
                cPage = 1;
                $('.closePage span').css('color','#777');
                $('.closePage span:first').css('color','#006699');
            }
            $(tr).addClass('active');
        }
    });

    $('.page').click(function(){
        /*SWITCH SPANS*/
        var spans = $(this).siblings("span");
        $(this).css('color','#006699');
        $(spans).css('color','##777');
        /*SWITCH LIST*/
        if($(this).parent().hasClass('openPage')){
            if(parseInt($(this).text()) > oPage){
                var actives = $('.open.active:last').nextAll('.open').splice(0,25);
            } else {
                var actives = $('.open.active:first').prevAll('.open').splice(0,25);
            }
            $('.open.active').removeClass('active');
            $(actives).addClass('active');
            oPage = parseInt($(this).text());
        } else {
            if(parseInt($(this).text()) > cPage){
                var actives = $('.closed.active:last').nextAll('.closed').splice(0,25);
            } else {
                var actives = $('.closed.active:first').prevAll('.closed').splice(0,25);
            }
            $('.closed.active').removeClass('active');
            $(actives).addClass('active');
            cPage = parseInt($(this).text());
        }

    });

</script>
