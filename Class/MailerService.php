<?php
/**
Genereert e-mails
**/
class MailerService
{
    private $fromAddress;
    private $toAddress;
    private $mailbody;
    private $subject;

    /**
     * Het 'van' adres opgeven
     *
     * @param $fromAddress
     * @return void
     */
    public function setFromAddress(string $fromAddress) {
        $this->fromAddress = $fromAddress;
    }

    /**
     * Het 'naar' adres opgeven
     *
     * @param $toAddress
     * @return void
     */
    public function setToAddress(string $toAddress) {
        $this->toAddress = $toAddress;
    }

    /**
     * Het onderwerp opgeven
     *
     * @param $subject
     * @return void
     */
    public function setSubject(string $subject) {
        $this->subject = $subject;
    }

    /**
     * De inhoud van de mail opgeven
     *
     * @param $mailbody
     * @return void
     */
    public function setMailbody(string $mailbody) {
        $this->mailbody = $mailbody;
    }

    /**
     * Mail in HTML-formaat sturen
     *
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
     * Mail in platte tekst sturen
     *
     * @return void
     */
    public function send() {
        $headers  = "From: " . strip_tags($this->fromAddress) . "\r\n";
        $headers .= "Reply-To: " . strip_tags($this->fromAddress) . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";

        mail($this->toAddress, $this->subject, $this->mailbody, $headers);
    }
}