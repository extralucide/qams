<?php
Atomik::disableLayout();
// Atomik::noRender();
Atomik::needed("Data.class");
$data_id = (isset($_GET['id']) ? $_GET['id'] : null);
$data = new Data;
$data->get($data_id);
$diagram_img = $data->createDiagram();
