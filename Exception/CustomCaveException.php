<?php
namespace App\GptCavebackendBundle\Exception;

use App\GptCavebackendBundle\Model\CaveExceptionInteface;

/**
 * Exception message to translator
 */
class CustomCaveException extends \Exception implements CaveExceptionInteface
{
    private $messageKey;

    private $messageData = [];

    public function __construct(string $message = '', array $messageData = [], int $code = 0, \Exception $previous = null) {
        parent::__construct($message, $code, $previous);

        $this->setTranslatorMessage($message, $messageData);
    }

    /**
     * Set a message that will be shown to the user.
     *
     * @param string $messageKey  The message or message key
     * @param array  $messageData Data to be passed into the translator
     */
    public function setTranslatorMessage($messageKey, array $messageData = [])
    {
        $this->messageKey = $messageKey;
        $this->messageData = $messageData;
    }

    /**
     * @inheritDoc
     */
    public function getMessageKey(): string
    {
        return $this->messageKey;
    }

    /**
     * @inheritDoc
     */
    public function getMessageData(): array
    {
        return $this->messageData;
    }
}