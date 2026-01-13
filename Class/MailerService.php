<?php

class MailerService
{
    private $fromAddress;
    private $toAddress;
    private $mailbody;
    private $subject;

    /**
     * @param $fromAddress
     * @return void
     */
    public function setFromAddress($fromAddress) {
        $this->fromAddress = $fromAddress;
    }

    /**
     * @param $toAddress
     * @return void
     */
    public function setToAddress($toAddress) {
        $this->toAddress = $toAddress;
    }

    /**
     * @param $subject
     * @return void
     */
    public function setSubject($subject) {
        $this->subject = $subject;
    }

    /**
     * @param $mailbody
     * @return void
     */
    public function setMailbody($mailbody) {
        $this->mailbody = $mailbody;
    }

    /**
     * @return void
     */
    public function sendHTML() {
        $headers  = "From: " . strip_tags($this->fromAddress) . "\r\n";
        $headers .= "Reply-To: " . strip_tags($this->fromAddress) . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        mail($this->toAddress, $this->subject, $this->mailbody, $headers);
    }

    /**
     * @return void
     */
    public function send() {
        $headers  = "From: " . strip_tags($this->fromAddress) . "\r\n";
        $headers .= "Reply-To: " . strip_tags($this->fromAddress) . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";

        mail($this->toAddress, $this->subject, $this->mailbody, $headers);
    }
}