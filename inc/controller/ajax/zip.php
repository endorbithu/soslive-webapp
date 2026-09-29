<?php 

//TODO: jogosultságellenőrzés
if(!isset($get['id'])) exit;
$iService = new IncidentsService();
$dbConn = new DbConn();
$hasPerm = $iService->isPostAvailableForCurrentUser($get['id']);  
if(!$hasPerm) exit; 
$id = $get['id'];


$stmt = $dbConn->conn->prepare('SELECT * FROM `posts` WHERE event_type = "PHOTO" AND `id` = :id ;');
$stmt->bindParam(':id', $id);
$stmt->execute();
$oneResult = $stmt->fetch(PDO::FETCH_ASSOC);


if(empty($oneResult)) {
	print "<h2>" . NO_RESULT . "</h2>";
	exit;
}
	


$filesTemp = glob(__DIR__ . '/../../../public/temp/*'); 
foreach($filesTemp as $fileT){ 
  if(is_file($fileT))
	unlink($fileT); 
}



if(!is_numeric($get['id'])) exit;
if(empty(preg_match('/^[a-z0-9\.\-]+$/', $get['username']))) exit;

$id = $get['id'] . '_' . substr(hash('sha512', $get['username'] . '||' . ACCESS_TOKEN_SALT . '||' . $get['id']),0,48);

$nameClean = $get['id'];

$datetime = new DateTime($oneResult['datetime']);

$get = [];

$fname = $nameClean . '.zip';

// Get real path for our folder
$rootPath = realpath(__DIR__ . '/../../../public/photos/' .$datetime->format('Ymd') . '/' . $id);

if(empty($rootPath)) {
    echo NO_SNAPSHOT_DATA_YET;
    exit;
}


// Initialize archive object
$zip = new ZipArchive();
$zip->open($fname, ZipArchive::CREATE | ZipArchive::OVERWRITE);

// Create recursive directory iterator
/** @var SplFileInfo[] $files */
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($rootPath),
    RecursiveIteratorIterator::LEAVES_ONLY
);


foreach ($files as $name => $file) {
    // Skip directories (they would be added automatically)
    if (!$file->isDir())
    {
        // Get real and relative path for current file
        $filePath = $file->getRealPath() ;
        $relativePath = substr($filePath, strlen($rootPath) + 1) . '.jpg' ;

        // Add current file to archive
        $zip->addFile($filePath ,  $relativePath);
    }
}


// Zip archive will be created only after closing object

$zip->close();


rename(__DIR__ . '/../../../public/' . $fname , __DIR__ . '/../../../public/temp/'.  $fname );

if (file_exists(__DIR__ . '/../../../public/temp/'.  $fname)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="'. basename(__DIR__ . '/temp/'.  $fname).'"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize(__DIR__ . '/../../../public/temp/'.  $fname));
    readfile(__DIR__ . '/../../../public/temp/'.  $fname);
    exit;
}







