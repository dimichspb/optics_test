<?php
namespace optics;

class FileObserver extends Observer
{
    const LOG_FILENAME = 'optics.log';

    protected function log(\SplSubject $subject)
    {
        $this->appendLogFile((array)$subject);
    }

    private function appendLogFile($data)
    {
        if (!is_string($data) && !is_array($data)) {
            throw new \InvalidArgumentException('$data must be a string or an array');
        }
        $filePath = self::getLogFilePath();
        
        $result = file_put_contents($filePath, json_encode($data));

        if ($result === null) {
            throw new \Exception("Write of data store file $filePath failed.  Details:" . App::getLastError());
        }

        return $result;

    }

    private static function getLogFilePath()
    {
        return dirname(__DIR__) . DIRECTORY_SEPARATOR . self::LOG_FILENAME;
    }
}