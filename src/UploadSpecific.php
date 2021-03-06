<?php

namespace h4kuna\Upload;

use h4kuna\Upload\Upload\Options,
	Nette\Http,
	Nette\Forms\Controls;

class UploadSpecific
{

	/** @var IDriver */
	private $driver;

	/** @var Options */
	private $uploadOptions;


	public function __construct(IDriver $driver, Options $uploadOptions)
	{
		$this->driver = $driver;
		$this->uploadOptions = $uploadOptions;
	}


	/**
	 * @param Controls\UploadControl $uploadControl
	 * @param $message
	 * @return Controls\UploadControl
	 */
	public function setMimeTypeRuleForUploadControl(Controls\UploadControl $uploadControl, $message)
	{
		if ($this->uploadOptions->getContentTypeFilter() !== null) {
			Utils::setMimeTypeRule($this->uploadOptions->getContentTypeFilter(), $uploadControl, $message);
		}
		return $uploadControl;
	}


	/**
	 * @param Http\FileUpload $fileUpload
	 * @return Store\File
	 * @throws UnSupportedFileTypeException
	 * @throws FileUploadFailedException
	 */
	public function save(Http\FileUpload $fileUpload)
	{
		return Upload::saveFileUpload($fileUpload, $this->driver, $this->uploadOptions);
	}
}