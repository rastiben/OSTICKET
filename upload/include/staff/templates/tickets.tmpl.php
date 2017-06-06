<?php
/*$args = array();
parse_str($_SERVER['QUERY_STRING'], $args);
$args['t'] = 'tickets';
unset($args['p'], $args['_pjax']);

$tickets = TicketModel::objects();

if ($user) {
    $filter = $tickets->copy()
        ->values_flat('ticket_id')
        ->filter(array('user_id' => $user->getId()))
        ->union($tickets->copy()
            ->values_flat('ticket_id')
            ->filter(array('thread__collaborators__user_id' => $user->getId()))
        , false);
} elseif ($org) {
    $filter = $tickets->copy()
        ->values_flat('ticket_id')
        ->filter(array('user__org' => $org));
}

// Apply filter
$tickets->filter(array('ticket_id__in' => $filter));

// Apply staff visibility
if (!$thisstaff->hasPerm(SearchBackend::PERM_EVERYTHING)) {
    // -- Open and assigned to me
    $visibility = array(
        new Q(array('status__state'=>'open', 'staff_id' => $thisstaff->getId()))
    );
    // -- Routed to a department of mine
    if (!$thisstaff->showAssignedOnly() && ($depts=$thisstaff->getDepts()))
        $visibility[] = new Q(array('dept_id__in' => $depts));
    // -- Open and assigned to a team of mine
    if (($teams = $thisstaff->getTeams()) && count(array_filter($teams)))
        $visibility[] = new Q(array(
            'team_id__in' => array_filter($teams), 'status__state'=>'open'
        ));
    $tickets->filter(Q::any($visibility));
}

$tickets->constrain(array('lock' => array(
                'lock__expire__gt' => SqlFunction::NOW())));

// Group by ticket_id.
$tickets->distinct('ticket_id');

// Save the query to the session for exporting
$queue = sprintf(':%s:tickets', $user ? 'U' : 'O');
$_SESSION[$queue] = $tickets;

// Apply pagination
$total = $tickets->count();
$page = ($_GET['p'] && is_numeric($_GET['p'])) ? $_GET['p'] : 1;
$pageNav = new Pagenate($total, $page, PAGE_LIMIT);
$pageNav->setURL(($user ? 'users.php' : 'orgs.php'), $args);
$tickets = $pageNav->paginate($tickets);

$tickets->annotate(array(
    'collab_count' => SqlAggregate::COUNT('thread__collaborators', true),
    'attachment_count' => SqlAggregate::COUNT(SqlCase::N()
       ->when(new SqlField('thread__entries__attachments__inline'), null)
       ->otherwise(new SqlField('thread__entries__attachments')),
        true
    ),
    'thread_count' => SqlAggregate::COUNT(SqlCase::N()
        ->when(
            new Q(array('thread__entries__flags__hasbit'=>ThreadEntry::FLAG_HIDDEN)),
            null)
        ->otherwise(new SqlField('thread__entries__id')),
       true
    ),
));

$tickets->values('staff_id', 'staff__firstname', 'staff__lastname', 'team__name', 'team_id', 'lock__lock_id', 'lock__staff_id', 'isoverdue', 'status_id', 'status__name', 'status__state', 'number', 'cdata__subject', 'ticket_id', 'source', 'dept_id', 'dept__name', 'user_id', 'user__default_email__address', 'user__name', 'lastupdate');

$tickets->order_by('-created');

TicketForm::ensureDynamicDataView();*/

// Fetch the results
?>
<div>
    <div>
        <p class="numberTickets">Tickets (<?php echo count($tickets) ?>)</p>
    </div>
</div>

<div class="listOrg">
  <?php

    foreach ($tickets as $T) {
                $total += 1;
                $tid=$T['number'];
                ?>
      <div class="ticketDiv">
        <div class="ticketColor" style="background-color:<?= $T['topic__couleur']; ?>"></div>
        <h6 id="orgName"><b><?= $T['user__org_name'] ?></b></h6>
        <p id="ticketSubject"><?= $T['cdata__subject'] ?></p>
        <p id="ticketCreated"><?= DateTime::createFromFormat('Y-m-d H:i:s',$T['created'])->format('d/m/Y') ?></p>
      </div>
            <!--<tr id="<?php echo $T['ticket_id']; ?>">
                <?php if($thisstaff->canManageTickets()) {

                    $sel=false;
                    if($ids && in_array($T['ticket_id'], $ids))
                        $sel=true;
                    ?>
                <td title="<?php echo $T['user__default_email__address']; ?>" nowrap>
                  <a class="Icon <?php echo strtolower($T['source']); ?>Ticket preview no-pjax"
                    title="Preview Ticket"
                    href="tickets.php?id=<?php echo $T['ticket_id']; ?>"
                    data-preview="#tickets/<?php echo $T['ticket_id']; ?>/preview"
                    ><?php echo $tid; ?></a></td>
                <td nowrap><div><?php
                    if ($T['collab_count'])
                        echo '<span class="pull-right faded-more" data-toggle="tooltip" title="'
                            .$T['collab_count'].'"><i class="icon-group"></i></span>';
                    ?><span class="truncate" style="max-width:<?php
                        echo $T['collab_count'] ? '150px' : '170px'; ?>"><?php
                    /*TO CHANGE*/
                    $un = new UsersName($T['user__name']);
                        echo '<a href="./users.php?id='. TicketsInfos::getInstance()->ticket_user_id($T['ticket_id']) .'#tickets">' . ucwords($T['firsname'] . ' ' . $T['name']) . '</a>';
                    ?></span></div></td>
                <?php } ?>
                <td><?php echo $T['created'] ?></td>
                <td><?php echo $T['subject'] ?></td>
            </tr>-->
            <?php
            } //end of foreach
        ?>
    </tbody>
</table>
<?php
/*if ($total>0) {
    echo '<div>';
    echo __('Page').':'.$pageNav->getPageLinks('tickets', '#tickets').'&nbsp;';
    echo sprintf('<a class="export-csv no-pjax" href="?%s">%s</a>',
            Http::build_query(array(
                    'id' => $user ? $user->getId(): $org->getId(),
                    'a' => 'export',
                    't' => 'tickets')),
            __('Export'));
    echo '</div>';
}*/ ?>
</div>
