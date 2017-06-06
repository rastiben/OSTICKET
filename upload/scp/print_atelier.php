<?php


?>


<html>
   <head>
       <link href="../css/bootstrap.css" rel="stylesheet" type="text/css"/>
   </head>
    <body>
        <style>

            body{
                background: whitesmoke;
            }

            .states{
                margin: 0px;
                padding: 0px;
                margin-top: 30px;
            }

            .tickets{
                margin: 0px;
                padding: 0px;
            }

            .state .content{
                background:white;
                text-align: center;
                height: 200px;
                border: 1px solid #dfdfdf;
                box-shadow: 1px 1px 10px -4px black;
                position: relative;
            }

            .state .content .glyphicon{
                font-size: 35px;
                margin-top: 25px;
                margin-bottom: 15px;
            }

            .glyphicon.glyphicon-share-alt{
                color: #28B463;
            }
            .glyphicon.glyphicon-home{
                color: #3498DB;
            }
            .glyphicon.glyphicon-share{
                color: #E74C3C;
            }
            .glyphicon.glyphicon-repeat{
                color: #F4D03F;
            }

            .state .content .alt{
                background-color: #28B463;
            }
            .state .content .home{
                background-color: #3498DB;
            }
            .state .content .share{
                background-color: #E74C3C;
            }
            .state .content .repeat{
                background-color: #F4D03F;
            }

            .state .content h3{
                width: 136px;
                padding: 3px 25px;
                margin: 10px auto;
                position: absolute;
                margin-top: -12px;
                left: 50%;
                margin-left: -70px;
                color: white;
                font-size: 16px;
            }

            .state .content h2{
                margin-top: 37px;
                font-size: 35px;
            }

            .state .content .bottom{
                height: 100px;
                background: whitesmoke;
                position: absolute;
                width: 100%;
                margin-top: 23px;
            }

            .state .content h3:before{
                border-top: 2px solid #dfdfdf;
                content: "";
                position: absolute;
                top: 50%;
                left: -62px;
                right: 0;
                bottom: 0;
                width: 62px;
                z-index: 4;
            }

            .state .content h3:after{
                border-top: 2px solid #dfdfdf;
                content: "";
                position: absolute;
                top: 50%;
                right: -63px;
                bottom: 0;
                width: 63px;
                z-index: 4;
            }

            .ticket{
                margin-top: 30px;
            }

            .ticket .content{
                background:white;
                padding: 10px;
                height: 150px;
                border: 1px solid #dfdfdf;
                box-shadow: 1px 1px 10px -4px black;
                position: relative;
            }


        </style>


         <div class="states col-md-12">
            <div class="col-md-3 state">
                 <div class="content">
                    <span class="glyphicon glyphicon-share-alt" aria-hidden="true"></span>
                    <div class="bottom">
                        <h3 class="alt">Entrées</h3>
                        <h2><b>2</b></h2>
                    </div>
                 </div>
             </div>
            <div class="col-md-3 state">
                 <div class="content">
                    <span class="glyphicon glyphicon-home" aria-hidden="true"></span>
                    <div class="bottom">
                        <h3 class="home">Planche</h3>
                        <h2><b>2</b></h2>
                    </div>
                 </div>
             </div>
            <div class="col-md-3 state">
                 <div class="content">
                    <span class="glyphicon glyphicon-share" aria-hidden="true"></span>
                    <div class="bottom">
                        <h3 class="share">Sorties</h3>
                        <h2><b>2</b></h2>
                    </div>
                 </div>
             </div>
            <div class="col-md-3 state">
                 <div class="content">
                    <span class="glyphicon glyphicon-repeat" aria-hidden="true"></span>
                    <div class="bottom">
                        <h3 class="repeat">RMA</h3>
                        <h2><b>2</b></h2>
                    </div>
                 </div>
             </div>
         </div>

        <div class="tickets col-md-12">
            <div class="ticket repa col-md-12">
               <div class="content">
                    <div class="top">
                        <!--ICON-->
                        <h3>Ticket 867577</h3>
                    </div>
                    <div class="bot">
                        <ul class="infoT">
                            <li>411VDOC</li>
                            <li>Réparation</li>
                            <li>Normal</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="ticket prepa">

            </div>
        </div>


    </body>


     <script src="../js/jquery-1.11.2.min.js" type="text/javascript"></script>
     <script src="../js/bootstrap.js" type="text/javascript"></script>


</html>
