Upload
==========
[![Build Status](https://travis-ci.org/h4kuna/upload.svg?branch=master)](https://travis-ci.org/h4kuna/upload)
[![Downloads this Month](https://img.shields.io/packagist/dm/h4kuna/upload.svg)](https://packagist.org/packages/h4kuna/upload)
[![Latest stable](https://img.shields.io/packagist/v/h4kuna/upload.svg)](https://packagist.org/packages/h4kuna/upload)
[![Coverage Status](https://coveralls.io/repos/github/h4kuna/upload/badge.svg?branch=master)](https://coveralls.io/github/h4kuna/upload?branch=master)

This extension help you save uploded files to filesystem and prepare for database.

Require PHP 5.4+.

This extension is for php [Nette framework](//github.com/nette/nette).

Installation to project
-----------------------
The best way to install h4kuna/upload is using composer:
```sh
$ composer require h4kuna/upload
```

How to use
-----------
Register extension for Nette in neon config file.
```neon
extensions:
    uploadExtension: h4kuna\Upload\DI\UploadExtension

# optional
uploadExtension:
	destinations: %wwwDir%/upload # this is default, you must create like writeable

	# or destinations can by array
	destinations:
		public: %wwwDir%/upload # first is default
		private: %appDir%/private # string or service h4kuna\Upload\IDriver
```

First destination (in example **public**) is autowired. For every destination is created service **upload** and **download**. 

Manualy add dependency:
```neon
services:
    - UploadControl(@uploadExtension.upload.private)
    - DownloadControl(@uploadExtension.download.private)
``` 

 

Inject Upload service to your class and use it.
```php
/* @var $upload h4kuna\Upload\Upload */


try {
	/* @var $file Nette\Http\FileUpload */
	$relativeDir = $upload->save($file);
	// or
	$relativeDir = $upload->save($file, 'subdir/by/id');

	// example how to save to database data for IStoreFile
	$fileTable->insert([
		'name' => $uploadFile->getName(),
		'filesystem' => $relativePath,
		'type' => $uploadFile->getContentType(),
		// optional
		'size' => $uploadFile->getSize(),
	]);
} catch (\h4kuna\Upload\FileUploadFailedException $e) {
	// upload is faild
}
```

Now create [Nette\Application\Responses\FileResponse](https://api.nette.org/Nette.Application.Responses.FileResponse.html) for download file.

If you use in presenter
```php
/* @var $download h4kuna\Upload\Download */

// this create from data whose stored in database
$file = new File(...); // instance of IStoreFile
$presenter->sendResponse($download->createFileResponse($file));
```

Or if you use own script
```php
$file = new File(...); // instance of IStoreFile

try {
	$download->send($file);
} catch (\h4kuna\Upload\FileDownloadFailedException $e) {
	$myResponse->setCode(Nette\Http\IResponse::S404_NOT_FOUND);
}
exit;
```

Own Driver service
-----------
This library save files only on filesystem. But you need save to remote server like ftp or ssh. For this moment is not implemented, but it is prepared. Let's inspire on [LocalFilesystem](src/Driver/LocalFilesystem.php) 


- Create your own class with [IDriver](src/IDriver.php) interface
```php
<?php

class FtpDriver implements \h4kuna\Upload\IDriver
{
// your implementation
} 
```
- Register in neon if you need
```neon
services:
    myFtpDriver: FtpDriver
    
    uploadComponent: UploadControl(@uploadExtension.upload.myFtpDriver)
    reponseComponent: DownloadControl(@uploadExtension.download.myFtpDriver)
    
uploadExtension:
	destinations:
		public: %wwwDir%/upload # first is default  
		myFtpDriver: @myFtpDriver  
```
