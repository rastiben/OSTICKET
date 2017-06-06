<?php
if(($subnav=$nav->getSubMenu()) && is_array($subnav)){
    $activeMenu=$nav->getActiveMenu();
    if($activeMenu>0 && !isset($subnav[$activeMenu-1]))
        $activeMenu=0;
    $typeDropdown = [];
    foreach($subnav as $k=> $item) {
        if($item['droponly']) continue;
        $class=$item['iconclass'];
        if ($activeMenu && $k+1==$activeMenu
                or (!$activeMenu
                    && (strpos(strtoupper($item['href']),strtoupper(basename($_SERVER['SCRIPT_NAME']))) !== false
                        or ($item['urls']
                            && in_array(basename($_SERVER['SCRIPT_NAME']),$item['urls'])
                            )
                        )))
            $class="$class active";
        if (!($id=$item['id']))
            $id="subnav$k";

        //Extra attributes
        $attr = '';
        if ($item['attr'])
            foreach ($item['attr'] as $name => $value)
                $attr.=  sprintf("%s='%s' ", $name, $value);


        //Calcule du nombre de fichier ouvert/fermé/assigné
        //SI ISSET(TYPE) AJOUTER DANS UNE DROPDOWN
        if(strstr($item['href'],"type")){
            array_push($typeDropdown,sprintf('<li><a class="%s no-pjax" href="%s" title="%s" id="%s" %s>%s</a></li>',
                $class, $item['href'], $item['title'], $id, $attr, $item['desc']));
        } else {
            if($item['desc'] == "Fermé"){
                echo '<div class="dropdown">
                      <button class="btn dropdown-toggle" type="button" data-toggle="dropdown">
                      <div class="icon"></div>
                      Type
                      <span class="caret"></span></button>
                      <ul class="dropdown-menu">';
                foreach($typeDropdown as $key=>$type){
                    echo $type;
                }
                echo '</ul></div>';
            }
            echo sprintf('<li><a class="%s no-pjax" href="%s" title="%s" id="%s" %s>%s</a></li>',
                $class, $item['href'], $item['title'], $id, $attr, $item['desc']);
        }

    }
}
