<?php
class TicketsInfos
{
    private static $instance;
    private static $hostname;
    private static $username;
    private static $password;
    private static $dbh;

    private function __construct()
    {
    // Your "heavy" initialization stuff here
        $this->hostname = 'localhost';
        $this->username = 'root';
        $this->password = '';
        try{
            $this->dbh = new PDO('mysql:host=localhost;dbname=osticket', $this->username, $this->password);
        }catch(PDOException $e){
            die($e);
        }
    }

    public static function getInstance()
    {
        if ( is_null( self::$instance ) )
        {
        self::$instance = new self();
        }
        return self::$instance;
    }

    public function ticket_open_org_count($org){
        $res = $this->dbh->prepare("SELECT COUNT(*) FROM ost_ticket,ost_user,ost_user__cdata,ost_ticket__cdata WHERE ost_ticket.user_id = ost_user.id AND ost_user.id = ost_user__cdata.user_id AND ost_ticket.ticket_id = ost_ticket__cdata.ticket_id AND ost_user.org_id = :org AND ost_ticket.status_id = '1'");
        $res->execute(array(':org'=>$org));
        return $res->fetchAll()[0]['COUNT(*)'];
    }

    public function ticket_close_org_count($org){
        $res = $this->dbh->prepare("SELECT COUNT(*) FROM ost_ticket,ost_user,ost_user__cdata,ost_ticket__cdata WHERE ost_ticket.user_id = ost_user.id AND ost_user.id = ost_user__cdata.user_id AND ost_ticket.ticket_id = ost_ticket__cdata.ticket_id AND ost_user.org_id = :org AND ost_ticket.status_id != '1'");
        $res->execute(array(':org'=>$org));
        return $res->fetchAll()[0]['COUNT(*)'];
    }

