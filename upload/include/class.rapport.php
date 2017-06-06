<?php
class RapportModel extends VerySimpleModel {
    static $meta = array(
        'table' => 'ost_rapport',
        'pk' => array('id'),
        'joins' => array(
            'staff' => array(
                'constraint' => array('id_agent' => 'Staff.staff_id'),
                'null' => true,
            ),
            'topic' => array(
                'constraint' => array('topic_id' => 'Topic.topic_id'),
                'null' => true,
            ),
            'ticket' => array(
                'constraint' => array('id_ticket' => 'Ticket.ticket_id'),
                'null' => true,
            ),
        )
    );
}

class RapportHorairesModel extends VerySimpleModel {
    static $meta = array(
        'table' => 'ost_rapport_horaires',
        'pk' => array('id')
    );
}

class RapportStockModel extends VerySimpleModel {
    static $meta = array(
        'table' => 'ost_rapport_stock',
        'pk' => array('id')
    );
}
