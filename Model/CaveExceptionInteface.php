<?php
namespace App\GptCavebackendBundle\Model;

interface CaveExceptionInteface
{
    /**
     * Message key to be used by the translation component.
     *
     * @return string
     */
    public function getMessageKey();

    /**
     * Message data to be used by the translation component.
     *
     * @return array
     */
    public function getMessageData();
}