<?php
namespace optics;

class EmailObserver extends Observer
{
    const EMAIL = 'notify@example.com';
    const SUBJECT = 'Notification';

    protected function log(\SplSubject $subject)
    {
        $this->sendEmail((array)$subject);
    }

    private function sendEmail($data)
    {
        if (!is_string($data) && !is_array($data)) {
            throw new \InvalidArgumentException('$data must be a string or an array');
        }

        $email = self::getEmailTo();
        $subject = self::getSubject();
        $message = json_encode($data);

        //$result = mail($email, $subject, $message);
        $result = true;

        if ($result === null) {
            throw new \Exception("Send email to $email failed.  Details:" . App::getLastError());
        }

        return $result;
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