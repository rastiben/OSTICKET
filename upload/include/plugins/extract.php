<?php
try {
$phar = new Phar('fr.phar');
$phar->extractTo('./',null,true); // extract all files
} catch (Exception $e) {
echo "there was an error<br>";
print_r($e);
}
?>
