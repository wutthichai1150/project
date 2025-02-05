<?php
$folderPath = "C:\\AppServ\\www\\project\\uploads"; 
$command = 'icacls "' . $folderPath . '" /grant Everyone:(OI)(CI)F /T';
exec($command, $output, $return_var);
