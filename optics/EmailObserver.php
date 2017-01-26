<?php
namespace optics;

class EmailObserver extends Observer
{
    const EMAIL = 'notify@example.com';
    const SUBJECT = 'Notification';

    protected function log(\SplSubject $subject, Entity $entity = null)
    {
        if ($this->filter($subject, $entity)) {
            $this->sendEmail([
                'sku' => $entity->sku,
                'qoh' => $entity->qoh,
            ]);
        }
    }

    private function filter(\SplSubject $subject, Entity $entity = null)
    {
        if (!$entity) {
            return false;
        }
        if ($entity->qoh < 5) {
            return true;
        }
        return false;
    }

    private function sendEmail($data)
    {
        if (!is_string($data) && !is_array($data)) {
            throw new \InvalidArgumentException('$data must be a string or an array');
        }

        $email = self::getEmailTo();
        $subject = self::getSubject();
        $message = self::formatLogMessage($data);

        //avoid really send emails
        //$result = mail($email, $subject, $message);
        $result = true;

        if ($result === null) {
            throw new \Exception("Send email to $email failed.  Details:" . App::getLastError());
        }

        echo 'Sending email...<br>';

        return $result;
    }

    private static function formatLogMessage($data)
    {
        if (!is_string($data) && !is_array($data)) {
            throw new \InvalidArgumentException('$data must be a string or an array');
        }

        return (new \DateTime())->format('Y-m-d H:i:s') . json_encode($data) . PHP_EOL;
    }

    private static function getEmailTo()
    {
        return self::EMAIL;
    }

    private static function getSubject()
    {
        return self::SUBJECT;
    }
}