    public function ticket_open_org($user){
        $res = $this->dbh->prepare("SELECT ost_ticket.ticket_id,ost_ticket.number,ost_ticket.created,subject,ost_user.name,firsname
        FROM ost_ticket,ost_user,ost_user__cdata,ost_ticket__cdata
        WHERE ost_ticket.user_id = ost_user.id
        AND ost_user.id = ost_user__cdata.user_id
        AND ost_ticket.ticket_id = ost_ticket__cdata.ticket_id
        AND ost_user.id = :user
        AND ( ost_ticket.status_id = '1' OR ost_ticket.status_id = '6')");
        $res->execute(array(':user'=>$user));
        return $res->fetchAll();
    }

    public function ticket_org($org){
        $res = $this->dbh->prepare("SELECT ost_ticket.ticket_id,ost_ticket.number,ost_ticket.created,subject,ost_user.name,firsname
        FROM ost_ticket,ost_user,ost_user__cdata,ost_ticket__cdata
        WHERE ost_ticket.user_id = ost_user.id
        AND ost_user.id = ost_user__cdata.user_id
        AND ost_ticket.ticket_id = ost_ticket__cdata.ticket_id
        AND ost_user.org_name = :org
        AND ( ost_ticket.status_id = '1' OR ost_ticket.status_id = '6')");
        $res->execute(array(':org'=>$org));
        return $res->fetchAll();
    }

    public function ticket_user($user){
        $res = $this->dbh->prepare("SELECT ost_ticket.ticket_id,ost_ticket.number,ost_ticket.created,subject,ost_user.name,firsname
        FROM ost_ticket,ost_user,ost_user__cdata,ost_ticket__cdata
        WHERE ost_ticket.user_id = ost_user.id
        AND ost_user.id = ost_user__cdata.user_id
        AND ost_ticket.ticket_id = ost_ticket__cdata.ticket_id
        AND ost_user.id = :user
        AND ( ost_ticket.status_id = '1' OR ost_ticket.status_id = '6')");
        $res->execute(array(':user'=>$user));
        return $res->fetchAll();
    }

    public function ticket_close_org($user){
        $res = $this->dbh->prepare("SELECT ost_ticket.ticket_id,ost_ticket.number,ost_ticket.created,subject,ost_user.name,firsname
        FROM ost_ticket,ost_user,ost_user__cdata,ost_ticket__cdata
        WHERE ost_ticket.user_id = ost_user.id
        AND ost_user.id = ost_user__cdata.user_id
        AND ost_ticket.ticket_id = ost_ticket__cdata.ticket_id
        AND ost_user.id = :user
        AND ost_ticket.status_id != '1' AND ost_ticket.status_id != '6'");
        $res->execute(array(':user'=>$user));
        return $res->fetchAll();
    }

    public function numberOfOpenTicketsForUser ($user){
        $res = $this->dbh->prepare("SELECT COUNT(*)
        FROM ost_ticket,ost_user
        WHERE (ost_ticket.status_id = '1' OR ost_ticket.status_id = '6')
        AND ost_ticket.user_id = ost_user.id
        AND ost_user.id = :user");
        $res->execute(array(':user'=>$user));
        return $res->fetchAll()[0]['COUNT(*)'];
    }

    public function numberOfOpenTickets(){
        $res = $this->dbh->prepare("SELECT COUNT(*) FROM ost_ticket,ost_ticket_status,ost_help_topic
        WHERE ost_ticket.status_id = ost_ticket_status.id
        AND ost_ticket_status.state = 'open'
        AND ost_help_topic.topic_id = ost_ticket.topic_id
        AND ost_help_topic.topic != 'Atelier'");
        $res->execute();
        return $res->fetchAll()[0]['COUNT(*)'];
    }

    public function numberOfAssignedTickets(){
        $res = $this->dbh->prepare("SELECT COUNT(*)
        FROM ost_ticket,ost_ticket__cdata,ost_ticket_status,ost_user,ost_user__cdata,ost_ticket_priority,ost_help_topic
        WHERE ost_ticket.ticket_id = ost_ticket__cdata.ticket_id
        AND ost_ticket.status_id = ost_ticket_status.id
        AND ost_ticket.user_id = ost_user.id
        AND ost_user.id = ost_user__cdata.user_id
        AND ost_ticket__cdata.priority = ost_ticket_priority.priority_id
        AND ost_ticket.staff_id = '1'
        AND ost_ticket_status.state = 'open'
        AND (ost_ticket.created IS NOT NULL OR ost_ticket.reopened IS NOT NULL)
        AND ost_help_topic.topic_id = ost_ticket.topic_id
        AND ost_help_topic.topic != 'Atelier'
        ORDER BY DATE(lastupdate) DESC");
        $res->execute();
        return $res->fetchAll()[0]['COUNT(*)'];
    }

    public function ticket_user_id($ticketID)
    {
        $res = $this->dbh->prepare("SELECT ost_ticket.user_id FROM ost_ticket WHERE ost_ticket.ticket_id = :ticketID");
        $res->execute(array(':ticketID'=>$ticketID));
        return $res->fetchAll()[0]['user_id'];
    }

    public function ticket_user_name($ticketID)
    {
        /*TO CHANGE*/
        $res = $this->dbh->prepare("SELECT ost_ticket.user_id FROM ost_ticket WHERE ost_ticket.ticket_id = :ticketID");
        $res->execute(array(':ticketID'=>$ticketID));
        return $res->fetchAll()[0]['user_id'];
    }

    public function ticket_org_id($ticketID)
    {
        $res = $this->dbh->prepare("SELECT ost_organization.id FROM ost_ticket,ost_user,ost_organization WHERE ost_ticket.user_id = ost_user.id AND ost_user.org_id = ost_organization.id AND ost_ticket.ticket_id = :ticketID");
        $res->execute(array(':ticketID'=>$ticketID));
        return $res->fetchAll()[0]['id'];
    }

    public function ticket_org_name($ticketID)
    {
        $res = $this->dbh->prepare("SELECT ost_organization.name FROM ost_ticket,ost_user,ost_organization WHERE ost_ticket.user_id = ost_user.id AND ost_user.org_id = ost_organization.id AND ost_ticket.ticket_id = :ticketID");
        $res->execute(array(':ticketID'=>$ticketID));
        return $res->fetchAll()[0]['name'];
    }

    public function tickets_assigned($staffId)
    {
        $res = $this->dbh->prepare("SELECT ost_ticket.ticket_id,ost_ticket.status_id,ost_ticket_status.name as status_name,number,lastupdate,closed,subject,source,ost_user.name,ost_user.org_name,ost_user.org_id,ost_user__cdata.firsname,priority_desc
        FROM ost_ticket,ost_ticket__cdata,ost_ticket_status,ost_user,ost_user__cdata,ost_ticket_priority,ost_help_topic
        WHERE ost_ticket.ticket_id = ost_ticket__cdata.ticket_id
        AND ost_ticket.status_id = ost_ticket_status.id
        AND ost_ticket.user_id = ost_user.id
        AND ost_user.id = ost_user__cdata.user_id
        AND ost_ticket__cdata.priority = ost_ticket_priority.priority_id
        AND ost_ticket.staff_id = :staffID
        AND ost_ticket_status.state = 'open'
        AND (ost_ticket.created IS NOT NULL OR ost_ticket.reopened IS NOT NULL)
        AND ost_help_topic.topic_id = ost_ticket.topic_id
        AND ost_help_topic.topic != 'Atelier'
        ORDER BY DATE(lastupdate) DESC");
        $res->execute(array(':staffID'=>$staffId));
        return $res->fetchAll();
    }

    public function atelier_tickets(){
        $res = $this->dbh->prepare("SELECT ost_ticket.ticket_id,ost_ticket_status.name as status_name,ost_ticket.status_id,number,lastupdate,closed,subject,source,ost_user.name,ost_user.org_name,ost_user__cdata.firsname,priority_desc
        FROM ost_ticket,ost_ticket_status,ost_ticket__cdata,ost_user,ost_user__cdata,ost_ticket_priority,ost_help_topic
        WHERE ost_ticket.ticket_id = ost_ticket__cdata.ticket_id
        AND ost_ticket.status_id = ost_ticket_status.id
        AND ost_ticket.user_id = ost_user.id
        AND ost_user.id = ost_user__cdata.user_id
        AND ost_ticket__cdata.priority = ost_ticket_priority.priority_id
        AND ost_ticket_status.state = 'open'
        AND ost_help_topic.topic_id = ost_ticket.topic_id
        AND ost_help_topic.topic = 'Atelier'");
        $res->execute(array(':status'=>$status));
        return $res->fetchAll();
    }

    public function tickets($status="open"){
        $res = $this->dbh->prepare("SELECT ost_ticket.ticket_id,ost_ticket_status.name as status_name,ost_ticket.status_id,number,lastupdate,closed,subject,source,ost_user.name,ost_user.org_id,ost_user.org_name,ost_user__cdata.firsname,priority_desc
        FROM ost_ticket,ost_ticket_status,ost_ticket__cdata,ost_user,ost_user__cdata,ost_ticket_priority,ost_help_topic
        WHERE ost_ticket.ticket_id = ost_ticket__cdata.ticket_id
        AND ost_ticket.status_id = ost_ticket_status.id
        AND ost_ticket.user_id = ost_user.id
        AND ost_user.id = ost_user__cdata.user_id
        AND ost_ticket__cdata.priority = ost_ticket_priority.priority_id
        AND ost_ticket_status.state = :status
        AND ost_help_topic.topic_id = ost_ticket.topic_id
        AND ost_help_topic.topic != 'Atelier'");
        $res->execute(array(':status'=>$status));
        return $res->fetchAll();
    }

    public function ticketsByType($type){
        $res = $this->dbh->prepare("SELECT ost_ticket.ticket_id,ost_ticket_status.name as status_name,ost_ticket.status_id,number,lastupdate,closed,subject,source,ost_user.name,ost_user.org_id,ost_user.org_name,ost_user__cdata.firsname,priority_desc
        FROM ost_ticket,ost_ticket_status,ost_ticket__cdata,ost_user,ost_user__cdata,ost_ticket_priority,ost_help_topic
        WHERE ost_ticket.ticket_id = ost_ticket__cdata.ticket_id
        AND ost_ticket.status_id = ost_ticket_status.id
        AND ost_ticket.user_id = ost_user.id
        AND ost_user.id = ost_user__cdata.user_id
        AND ost_ticket__cdata.priority = ost_ticket_priority.priority_id
        AND ost_help_topic.topic_id = ost_ticket.topic_id
        AND ost_help_topic.topic = :type
        AND ost_ticket_status.state = 'open'");
        $res->execute(array(':type'=>$type));
        return $res->fetchAll(PDO::FETCH_ASSOC);
    }

    public function search_tickets($search){
        $res = $this->dbh->prepare(
        "SELECT ost_ticket.ticket_id,ost_ticket_status.name as status_name,status_id,number,lastupdate,closed,subject,ost_ticket.source,ost_user.name,ost_user.org_id,ost_user.org_name,ost_user__cdata.firsname,priority_desc,ost_organization.name as org_name
        FROM ost_ticket,ost_ticket__cdata,ost_ticket_status,ost_user,ost_user__cdata,ost_ticket_priority,ost_thread,ost_thread_entry
        WHERE ost_ticket.ticket_id = ost_ticket__cdata.ticket_id
        AND ost_ticket.status_id = ost_ticket_status.id
        AND ost_ticket.user_id = ost_user.id
        AND ost_thread.object_id = ost_ticket.ticket_id
        AND ost_thread.id = ost_thread_entry.thread_id
        AND ost_user.id = ost_user__cdata.user_id
        AND ost_ticket__cdata.priority = ost_ticket_priority.priority_id
        AND (number LIKE :search
        OR subject LIKE :search
        OR CONCAT(ost_user__cdata.firsname, ' ', ost_user.name) LIKE :search
        OR ost_thread_entry.body LIKE :search
        OR ost_thread_entry.title LIKE :search)
        ORDER BY DATE(lastupdate) DESC ,DATE(closed) DESC");
        $res->execute(array(':search'=>'%' . $search . '%'));
        return $res->fetchAll();
    }
}


?>
