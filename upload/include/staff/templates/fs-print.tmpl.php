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

<?php include ROOT_DIR . 'css/thread.css';?>

    </style>
</head>
<body>


<?php
    require_once('./Request/Atelier.php');

    $data = (array)json_decode(trim(file_get_contents('php://input')));
    $img = $data['img'];

    $field = $data['data'];

    $atelier = Atelier::getInstance();
    $data = $atelier->getAtelierTicket($id);
    //print_r($data)

?>



</body>
</html>
