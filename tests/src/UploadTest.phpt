<?php

namespace h4kuna\Upload;

use Nette\Http,
	Nette\Utils,
	Tester\Assert;

$container = require __DIR__ . '/../bootsrap-container.php';
/* @var $fileUploadFactory \Salamium\Testinium\FileUploadFactory */
$fileUploadFactory = $container->getByType(\Salamium\Testinium\FileUploadFactory::class);

$tempDir = TEMP_DIR . '/upload';
Utils\FileSystem::createDir($tempDir);

// save file
$driver = new Driver\LocalFilesystem($tempDir);
$upload = new Upload($driver);
$storedFile = $upload->save($fileUploadFactory->create('čivava.txt'));

$absolutePath = $driver->createURI($storedFile);
Assert::true(is_file($absolutePath));

// save file to sub directory
$uploadFile = $fileUploadFactory->create('čivava.txt');
$storedFile = $upload->save($uploadFile, new \h4kuna\Upload\Upload\Options('my/path/is/here', function (Store\File $file, Http\FileUpload $uploadFile) {
	$file->size = filesize($uploadFile->getTemporaryFile());
	$file->name = 'foo';
}));

Assert::same('foo', $storedFile->name);
Assert::same('čivava.txt', $storedFile->getName());
Assert::same('text/plain', $storedFile->getContentType());
Assert::contains('my/path/is/here/', (string) $storedFile);

Assert::exception(function () use ($storedFile) {
	$storedFile->foo;
}, InvalidArgumentException::class);

Assert::true($storedFile->size > 0);
Assert::true($driver->isFileExists($storedFile));

$driver->remove($storedFile);
Assert::false($driver->isFileExists($storedFile));

// upload failed
Assert::exception(function () use ($upload, $fileUploadFactory) {
	$upload->save($fileUploadFactory->create('čivava.txt', UPLOAD_ERR_NO_FILE));
}, FileUploadFailedException::class);

// upload failed
Assert::exception(function () use ($upload, $fileUploadFactory) {
	$upload->save($fileUploadFactory->create('čivava.txt'), []);
}, InvalidArgumentException::class);

// upload failed
Assert::exception(function () use ($upload, $fileUploadFactory) {
	$upload->save($fileUploadFactory->create('čivava.txt'), new \h4kuna\Upload\Upload\Options('', null, null, new \h4kuna\Upload\Upload\ContentTypeFilter('application/json')));
}, UnSupportedFileTypeException::class);

