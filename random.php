<?php
	header('Origin: *');
	header('Access-Control-Allow-Origin: *');
	
	//error_reporting(0);
	ini_set('display_errors', false);
	ini_set('log_errors', false);

	$url = 'http://icons.labs.coop'.$_SERVER['REQUEST_URI'];
	parse_str(parse_url($url, PHP_URL_QUERY), $___GET);
	$_GET = array_merge($_GET, $___GET);

        function getDirList($dirname)
        {
            $ignored = array(
                'cvs' ,
                '_darcs');
            $list = array();
            if (substr($dirname, - 1) != '/') {
                $dirname .= '/';
            }
            if ($handle = opendir($dirname)) {
                while ($file = readdir($handle)) {
                    if (substr($file, 0, 1) == '.' || in_array(strtolower($file), $ignored))
                        continue;
                    if (is_dir($dirname . $file)) {
                        $list[$file] = $file;
                    }
                }
                closedir($handle);
                asort($list);
                reset($list);
            }

            return $list;
        }
	
	function getFileList($dirname, $prefix = '')
        {
            $filelist = array();
            if (substr($dirname, - 1) == '/') {
                $dirname = substr($dirname, 0, - 1);
            }
            if (is_dir($dirname) && $handle = opendir($dirname)) {
                while (false !== ($file = readdir($handle))) {
                    if (! preg_match('/^[\.]{1,2}$/', $file) && is_file($dirname . '/' . $file)) {
                        $file = $prefix . $file;
                        $filelist[$file] = $file;
                    }
                }
                closedir($handle);
                asort($filelist);
                reset($filelist);
            }

            return $filelist;
        }

	mt_srand(mt_rand(-microtime(true), microtime(true)));
	mt_srand(mt_rand(-microtime(true), microtime(true)));
	mt_srand(mt_rand(-microtime(true), microtime(true)));
	mt_srand(mt_rand(-microtime(true), microtime(true)));
	srand(mt_rand(-microtime(true), microtime(true)));
 	if (isset($_GET['folder']) && !empty($_GET['folder'])) {
		$folder = $_GET['folder'];
	} else {
		$folders = getDirList(dirname(__FILE__));
		shuffle($folders);
		$keys = array_keys($folders);
		$folder = $folders[$keys[mt_rand(0, count($folders)-1)]];
		unset($folders);
		unset($keys);
	}
	$folders = array();
	$pass = 0;
	$include = false;
	$exclude = false;
	while(count($folders)==0)
	{
		$folders = getDirList(dirname(__FILE__) . DIRECTORY_SEPARATOR . $folder);
		$pass++;
		if (isset($_GET['include']) && !empty($_GET['include']) && $include = false && $pass < 2)
		{
			foreach($folders as $key => $path) {
				if (!in_array($path, explode('--', $_GET['include']))) {
					unset($folders[$key]);
				}
			}
			$include = true;
			
		}
		if (isset($_GET['exclude']) && !empty($_GET['exclude']) && $exclude = false)
		{
			foreach($folders as $key => $path) {
				if (in_array($path, explode('--', $_GET['exclude']))) {
					unset($folders[$key]);
				}
			}
			if ($pass>=2)
				$exclude = true;
		}
	}
	shuffle($folders);
	$keys = array_keys($folders);
	if (isset($_GET['lockon'])) 
	{
		if (isset($_GET['sessionid']) && !empty($_GET['sessionid']))
			$sesshid = sha1($_GET['sessionid']);
		else
			$sesshid = sha1(serialize($_SERVER).json_encode($_GET));
		session_id($sesshid);
		session_start();
		if (!isset($_SESSION['foldise']) && empty($_SESSION['foldise']))
		{
			$_SESSION['foldise'] = $folders[$keys[mt_rand(0, count($folders)-1)]];
		}
		$foldise = $_SESSION['foldise'];
	} else {
		$foldise = $folders[$keys[mt_rand(0, count($folders)-1)]];
	}

	$icons = array();
	foreach(getFileList(dirname(__FILE__) . DIRECTORY_SEPARATOR . $folder  . DIRECTORY_SEPARATOR . $foldise ) as $id => $fname)
	{
		if (strpos($fname, '.png') || strpos($fname, '.ico'))
			$icons[str_replace(array("_", ".", '(', ')', "[", "]"), '-', $fname)] = "//icons.labs.coop/$folder/$foldise/$fname";
	}

	switch($_GET['op'])
	{
		default:
		case 'meta':
			header('Content-type: text/html');
?>
	<link rel="shortcut icon" type="image/ico" href="http://icons.labs.coop/<?php echo $folder; ?>/<?php echo $foldise; ?>/icon.ico" id="icon-ico" class="icon-ico" />
	<link rel="icon" type="image/png" href="http://icons.labs.coop/<?php echo $folder; ?>/<?php echo $foldise; ?>/icon-48x48.png" id="icon-48x48-png" class="icon-48x48-png" />
	<link rel="icon" href="http://icons.labs.coop/<?php echo $folder; ?>/<?php echo $foldise; ?>/icon-56x56.png" id="icon-56x56-png" class="icon-56x56-png" />
	<link rel="icon" sizes="72x72" href="http://icons.labs.coop/<?php echo $folder; ?>/<?php echo $foldise; ?>/icon-72x72.png" id="icon-72x72-png" class="icon-72x72-png" />
	<link rel="icon" sizes="114x114" href="http://icons.labs.coop/<?php echo $folder; ?>/<?php echo $foldise; ?>/icon-114x114.png" id="icon-114x114-png" class="icon-114x114-png" />
	<!--  
		Icongraphic Resourcing Help Key for url:~ <?php echo "http://icons.labs.coop" . $_SERVER['REQUEST_URI']; ?>

	  	Variables for $_GET if specified
	 
	  	$_GET['lockon'] (exists) ~ Icon being used will be stored in a session 
	  	$_GET['exclude'] = "<?php echo implode('--', $folders); ?>" ~ ICON Folders to exclude from selection 
	  	$_GET['include'] = "<?php echo implode('--', $folders); ?>" ~ ICON Folders to include from selection
	 
	  	ICON Typal's in path of "<?php echo $_GET['folder'] . '":~ ' . implode(', ', $folders); ?>
	 -->
<?php
			break;
		case "path":
			header('Content-type: text/text');
			echo "http://icons.labs.coop/$folder/$foldise";
			break;
		case "json":
			header('Content-type: application/json');
			echo json_encode($icons);
			break;
	}
	exit(0);
?>

