<html>

<head>
   <?php include_once(SCP_DIR . '/Request/Rapport.php') ?>
    <style type="text/css">
        @page {
            header: html_def;
            footer: html_def;
            margin: 10mm;
            margin-top: 5mm;
            margin-bottom: 22mm;
        }

        .round{
            border:0.1mm solid #220044;
            background-color: #f0f2ff;
            background-gradient: linear #c7cdde #f0f2ff 0 1 0 0.5;
            border-top-left-radius: 4em;
            background-clip: border-box;
        }

        .signature{
            /*float:right;*/
            width: 300px;
            height: 150px;
            border: 1px solid black;
            margin-top: 15px;
            padding-left: 15px;
        }

        table.tableContent{
            width: inherit;
            border-collapse: inherit;
        }

        table{
            width:100%;
            border-collapse: collapse;
        }

        table.tableContent,
        table.tableContent thead th,
        table.tableContent tbody td{
            border:none;
        }

        table,
        table thead th,
        table tbody td{
            border:1px solid black;
        }

        table tbody .planche{
            text-align:center;
            font-size: 25px;
        }

        table.tableContent tbody td{
            height:110px;
            /*width: 110px;*/
            background: red;
        }

        table tbody td{
            height:120px
        }

        .content{
        }

<?php include ROOT_DIR . 'css/thread.css';?>

    </style>
</head>
<body>


<?php
    /*require_once('./Request/Atelier.php');

    $data = (array)json_decode(trim(file_get_contents('php://input')));
    $img = $data['img'];

    $field = $data['data'];

    $atelier = Atelier::getInstance();
    $data = $atelier->getAtelierTicket($id);*/
    //print_r($data)

    //print_r($planches);
    //$planches = $planches[0];
    //print_r(array_filter( $planches, function($item) { return $item['planche'] == "b1"; }));
?>

<table>
    <thead>
        <tr>
            <th width="10%">Planche</th>
            <th width="90%">Contenu</th>
        </tr>
    </thead>
    <tbody>
       <?php
        $listPlanche = ["b1"];
        //print_r($listPlanche);
       // $temp = [];
        foreach($listPlanche as $planche){
        $temp = array_filter( $planches, function($item) { return $item['planche'] == $planche; });
        echo "<tr><td>";
        print_r($temp);
        echo "</tr></td>";
        //unset($temp);
            /*if($temp.length >= 0){
            ?>
            <tr>
                <td class="planche"><b><?php echo $planche ?></b></td>
                <td><table class="tableContent"><tbody><tr>
                   <?php
                    foreach($temp as $content){
                        ?>
                        <td><div class="content"><?php echo $content['contenuType'] ?></div></td>
                        <?php
                    }
                    ?></td>
                    </tr></tbody></table>
            </tr>
            <?php
            }*/
        }
        ?>

    </tbody>
</table>




</body>
</html>
