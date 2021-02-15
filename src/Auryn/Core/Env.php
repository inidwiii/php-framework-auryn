<?php

namespace Auryn\Core;

class Env
{
  /**
   * Hold the environment data variable
   * @var array
   */
  private $bag = [];

  /**
   * Hold the environment file handler
   * @var resource
   */
  private $fileHandler;

  /**
   * Hold the environment file name 
   * @var string
   */
  private $fileName;

  /**
   * Hold the environment file directory path
   * @var string
   */
  private $filePath;

  public function __construct($filePath = ROOT, $fileName = '.env')
  {
    $this->fileName = $fileName;
    $this->filePath = $filePath;
  }

  /**
   * Compile the environment variable from the environment file
   * 
   * @return void
   */
  public function init($callback = null)
  {
    $this->resolveFile();
    $this->resolveEnv();

    foreach ((array) $this->bag as $key => $value) {
      $_ENV[$key] = $value;
      $_SERVER[$key] = $value;
    }

    !is_null($callback) && call_user_func($callback, $this);
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
   * Parsing environment variable from the environment file
   * 
   * @return void
   * @throws \UnexpectedValueException
   */
  private function resolveEnv()
  {
    if (is_null($this->fileHandler)) throw new \UnexpectedValueException("Env file handler can't be null.");

    while ($line = fgets($this->fileHandler)) {
      if (strpos($line, '=') === false) continue;
      $line = explode('=', trim($line));

      $key = $line[0];
      $value = trim($line[1], '"\'`');

      $this->bag[$key] = $value;
    }
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