<?php

class Article{

    public $reference = null;
    public $quantite = null;
    public $suiviStock = null;
    public $stock = null;
    public $designation = null;
    public $prix = null;
    public $sn = [];

    public function __construct($data){
        $data = array_values($data);
        for($i=0;$i<count($data);$i++){
            $data[$i] = iconv('Windows-1250', 'UTF-8', $data[$i]);
        }
        $this->reference = $data[0];
        $this->quantite = $data[1];
        $this->suiviStock = $data[2];
        $this->stock = $data[3];
        $this->designation = $data[4];
        $this->prix = $data[5];
    }

}

?>
