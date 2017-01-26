<?php
namespace optics;

class FileObserver extends Observer
{
    const LOG_FILENAME = 'optics.log';

    protected function log(\SplSubject $subject, Entity $entity = null)
    {
        if ($this->filter($subject, $entity)) {
            $this->appendLogFile([
                'sku' => $entity->sku,
                'qoh' => $entity->qoh,
                'cost' => $entity->cost,
                'salePrice' => $entity->salePrice,
            ]);
        }
    }

    private function filter(\SplSubject $subject, Entity $entity = null)
    {
        if (!$entity) {
            return false;
        }

        return true;
    }

    private function appendLogFile($data)
    {
        if (!is_string($data) && !is_array($data)) {
            throw new \InvalidArgumentException('$data must be a string or an array');
        }
        $filePath = self::getLogFilePath();

        $logMessage = self::formatLogMessage($data);

        $result = file_put_contents($filePath, $logMessage, FILE_APPEND);

        if ($result === null) {
            throw new \Exception("Write of data store file $filePath failed.  Details:" . App::getLastError());
        }

        echo 'Append log file...<br>';

        return $result;

    }

    private static function formatLogMessage($data)
    {
        if (!is_string($data) && !is_array($data)) {
            throw new \InvalidArgumentException('$data must be a string or an array');
        }

        return (new \DateTime())->format('Y-m-d H:i:s') . json_encode($data) . PHP_EOL;
    }

    private static function getLogFilePath()
    {
        return dirname(__DIR__) . DIRECTORY_SEPARATOR . self::LOG_FILENAME;
    }
}