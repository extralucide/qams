<?php
date_default_timezone_set('Europe/Paris');
Atomik::set(array (
  'app' => 
  array (
    'layout' => array('_layout_generic','_layout_left','_layout_right'),
    'default_action' => 'index',
    'views' => 
    array (
      'file_extension' => '.phtml'
    ),
  ),
	'dirs' => array(
		'app'                => './app',
		'plugins'            => array('./app/modules', './app/plugins/'),
		'actions'            => './app/actions/',
		'views'              => './app/views/',
		'layouts'            => array('./app/layouts', './app/views'),
		'helpers'            => './app/helpers/',
		'includes'           => array('./app/includes/', './app/libraries/'),
		'overrides'          => './app/overrides/'
	),
  'atomik' => 
  array (
    'start_session' => true,
    'class_autoload' => true,
    'trigger' => 'action',
    'catch_errors' => true,
    'display_errors' => true,
    'debug' => false,
    'url_rewriting' => false
  ),
  'styles' => 
  array (
    1 => '../ckeditor/_samples/sample.css',
    3 => 'assets/css/main.css',
    4 => 'assets/css/tundra.css',
	7 => '../coloration/styles/shCore.css',
	8 => '../coloration/styles/shThemeDefault.css'
  ),
  'plugins' => 
  array (
    0 => 'Db',
  ),
  'scripts' => 
  array (
	1 => '../ckeditor/ckeditor.js',
    2 => '../ckeditor/_samples/sample.js',
    4 => 'assets/js/libs/review.js',
	8 => 'assets/js/libs/home.js',
	9 => 'assets/js/libs/actions.js',
	10 => 'assets/js/libs/data.js'
  ),
));

Atomik::set('db_config',array(
		'server' => 'localhost',
		'select' => 'finister',
		'user' => 'finister',
		'pass' => 'nm86hjj',
		'user_admin' => 'finister',
		'pass_admin' => 'nm86hjj',		
		'bin_path' => 'C:\\xampplite\\mysql\\bin\\',
		'qams_path' => 'C:\\xampplite\\htdocs\\qams\\',
		'sept_path' => 'C:\\\"Program Files\"\\7-Zip\\',	
		'zip_path' => 'C:\\xampplite\\htdocs',
		'backup_dir' => 'M:\\"02 - Qualité développement"\\Appere',
		'log' => 'qams_log.txt',
        'import_prr' => 'import_prr.sql',
		'ghostscript' => array ('win' => 'C:\\"Program Files"\\gs\\bin\\gswin32c.exe'."  -dNOPAUSE -sDEVICE=jpeg -dUseCIEColor -dTextAlphaBits=4 -dGraphicsAlphaBits=4 -dFirstPage=1 -dLastPage=1 -o{exportPath} -r{res} -dJPEGQ={quality} {input_pdf} ",
								'mac' => "/Applications/ghostscript/bin/gs '-dNOPAUSE' '-sDEVICE=jpeg' '-dUseCIEColor' '-dTextAlphaBits=4' '-dGraphicsAlphaBits=4' '-dFirstPage=1' '-dLastPage=1' '-o{exportPath}' '-r{res}' '-dJPEGQ={quality}' '{input_pdf}'"),
		'imagemagick' => array ('mac' => 'convert {input} {output}',
								'win' => 'C:\\ImageMagick\\emfplus -format png {input} {output}'),
		'mysqldump' => array ('unix'=>'mysqldump -h{db_server} -u{db_user} -p{db_pass} {db_select} | gzip> {output}',
							  'mac' => '/Applications/XAMPP/xamppfiles/bin/mysqldump -h{db_server} -u{db_user} -p{db_pass} {db_select} | gzip> {$utput}',
							  'win' => 'C:\\xampplite\\mysql\\bin\\mysqldump -h{db_server} -u{db_user} -p{db_pass} {db_select} > {output}'),
		'tar' => array ('unix'=> "tar -zcvf {output} --exclude='result' --exclude='docs' ../../qams",
						'mac' => "tar -zcvf {output} --exclude='result' --exclude='docs' ../../qams",
						'win' => 'C:\\"Program Files\"\\7-Zip\\7z a -r -x!docs/*.* -x!result/*.* -x!.svn -x!.DS_Store -x!.php~ -x!.phtml~ -x!.js~ -x!.css~ -tzip {output} C:\\xampplite\\htdocs\\qams\\* ')        
));
Atomik::set('plugins/Db', array(
	'dsn' 		=> 'mysql:host='.Atomik::get('db_config/server').';dbname='.Atomik::get('db_config/select'),
	'username' 	=> Atomik::get('db_config/user_admin'),
	'password' 	=> Atomik::get('db_config/pass_admin')
));
Atomik::set('site_title',"Quality Assurance Management System");
