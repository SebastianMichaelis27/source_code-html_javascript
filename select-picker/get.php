<?php

 $result = array(array(
  "label" => $_POST['search'],
  "value" => $_POST['search'],
 ));

 echo json_encode($result);

?>