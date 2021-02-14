<?php

namespace Auryn\Core;

class Env
{
  private $bag = [];

  private $fileHandler;

  private $fileName;

  private $filePath;

  public function __construct($filePath = ROOT, $fileName = '.env')
  {
    $this->fileName = $fileName;
    $this->filePath = $filePath;
  }

  public function compile()
  {
    $this->resolveFile();
  }

  /**
   * Re-assign the path of the environment file
   * 
   * @param string $filePath
   * @return void 
   * @throws \InvalidArgumentException
   */
  public function setFilePath(string $filePath)
  {
    if (!is_dir($filePath)) throw new \InvalidArgumentException("'{$filePath}' is not exists.");
    $this->filePath = $filePath;
  }

  /**
   * Re-assign the file name of the environment default file name
   * 
   * @param string $fileName
   * @return void
   */
  public function setFileName(string $fileName)
  {
    $this->fileName = $fileName;
  }

  /**
   * Resolve the file handler of the environment file
   * 
   * @return void
   * @throws \InvalidArgumentException
   */
  private function resolveFile()
  {
    $this->filePath = strtr(trim($this->filePath, '\\/'), '\\/', DS . DS) . DS;
    
    if (!file_exists($this->filePath . $this->fileName)) throw new \InvalidArgumentException(
      "'{$this->filePath}{$this->fileName}' is not exists."
    );

    $this->fileHandler = fopen($this->filePath . $this->fileName, 'r');
  }
}