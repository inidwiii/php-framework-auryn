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
   * Get environment data stored
   * 
   * @param string $key
   * @param mixed $default
   * @return mixed
   */
  public function get(string $key, $default = null)
  {
    if (!$this->isKeyExists($key)) return value($default);
    return $this->bag[$key];
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
    $this->share();

    !is_null($callback) && call_user_func($callback, $this);
  }

  /**
   * Set new environment data to store to the bag
   * 
   * @param string $key
   * @param mixed $value
   * @return array
   */
  public function set(string $key, $value = null)
  {
    if ($this->isKeyExists($key)) throw new \InvalidArgumentException("'{$key}' is already present in \$_ENV");
    $this->bag[$key] = value($value);
    $this->share(); // re-share the newest env data
    return $this->bag;
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
   * Check the key is present in the bag or not
   * 
   * @return bool
   */
  private function isKeyExists($key):bool
  {
    return (bool) array_key_exists($key, $this->bag);
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

  /**
   * Share the environment data on the bag to $_ENV and $_SERVER
   * 
   * @return void
   */
  private function share()
  {
    foreach ((array) $this->bag as $key => $value) {
      $_ENV[$key] = $value;
      $_SERVER[$key] = $value;
    }
  }
}