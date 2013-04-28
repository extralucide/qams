<?php
Atomik::needed('Tool.class');
Tool::deleteKey('session/project_id');
Tool::deleteKey('session/company_id');
Tool::deleteKey('session/first_letter');
Tool::deleteKey('session/search');
Atomik::redirect('../users',false);
