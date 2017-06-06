<?php


class Agent extends VerySimpleModel {

    static $meta = array(
        'table' => 'ost_staff_avatar',
        'pk' => array('id_staff'),
        'joins' => array(
            'ost_staff' => array('constraint' => array('id_staff' => 'staff.staff_id')),
        ),
    );

}

?>
