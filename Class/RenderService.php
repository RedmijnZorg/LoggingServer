<?php
/**
Rendert e-mails
**/
class RenderService {

    private $title;
    private $contenttop;
    private $contentbottom;

    private $buttonvalue;
    private $buttonurl;
    private $footertext;

    /**
     * Titel instellen
     *
     * @param mixed $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * Inhoud bovenkant instellen
     *
     * @param mixed $contenttop
     */
    public function setContenttop(string $contenttop): void
    {
        $this->contenttop = $contenttop;
    }

    /**
     * Inhoud onderkant instellen
     *
     * @param mixed $contentbottom
     */
    public function setContentbottom(string $contentbottom): void
    {
        $this->contentbottom = $contentbottom;
    }

    /**
     * Knoptekst instellen
     *
     * @param mixed $buttonvalue
     */
    public function setButtonvalue(string $buttonvalue): void
    {
        $this->buttonvalue = $buttonvalue;
    }

    /**
     * Knop URL instellen
     *
     * @param mixed $buttonurl
     */
    public function setButtonurl(string $buttonurl): void
    {
        $this->buttonurl = $buttonurl;
    }

    /**
     * Voettekst instellen
     *
     * @param mixed $footertext
     */
    public function setFootertext(string $footertext): void
    {
        $this->footertext = $footertext;
    }

    /**
     * E-mail renderen
     *
     * @param bool $includebutton
     * @return string|false
     */
    public function renderMail(bool $includebutton = false) {
    	// Vereiste velden controleren
        if($this->title == NULl) {
            return false;
        }
        if($this->contenttop == NULl) {
            return false;
        }
        if($this->buttonvalue == NULl) {
            return false;
        }
        if($this->buttonurl == NULl) {
            return false;
        }
        if($this->footertext == NULl) {
            return false;
        }
        
        // Sjabloon openen op basis van wel/geen knop
        if($includebutton == true) {
            $template = file_get_contents(__dir__."/../Templates/email-button.tmpl");

        } else {
            $template = file_get_contents(__dir__."/../Templates/email-nobutton.tmpl");
        }
        
        // Kan de Sjabloon niet geopend worden? Stop dan.
        if($template == false) {
            return false;
        }
        
        // Variabelen in sjabloon vervangen
        $template = str_replace('VAR_TITLE', $this->title, $template);
        $template = str_replace('VAR_CONTENTSTOP', $this->contenttop, $template);
        if(isset($this->contentbottom)) {
            $template = str_replace('VAR_CONTENTSBOTTOM', $this->contentbottom, $template);
        } else {
            $template = str_replace('VAR_CONTENTSBOTTOM', "", $template);
        }
        if($includebutton == true) {
            $template = str_replace('VAR_BUTTONTEXT', $this->buttonvalue, $template);
            $template = str_replace('VAR_BUTTONURL', $this->buttonurl, $template);
        }

        $template = str_replace('VAR_FOOTERTEXT', $this->footertext, $template);
       
        return $template;
    }

}